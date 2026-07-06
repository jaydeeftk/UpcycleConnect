package services

import (
	"crypto/rand"
	"database/sql"
	"errors"
	"fmt"
	"os"
	"strings"
	"time"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/refund"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type FacturationService struct {
	repo    repository.FacturationRepo
	insc    repository.InscriptionRepo
	inscSvc *InscriptionService
}

func NewFacturationService() *FacturationService {
	return &FacturationService{inscSvc: NewInscriptionService()}
}

const alphabetFacture = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
const nbTentativesNumero = 6

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
	ID        int     `json:"id"`
	Type      string  `json:"type"`
	Statut    string  `json:"statut"`
	DateDebut string  `json:"date_debut"`
	DateFin   string  `json:"date_fin"`
	Montant   float64 `json:"montant"`
	Frequence string  `json:"frequence"`
}

type FacturationProDTO struct {
	NbContratsActifs    int     `json:"nb_contrats_actifs"`
	NbContratsResilie   int     `json:"nb_contrats_resilie"`
	TotalContratsActifs float64 `json:"total_contrats_actifs"`
	TotalAbonnements    float64 `json:"total_abonnements"`
	TotalCampagnes      float64 `json:"total_campagnes"`
	TotalCommissions    float64 `json:"total_commissions"`
	TotalGeneral        float64 `json:"total_general"`
}

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
			Montant: c.Montant, Frequence: c.Frequence,
		})
	}
	return out, nil
}

func (s *FacturationService) FacturationDuProfessionnel(idUtilisateur int) (FacturationProDTO, error) {
	idPro, err := s.repo.IdProfessionnel(database.DB, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return FacturationProDTO{}, domain.Forbidden("Action réservée aux professionnels")
	}
	if err != nil {
		return FacturationProDTO{}, err
	}
	a, err := s.repo.FacturationDuProfessionnel(database.DB, idPro)
	if err != nil {
		return FacturationProDTO{}, err
	}
	return FacturationProDTO{
		NbContratsActifs:    a.NbContratsActifs,
		NbContratsResilie:   a.NbContratsResilie,
		TotalContratsActifs: a.TotalContratsActifs,
		TotalAbonnements:    a.TotalAbonnements,
		TotalCampagnes:      a.TotalCampagnes,
		TotalCommissions:    a.TotalCommissions,
		TotalGeneral:        a.TotalGeneral,
	}, nil
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

func (s *FacturationService) ResilierContratPro(idUtilisateur, idContrat int) error {
	idPro, err := s.repo.IdProfessionnel(database.DB, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	if err != nil {
		return err
	}
	return withTx(func(tx *sql.Tx) error {
		owner, statut, err := s.repo.ContratOwnerEtStatut(tx, idContrat)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Contrat introuvable")
		}
		if err != nil {
			return err
		}
		if owner != idPro {
			return domain.Forbidden("Ce contrat ne vous appartient pas")
		}
		nouveau, err := domain.TransitionContrat(statut, "resilier")
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

type PaiementDTO struct {
	ID        int     `json:"id"`
	Montant   float64 `json:"montant"`
	Statut    string  `json:"statut"`
	Methode   string  `json:"methode"`
	Date      string  `json:"date"`
	Facture   string  `json:"facture"`
	IdFacture int     `json:"id_facture"`
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
			Date: p.Date, Facture: p.Facture, IdFacture: p.IdFacture,
		})
	}
	return out, nil
}

type CommandeDTO struct {
	Trouve        bool    `json:"trouve"`
	Statut        string  `json:"statut"`
	Montant       float64 `json:"montant"`
	IdFacture     int     `json:"id_facture"`
	NumeroFacture string  `json:"numero_facture"`
	Type          string  `json:"type"`
}

func (s *FacturationService) CommandeParReference(reference string) (CommandeDTO, error) {
	c, err := s.repo.PaiementParReference(database.DB, reference)
	if err != nil {
		return CommandeDTO{}, err
	}
	return CommandeDTO{
		Trouve: c.Trouve, Statut: c.Statut, Montant: c.Montant,
		IdFacture: c.IdFacture, NumeroFacture: c.NumeroFacture, Type: c.Type,
	}, nil
}

type CheckoutData struct {
	Montant float64
	Titre   string
}

func (s *FacturationService) appliquerReductionParticulier(q repository.Querier, idUtilisateur int, typ string, prix float64) float64 {
	if typ != "formation" && typ != "evenement" {
		return prix
	}
	idParticulier, err := s.repo.IdParticulier(q, idUtilisateur)
	if err != nil {
		return prix
	}
	activite, err := (repository.ScoreRepo{}).Activite(q, idParticulier, idUtilisateur)
	if err != nil {
		return prix
	}
	score, _ := domain.CalculerScore(activite)
	reduction := domain.ReductionFormationEvenementPourScore(score)
	if reduction <= 0 {
		return prix
	}
	return domain.Round2(prix * (1 - reduction))
}

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
	case "annonce":
		var a repository.AnnonceAchat
		a, err = s.repo.AnnoncePourAchat(q, idItem)
		prix, titre = a.Prix, a.Titre
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

func (s *FacturationService) PreparerCheckout(idUtilisateur int, typ string, idItem int) (CheckoutData, error) {
	if typ == "annonce" {
		a, err := s.repo.AnnoncePourAchat(database.DB, idItem)
		if errors.Is(err, sql.ErrNoRows) {
			return CheckoutData{}, domain.Introuvable("Annonce introuvable")
		}
		if err != nil {
			return CheckoutData{}, err
		}
		if err := domain.ValiderAchatAnnonce(a.Statut, a.Type, a.Prix); err != nil {
			return CheckoutData{}, err
		}
		return CheckoutData{Montant: domain.Round2(a.Prix), Titre: a.Titre}, nil
	}
	prix, titre, err := s.resoudrePrixItem(database.DB, typ, idItem)
	if err != nil {
		return CheckoutData{}, err
	}
	if prix <= 0 {
		return CheckoutData{}, domain.Invalide("Cet article n'est pas payable en ligne")
	}
	prix = s.appliquerReductionParticulier(database.DB, idUtilisateur, typ, prix)
	return CheckoutData{Montant: domain.Round2(prix), Titre: titre}, nil
}

func (s *FacturationService) EnregistrerPaiementItem(idUtilisateur int, typ string, idItem int, referenceStripe, paymentIntent string) error {
	surbooking := false
	err := withTx(func(tx *sql.Tx) error {
		deja, err := s.repo.PaiementReferenceExiste(tx, referenceStripe)
		if err != nil {
			return err
		}
		if deja {
			return nil
		}

		prix, titre, err := s.resoudrePrixItem(tx, typ, idItem)
		if err != nil {
			return err
		}
		if prix <= 0 {
			return domain.Invalide("Article non payable")
		}
		prix = s.appliquerReductionParticulier(tx, idUtilisateur, typ, prix)

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

		if _, err := s.repo.CreerPaiement(tx, repository.PaiementCreation{
			Montant: ttcCoherent, Statut: domain.StatutPaiementPaye, Methode: domain.MethodePaiementCarte,
			ReferenceStripe: referenceStripe, PaymentIntent: paymentIntent, IdFacture: idFacture, IdUtilisateur: idUtilisateur,
		}); err != nil {
			return err
		}

		if typ == "annonce" {
			idVendeur, errVendeur := (repository.ConversationRepo{}).VendeurDeAnnonce(tx, idItem)
			if err := s.repo.MarquerAnnonceVendue(tx, idItem, idUtilisateur); err != nil {
				return err
			}
			if errVendeur == nil && idVendeur > 0 {
				_ = s.repo.NotifierUtilisateur(tx, idVendeur,
					"Votre annonce a été vendue ! Rendez-vous dans \"Mes annonces\" pour choisir un conteneur et y déposer l'objet.")
				_ = NewConversationService().DemarrerAvecMessageAutomatique(tx, idItem, idUtilisateur, idVendeur,
					"Achat confirmé pour \""+titre+"\".", "achat")
			}
			taux := domain.TauxCommission()
			if idVendeur > 0 {
				if idParticulierVendeur, errPart := (repository.ScoreRepo{}).ResoudreParticulier(tx, idVendeur); errPart == nil {
					if activite, errAct := (repository.ScoreRepo{}).Activite(tx, idParticulierVendeur, idVendeur); errAct == nil {
						score, _ := domain.CalculerScore(activite)
						taux = domain.TauxCommissionPourScore(score)
					}
				}
			}
			return s.repo.CreerCommission(tx, repository.CommissionCreation{
				Taux: domain.Round2(taux * 100), TauxApplique: taux,
				Montant: domain.Round2(taux * prix), IdAnnonce: idItem, IdFacture: idFacture,
			})
		}

		idParticulier, err := s.repo.IdParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		inscrit, err := s.inscrireDansTx(tx, idParticulier, typ, idItem)
		if err != nil {
			return err
		}

		statut := domain.StatutPaiementPaye
		observations := fmt.Sprintf("Achat %s #%d - facture %s (%.2f EUR)", typ, idItem, numero, ttcCoherent)
		if !inscrit {
			surbooking = true
			statut = "surbooking"
			observations = fmt.Sprintf("SURBOOKING - %s #%d facture %s (%.2f EUR) encaisse mais complet : place a regulariser ou rembourser", typ, idItem, numero, ttcCoherent)
		}
		return s.repo.CreerHistorique(tx, idParticulier, statut, observations)
	})
	if err != nil {
		return err
	}
	if surbooking {
		_ = s.repo.NotifierAdmins(database.DB, fmt.Sprintf("Surbooking : paiement encaisse pour %s #%d complet — place a regulariser ou rembourser.", typ, idItem))
	}
	return nil
}

func (s *FacturationService) inscrireDansTx(tx *sql.Tx, idParticulier int, typ string, idItem int) (bool, error) {
	switch typ {
	case "formation":
		deja, err := s.insc.EstInscritFormation(tx, idParticulier, idItem)
		if err != nil {
			return false, err
		}
		if deja {
			return true, nil
		}
		snap, err := s.insc.FormationPourMAJ(tx, idItem)
		if err != nil {
			return false, err
		}
		if snap.PlacesDispo <= 0 {
			return false, nil
		}
		if err := s.insc.InsererReservationFormation(tx, idParticulier, idItem); err != nil {
			return false, err
		}
		if err := s.insc.DecrementerPlacesFormation(tx, idItem); err != nil {
			return false, err
		}
		return true, nil
	case "evenement":
		deja, err := s.insc.EstInscritEvenement(tx, idParticulier, idItem)
		if err != nil {
			return false, err
		}
		if deja {
			return true, nil
		}
		snap, err := s.insc.EvenementPourMAJ(tx, idItem)
		if err != nil {
			return false, err
		}
		n, err := s.insc.CompterParticipantsEvenement(tx, idItem)
		if err != nil {
			return false, err
		}
		if n >= snap.Capacite {
			return false, nil
		}
		return true, s.insc.InsererParticipationEvenement(tx, idParticulier, idItem)
	}
	return false, domain.Invalide("Type d'inscription invalide")
}

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

type AbonnementDTO struct {
	ID                string   `json:"id"`
	Type              string   `json:"type"`
	Statut            string   `json:"statut"`
	Prix              float64  `json:"prix"`
	DateDebut         string   `json:"date_debut"`
	DateFin           string   `json:"date_fin"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

type CommissionDetailDTO struct {
	ID                int     `json:"id"`
	Date              string  `json:"date"`
	Type              string  `json:"type"`
	Description       string  `json:"description"`
	PrixTotal         float64 `json:"prix_total"`
	Taux              float64 `json:"taux"`
	MontantCommission float64 `json:"montant_commission"`
	MontantVendeur    float64 `json:"montant_vendeur"`
	NomVendeur        string  `json:"nom_vendeur,omitempty"`
}

func versCommissionDetailDTO(lignes []repository.CommissionDetailLigne) []CommissionDetailDTO {
	out := make([]CommissionDetailDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, CommissionDetailDTO{
			ID: l.ID, Date: l.Date, Type: l.Type, Description: l.Description,
			PrixTotal: l.PrixTotal, Taux: l.Taux, MontantCommission: l.MontantCommission,
			MontantVendeur: domain.Round2(l.PrixTotal - l.MontantCommission),
			NomVendeur:     l.NomVendeur,
		})
	}
	return out
}

func (s *FacturationService) ListerCommissionsAdmin() ([]CommissionDetailDTO, error) {
	lignes, err := s.repo.ListerCommissionsPourAdmin(database.DB)
	if err != nil {
		return nil, err
	}
	return versCommissionDetailDTO(lignes), nil
}

func (s *FacturationService) ListerCommissionsPro(idPro int) ([]CommissionDetailDTO, error) {
	lignes, err := s.repo.ListerCommissionsPourPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	return versCommissionDetailDTO(lignes), nil
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

const PrixAbonnementPremium = 24.99

func (s *FacturationService) ProAbonnementActuel(idPro int) (*AbonnementDTO, error) {
	lignes, err := s.repo.AbonnementsDuPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	if len(lignes) == 0 {
		return nil, nil
	}
	a := lignes[0]
	return &AbonnementDTO{
		ID: a.ID, Type: a.Type, Statut: a.Statut, Prix: a.Prix,
		DateDebut: a.DateDebut, DateFin: a.DateFin,
		ActionsAutorisees: domain.ActionsAbonnementAdmin(a.Statut),
	}, nil
}

func (s *FacturationService) ProSouscrireAbonnement(idPro int, referenceStripe string) (string, error) {
	if idPro <= 0 {
		return "", domain.Forbidden("Action réservée aux professionnels")
	}
	actuel, err := s.ProAbonnementActuel(idPro)
	if err != nil {
		return "", err
	}
	if actuel != nil && (actuel.Statut == domain.StatutAbonnementActif || actuel.Statut == domain.StatutAbonnementSuspendu) {
		return "", domain.Conflit("Vous avez déjà un abonnement en cours")
	}
	id := "ABO-" + suffixeAleatoire(8)
	err = s.repo.CreerAbonnement(database.DB, repository.AbonnementCreation{
		ID: id, Type: "premium", Prix: PrixAbonnementPremium,
		DateDebut: time.Now().Format("2006-01-02"), Statut: domain.StatutAbonnementActif,
		IdProfessionnels: idPro, ReferenceStripe: referenceStripe,
	})
	if err != nil {
		return "", err
	}
	return id, nil
}

func (s *FacturationService) CompleterAbonnementProStripe(idPro int, referenceStripe string) error {
	_, err := s.ProSouscrireAbonnement(idPro, referenceStripe)
	if err != nil && s.repo.EstViolationUnicite(err) {
		return nil
	}
	return err
}

func (s *FacturationService) ProResilierAbonnement(idPro int) error {
	actuel, err := s.ProAbonnementActuel(idPro)
	if err != nil {
		return err
	}
	if actuel == nil {
		return domain.Introuvable("Aucun abonnement en cours")
	}
	return s.TransitionAbonnement(actuel.ID, "resilier")
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

func (s *FacturationService) Finances() (repository.FinancesAgregat, error) {
	return s.repo.AgregatFinances(database.DB)
}

type DemandeRembDTO struct {
	ID         int     `json:"id"`
	IdPaiement int     `json:"id_paiement"`
	Motif      string  `json:"motif"`
	Statut     string  `json:"statut"`
	Date       string  `json:"date_demande"`
	Montant    float64 `json:"montant"`
	Nom        string  `json:"nom"`
	Prenom     string  `json:"prenom"`
}

func (s *FacturationService) ListerDemandesRemboursement() ([]DemandeRembDTO, error) {
	lignes, err := s.repo.ListerDemandesRemb(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]DemandeRembDTO, 0, len(lignes))
	for _, d := range lignes {
		out = append(out, DemandeRembDTO{
			ID: d.ID, IdPaiement: d.IdPaiement, Motif: d.Motif, Statut: d.Statut,
			Date: d.DateDemande, Montant: d.Montant, Nom: d.Nom, Prenom: d.Prenom,
		})
	}
	return out, nil
}

func (s *FacturationService) CreerDemandeRemboursement(idUtilisateur, idPaiement int, motif string) (int64, error) {
	var idDemande int64
	err := withTx(func(tx *sql.Tx) error {
		owner, statut, err := s.repo.PaiementOwnerStatutPourMAJ(tx, idPaiement)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Paiement introuvable")
		}
		if err != nil {
			return err
		}
		if owner != idUtilisateur {
			return domain.Forbidden("Ce paiement ne vous appartient pas")
		}
		if statut != domain.StatutPaiementPaye {
			return domain.EtatInvalide("Seul un paiement payé peut faire l'objet d'une demande de remboursement")
		}
		idPart, err := s.repo.IdParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}
		existe, err := s.repo.DemandeRembEnCoursExiste(tx, idPaiement)
		if err != nil {
			return err
		}
		if existe {
			return domain.Deja("Une demande de remboursement est déjà en cours sur ce paiement")
		}
		idDemande, err = s.repo.CreerDemandeRemboursement(tx, idPaiement, idPart, motif)
		return err
	})
	return idDemande, err
}

func (s *FacturationService) RefuserDemandeRemboursement(idDemande int) error {
	return withTx(func(tx *sql.Tx) error {
		d, err := s.repo.DemandeRembPourMAJ(tx, idDemande)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Demande introuvable")
		}
		if err != nil {
			return err
		}
		if d.Statut != domain.StatutDemandeRembEnAttente {
			return domain.EtatInvalide("Seule une demande en attente peut être refusée")
		}
		if err := s.repo.MajDemandeRembStatut(tx, idDemande, domain.StatutDemandeRembRefusee); err != nil {
			return err
		}
		idUser, _, _ := s.repo.PaiementOwnerStatutPourMAJ(tx, d.IdPaiement)
		_ = s.repo.NotifierUtilisateur(tx, idUser, "Votre demande de remboursement a été refusée.")
		return nil
	})
}

func (s *FacturationService) RefundDirect(idPaiement int, motif string) error {
	var idDemande int64
	err := withTx(func(tx *sql.Tx) error {
		owner, statut, err := s.repo.PaiementOwnerStatutPourMAJ(tx, idPaiement)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Paiement introuvable")
		}
		if err != nil {
			return err
		}
		if statut != domain.StatutPaiementPaye {
			return domain.EtatInvalide("Seul un paiement payé peut être remboursé")
		}
		existe, err := s.repo.DemandeRembEnCoursExiste(tx, idPaiement)
		if err != nil {
			return err
		}
		if existe {
			return domain.Deja("Une demande de remboursement est déjà en cours sur ce paiement")
		}
		idPart, err := s.repo.IdParticulier(tx, owner)
		if err != nil {
			return err
		}
		idDemande, err = s.repo.CreerDemandeRemboursement(tx, idPaiement, idPart, motif)
		return err
	})
	if err != nil {
		return err
	}
	return s.ExecuterRemboursement(int(idDemande))
}

type infoRemboursement struct {
	idPaiement    int
	idUtilisateur int
	paymentIntent string
	typ           string
	idItem        int
	motif         string
}

func (s *FacturationService) ExecuterRemboursement(idDemande int) error {
	info, action, err := s.preparerRemboursement(idDemande)
	if err != nil {
		return err
	}
	if action == "noop" {
		return nil
	}

	stripe.Key = os.Getenv("STRIPE_SECRET_KEY")
	if stripe.Key == "" {
		return domain.Invalide("Stripe non configuré")
	}
	params := &stripe.RefundParams{PaymentIntent: stripe.String(info.paymentIntent)}
	params.SetIdempotencyKey(fmt.Sprintf("refund-demande-%d", idDemande))
	rf, err := refund.New(params)
	if err != nil {
		_ = s.echecRemboursement(info.idPaiement, idDemande)
		return fmt.Errorf("échec du remboursement Stripe : %w", err)
	}
	return s.finaliserRemboursement(idDemande, info, rf.ID)
}

func (s *FacturationService) preparerRemboursement(idDemande int) (infoRemboursement, string, error) {
	var info infoRemboursement
	action := ""
	err := withTx(func(tx *sql.Tx) error {
		d, err := s.repo.DemandeRembPourMAJ(tx, idDemande)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Demande introuvable")
		}
		if err != nil {
			return err
		}
		if d.Statut == domain.StatutDemandeRembRemboursee {
			action = "noop"
			return nil
		}
		if d.Statut == domain.StatutDemandeRembRefusee {
			return domain.EtatInvalide("Demande déjà refusée")
		}

		p, err := s.repo.PaiementRembInfoPourMAJ(tx, d.IdPaiement)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Paiement introuvable")
		}
		if err != nil {
			return err
		}
		if p.Statut == domain.StatutPaiementRembourse {
			action = "noop"
			return nil
		}
		if p.Statut != domain.StatutPaiementPaye && p.Statut != domain.StatutPaiementRemboursementEnCours {
			return domain.EtatInvalide("Le paiement n'est pas dans un état remboursable")
		}
		if p.PaymentIntent == "" {
			return domain.EtatInvalide("PaymentIntent Stripe absent : remboursement impossible")
		}
		typ, idItem, err := s.repo.ItemDeFacture(tx, p.IdFacture)
		if err != nil {
			return err
		}

		if p.Statut == domain.StatutPaiementPaye {
			if err := s.repo.MajPaiementStatut(tx, d.IdPaiement, domain.StatutPaiementRemboursementEnCours); err != nil {
				return err
			}
		}
		if d.Statut == domain.StatutDemandeRembEnAttente {
			if err := s.repo.MajDemandeRembStatut(tx, idDemande, domain.StatutDemandeRembApprouvee); err != nil {
				return err
			}
		}
		info = infoRemboursement{
			idPaiement: d.IdPaiement, idUtilisateur: p.IdUtilisateur,
			paymentIntent: p.PaymentIntent, typ: typ, idItem: idItem, motif: d.Motif,
		}
		action = "refund"
		return nil
	})
	return info, action, err
}

func (s *FacturationService) finaliserRemboursement(idDemande int, info infoRemboursement, refundID string) error {
	return withTx(func(tx *sql.Tx) error {
		p, err := s.repo.PaiementRembInfoPourMAJ(tx, info.idPaiement)
		if err != nil {
			return err
		}
		if p.Statut == domain.StatutPaiementRembourse {
			return s.repo.MajDemandeRembStatut(tx, idDemande, domain.StatutDemandeRembRemboursee)
		}
		if err := s.repo.FinaliserRemboursementPaiement(tx, info.idPaiement, refundID, info.motif); err != nil {
			return err
		}
		idPart, err := s.repo.IdParticulier(tx, info.idUtilisateur)
		if err != nil {
			return err
		}
		if info.typ != "" && info.idItem != 0 {
			if _, err := s.inscSvc.LibererPlaceTx(tx, idPart, info.typ, info.idItem); err != nil {
				return err
			}
		}
		obs := fmt.Sprintf("Remboursement %s #%d (refund %s)", info.typ, info.idItem, refundID)
		if err := s.repo.CreerHistorique(tx, idPart, domain.StatutPaiementRembourse, obs); err != nil {
			return err
		}
		if err := s.repo.MajDemandeRembStatut(tx, idDemande, domain.StatutDemandeRembRemboursee); err != nil {
			return err
		}
		_ = s.repo.NotifierUtilisateur(tx, info.idUtilisateur, "Votre remboursement a été effectué.")
		return nil
	})
}

func (s *FacturationService) echecRemboursement(idPaiement, idDemande int) error {
	return withTx(func(tx *sql.Tx) error {
		_ = s.repo.MajPaiementStatut(tx, idPaiement, domain.StatutPaiementPaye)
		_ = s.repo.MajDemandeRembStatut(tx, idDemande, domain.StatutDemandeRembEchouee)
		_ = s.repo.NotifierAdmins(tx, fmt.Sprintf("Échec du remboursement Stripe (demande #%d, paiement #%d) : à retraiter.", idDemande, idPaiement))
		return nil
	})
}
