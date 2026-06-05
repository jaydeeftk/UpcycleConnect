package services

import (
	"crypto/rand"
	"database/sql"
	"errors"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// FacturationService porte les cas d'usage du vertical Contrat / Abonnement /
// Facture / Commission / Paiement. L'IDENTITÉ vient toujours du JWT (jamais du
// corps) ; les transitions verrouillent l'agrégat (FOR UPDATE) et délèguent la
// décision au domaine ; aucun montant n'est cru sur parole : tout est recalculé.
type FacturationService struct {
	repo repository.FacturationRepo
}

func NewFacturationService() *FacturationService { return &FacturationService{} }

const alphabetFacture = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
const nbTentativesNumero = 6

// suffixeAleatoire : segment aléatoire (crypto/rand) pour numéros de facture et
// identifiants d'abonnement uniques.
func suffixeAleatoire(n int) string {
	b := make([]byte, n)
	if _, err := rand.Read(b); err != nil {
		return strings.Repeat("0", n)
	}
	for i := range b {
		b[i] = alphabetFacture[int(b[i])%len(alphabetFacture)]
	}
	return string(b)
}

// parseDateSouple accepte les formats que le front peut envoyer (date seule,
// RFC3339, datetime SQL). Chaîne vide ou illisible => temps zéro (= non fournie).
func parseDateSouple(s string) time.Time {
	s = strings.TrimSpace(s)
	if s == "" {
		return time.Time{}
	}
	for _, layout := range []string{"2006-01-02", time.RFC3339, "2006-01-02 15:04:05"} {
		if t, err := time.Parse(layout, s); err == nil {
			return t
		}
	}
	return time.Time{}
}

// ===========================================================================
// Contrat
// ===========================================================================

type ContratAdminDTO struct {
	ID                int      `json:"id"`
	DateSignature     string   `json:"date_signature"`
	DateDebut         string   `json:"date_debut"`
	DateFin           string   `json:"date_fin"`
	Type              string   `json:"type"`
	Statut            string   `json:"statut"`
	Nom               string   `json:"nom"`
	Prenom            string   `json:"prenom"`
	Entreprise        string   `json:"nom_entreprise"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

func (s *FacturationService) ListerContrats() ([]ContratAdminDTO, error) {
	lignes, err := s.repo.AdminListerContrats(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]ContratAdminDTO, 0, len(lignes))
	for _, c := range lignes {
		out = append(out, ContratAdminDTO{
			ID: c.ID, DateSignature: c.DateSignature, DateDebut: c.DateDebut, DateFin: c.DateFin,
			Type: c.Type, Statut: c.Statut, Nom: c.Nom, Prenom: c.Prenom, Entreprise: c.Entreprise,
			ActionsAutorisees: domain.ActionsContratAdmin(c.Statut),
		})
	}
	return out, nil
}

type ContratProDTO struct {
	ID        int    `json:"id"`
	Type      string `json:"type"`
	Statut    string `json:"statut"`
	DateDebut string `json:"date_debut"`
	DateFin   string `json:"date_fin"`
}

// ContratsDuProfessionnel : un professionnel ne voit QUE ses contrats. L'identité
// (idUtilisateur) vient du JWT ; on la résout en Id_Professionnels — absence =>
// l'appelant n'est pas un pro (403 métier).
func (s *FacturationService) ContratsDuProfessionnel(idUtilisateur int) ([]ContratProDTO, error) {
	idPro, err := s.repo.IdProfessionnel(database.DB, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, domain.Forbidden("Action réservée aux professionnels")
	}
	if err != nil {
		return nil, err
	}
	lignes, err := s.repo.ContratsDuProfessionnel(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]ContratProDTO, 0, len(lignes))
	for _, c := range lignes {
		out = append(out, ContratProDTO{
			ID: c.ID, Type: c.Type, Statut: c.Statut, DateDebut: c.DateDebut, DateFin: c.DateFin,
		})
	}
	return out, nil
}

type ContratInput struct {
	Type            string
	DateSignature   string
	DateDebut       string
	DateFin         string
	Statut          string
	IdProfessionnel int
}

func (s *FacturationService) CreerContrat(in ContratInput) (int64, error) {
	debut := parseDateSouple(in.DateDebut)
	fin := parseDateSouple(in.DateFin)
	if err := domain.ValiderContrat(in.Type, debut, fin, in.IdProfessionnel); err != nil {
		return 0, err
	}

	// Un contrat naît en 'actif' (défaut), ou explicitement en 'brouillon'. On
	// n'autorise PAS la création directe dans un état terminal/suspendu.
	statut := strings.TrimSpace(in.Statut)
	if statut == "" {
		statut = domain.StatutContratActif
	}
	if statut != domain.StatutContratActif && statut != domain.StatutContratBrouillon {
		return 0, domain.Invalide("Un contrat se crée en brouillon ou actif")
	}

	var id int64
	err := withTx(func(tx *sql.Tx) error {
		existe, err := s.repo.ProfessionnelExiste(tx, in.IdProfessionnel)
		if err != nil {
			return err
		}
		if !existe {
			return domain.Introuvable("Professionnel rattaché introuvable")
		}
		id, err = s.repo.CreerContrat(tx, repository.ContratCreation{
			Type: in.Type, DateSignature: in.DateSignature, DateDebut: in.DateDebut,
			DateFin: in.DateFin, Statut: statut, IdProfessionnel: in.IdProfessionnel,
		})
		return err
	})
	return id, err
}

type ContratUpdateInput struct {
	DateFin string
	Type    string
}

func (s *FacturationService) ModifierContrat(idContrat int, in ContratUpdateInput) error {
	// L'existence se vérifie sous verrou (comme TransitionContrat) : RowsAffected
	// de MySQL compte les lignes CHANGÉES, pas APPARIÉES. Une mise à jour partielle
	// non destructive (COALESCE qui réécrit la valeur courante) touche 0 ligne sans
	// que le contrat soit absent ; on ne doit donc PAS dériver un 404 de RowsAffected.
	return withTx(func(tx *sql.Tx) error {
		if _, err := s.repo.ContratStatutPourMAJ(tx, idContrat); errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Contrat introuvable")
		} else if err != nil {
			return err
		}
		_, err := s.repo.MajContrat(tx, idContrat, in.DateFin, in.Type)
		return err
	})
}

// TransitionContrat applique une transition de la machine à états sous verrou :
// lit le statut FOR UPDATE, délègue la décision au domaine, écrit le statut cible.
func (s *FacturationService) TransitionContrat(idContrat int, action string) error {
	return withTx(func(tx *sql.Tx) error {
		statut, err := s.repo.ContratStatutPourMAJ(tx, idContrat)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Contrat introuvable")
		}
		if err != nil {
			return err
		}
		nouveau, err := domain.TransitionContrat(statut, action)
		if err != nil {
			return err
		}
		return s.repo.MajStatutContrat(tx, idContrat, nouveau)
	})
}

func (s *FacturationService) SupprimerContrat(idContrat int) error {
	n, err := s.repo.SupprimerContrat(database.DB, idContrat)
	if err != nil {
		return err
	}
	if n == 0 {
		return domain.Introuvable("Contrat introuvable")
	}
	return nil
}

// ===========================================================================
// Facture
// ===========================================================================

type FactureDTO struct {
	ID           int     `json:"id"`
	Numero       string  `json:"numero"`
	DateEmission string  `json:"date_emission"`
	MontantHT    float64 `json:"montant_ht"`
	TVA          float64 `json:"tva"`
	MontantTTC   float64 `json:"montant_ttc"`
	Statut       string  `json:"statut"`
	Type         string  `json:"type"`
	Nom          string  `json:"nom"`
	Prenom       string  `json:"prenom"`
}

func factureVersDTO(f repository.FactureLigne) FactureDTO {
	return FactureDTO{
		ID: f.ID, Numero: f.Numero, DateEmission: f.DateEmission, MontantHT: f.MontantHT,
		TVA: f.TVA, MontantTTC: f.MontantTTC, Statut: f.Statut, Type: f.Type,
		Nom: f.Nom, Prenom: f.Prenom,
	}
}

func (s *FacturationService) ListerFactures() ([]FactureDTO, error) {
	lignes, err := s.repo.AdminListerFactures(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]FactureDTO, 0, len(lignes))
	for _, f := range lignes {
		out = append(out, factureVersDTO(f))
	}
	return out, nil
}

func (s *FacturationService) ObtenirFacture(idFacture int) (FactureDTO, error) {
	f, err := s.repo.FactureParID(database.DB, idFacture)
	if errors.Is(err, sql.ErrNoRows) {
		return FactureDTO{}, domain.Introuvable("Facture introuvable")
	}
	if err != nil {
		return FactureDTO{}, err
	}
	return factureVersDTO(f), nil
}

// ===========================================================================
// Paiement
// ===========================================================================

type PaiementDTO struct {
	ID      int     `json:"id"`
	Montant float64 `json:"montant"`
	Statut  string  `json:"statut"`
	Methode string  `json:"methode"`
	Date    string  `json:"date"`
	Facture string  `json:"facture"`
}

func (s *FacturationService) PaiementsDeLUtilisateur(idUtilisateur int) ([]PaiementDTO, error) {
	lignes, err := s.repo.PaiementsDeLUtilisateur(database.DB, idUtilisateur)
	if err != nil {
		return nil, err
	}
	out := make([]PaiementDTO, 0, len(lignes))
	for _, p := range lignes {
		out = append(out, PaiementDTO{
			ID: p.ID, Montant: p.Montant, Statut: p.Statut, Methode: p.Methode,
			Date: p.Date, Facture: p.Facture,
		})
	}
	return out, nil
}

// CheckoutData : montant (TTC, recalculé en base) et intitulé d'un article
// payable. Le handler Stripe ne reçoit ces valeurs que du serveur.
type CheckoutData struct {
	Montant float64
	Titre   string
}

// resoudrePrixItem mappe (type, id) -> (prix, titre) en lisant la base. Renvoie
// 404 si l'article n'existe pas, 422 si le type est inconnu.
func (s *FacturationService) resoudrePrixItem(q repository.Querier, typ string, idItem int) (float64, string, error) {
	var (
		prix  float64
		titre string
		err   error
	)
	switch typ {
	case "formation":
		prix, titre, err = s.repo.PrixFormation(q, idItem)
	case "evenement":
		prix, titre, err = s.repo.PrixEvenement(q, idItem)
	default:
		return 0, "", domain.Invalide("Type de paiement invalide")
	}
	if errors.Is(err, sql.ErrNoRows) {
		return 0, "", domain.Introuvable("Article introuvable")
	}
	if err != nil {
		return 0, "", err
	}
	return prix, titre, nil
}

// PreparerCheckout valide et calcule, CÔTÉ SERVEUR, ce qui sera facturé. Un
// article gratuit (prix <= 0) n'est pas payable en ligne (422).
func (s *FacturationService) PreparerCheckout(typ string, idItem int) (CheckoutData, error) {
	prix, titre, err := s.resoudrePrixItem(database.DB, typ, idItem)
	if err != nil {
		return CheckoutData{}, err
	}
	if prix <= 0 {
		return CheckoutData{}, domain.Invalide("Cet article n'est pas payable en ligne")
	}
	return CheckoutData{Montant: domain.Round2(prix), Titre: titre}, nil
}

// EnregistrerPaiementItem matérialise un paiement CONFIRMÉ (appelé uniquement
// après vérification Stripe côté serveur). Idempotent sur la référence de session.
// Le prix catalogue est traité comme TTC payé : on en dérive un HT/TVA cohérent
// pour satisfaire l'invariant de facture, puis on enregistre facture + ligne +
// paiement dans une seule transaction.
func (s *FacturationService) EnregistrerPaiementItem(idUtilisateur int, typ string, idItem int, referenceStripe string) error {
	return withTx(func(tx *sql.Tx) error {
		deja, err := s.repo.PaiementReferenceExiste(tx, referenceStripe)
		if err != nil {
			return err
		}
		if deja {
			return nil // rapprochement déjà fait : rien à refaire
		}

		prix, titre, err := s.resoudrePrixItem(tx, typ, idItem)
		if err != nil {
			return err
		}
		if prix <= 0 {
			return domain.Invalide("Article non payable")
		}

		ttc := domain.Round2(prix)
		ht := domain.Round2(ttc / (1 + domain.TVAParDefaut/100))
		ttcCoherent := domain.CalculerTTC(ht, domain.TVAParDefaut)
		if err := domain.ValiderMontantsFacture(ht, domain.TVAParDefaut, ttcCoherent); err != nil {
			return err
		}

		numero, idFacture, err := s.creerFactureUnique(tx, repository.FactureCreation{
			MontantHT: ht, TVA: domain.TVAParDefaut, MontantTTC: ttcCoherent,
			Statut: domain.StatutFacturePayee, Type: typ, IdUtilisateur: idUtilisateur,
		})
		if err != nil {
			return err
		}
		_ = numero

		ligne := repository.LigneFactureCreation{
			Description: titre, Quantite: 1, PrixUnitaireHT: ht, TotalHT: ht, IdFacture: idFacture,
		}
		switch typ {
		case "formation":
			ligne.IdFormation = &idItem
		case "evenement":
			ligne.IdEvenement = &idItem
		}
		if err := s.repo.CreerLigneFacture(tx, ligne); err != nil {
			return err
		}

		_, err = s.repo.CreerPaiement(tx, repository.PaiementCreation{
			Montant: ttcCoherent, Statut: domain.StatutPaiementPaye, Methode: domain.MethodePaiementCarte,
			ReferenceStripe: referenceStripe, IdFacture: idFacture, IdUtilisateur: idUtilisateur,
		})
		return err
	})
}

// creerFactureUnique insère une facture en régénérant le numéro tant qu'il
// collisionne (uq Numero_facture) — bornée pour ne pas boucler indéfiniment.
func (s *FacturationService) creerFactureUnique(tx *sql.Tx, f repository.FactureCreation) (string, int64, error) {
	for i := 0; i < nbTentativesNumero; i++ {
		f.Numero = "FAC-" + time.Now().Format("20060102") + "-" + suffixeAleatoire(6)
		id, err := s.repo.CreerFacture(tx, f)
		if err == nil {
			return f.Numero, id, nil
		}
		if !s.repo.EstViolationUnicite(err) {
			return "", 0, err
		}
	}
	return "", 0, domain.Conflit("Impossible de générer un numéro de facture unique")
}

// ===========================================================================
// Abonnement
// ===========================================================================

type AbonnementDTO struct {
	ID                string   `json:"id"`
	Type              string   `json:"type"`
	Statut            string   `json:"statut"`
	Prix              float64  `json:"prix"`
	DateDebut         string   `json:"date_debut"`
	DateFin           string   `json:"date_fin"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

func (s *FacturationService) ListerAbonnements() ([]AbonnementDTO, error) {
	lignes, err := s.repo.AdminListerAbonnements(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]AbonnementDTO, 0, len(lignes))
	for _, a := range lignes {
		out = append(out, AbonnementDTO{
			ID: a.ID, Type: a.Type, Statut: a.Statut, Prix: a.Prix,
			DateDebut: a.DateDebut, DateFin: a.DateFin,
			ActionsAutorisees: domain.ActionsAbonnementAdmin(a.Statut),
		})
	}
	return out, nil
}

type AbonnementInput struct {
	Type      string
	Prix      float64
	DateDebut string
	DateFin   string
}

func (s *FacturationService) CreerAbonnement(in AbonnementInput) (string, error) {
	if err := domain.ValiderAbonnement(in.Type, in.Prix); err != nil {
		return "", err
	}
	id := "ABO-" + suffixeAleatoire(8)
	err := s.repo.CreerAbonnement(database.DB, repository.AbonnementCreation{
		ID: id, Type: in.Type, Prix: domain.Round2(in.Prix),
		DateDebut: in.DateDebut, DateFin: in.DateFin, Statut: domain.StatutAbonnementActif,
	})
	if err != nil {
		return "", err
	}
	return id, nil
}

func (s *FacturationService) TransitionAbonnement(id, action string) error {
	return withTx(func(tx *sql.Tx) error {
		statut, err := s.repo.AbonnementStatutPourMAJ(tx, id)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Abonnement introuvable")
		}
		if err != nil {
			return err
		}
		nouveau, err := domain.TransitionAbonnement(statut, action)
		if err != nil {
			return err
		}
		return s.repo.MajStatutAbonnement(tx, id, nouveau)
	})
}

func (s *FacturationService) SupprimerAbonnement(id string) error {
	n, err := s.repo.SupprimerAbonnement(database.DB, id)
	if err != nil {
		return err
	}
	if n == 0 {
		return domain.Introuvable("Abonnement introuvable")
	}
	return nil
}

// ===========================================================================
// Finances
// ===========================================================================

func (s *FacturationService) Finances() (repository.FinancesAgregat, error) {
	return s.repo.AgregatFinances(database.DB)
}
