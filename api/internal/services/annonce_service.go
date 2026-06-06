package services

import (
	"database/sql"
	"errors"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// AnnonceService porte les cas d'usage du cycle de vie d'une annonce. L'IDENTITÉ
// vient toujours de l'utilisateur AUTHENTIFIÉ (jamais du corps/URL). Chaque
// transition d'état est transactionnelle, verrouille l'agrégat (FOR UPDATE) et
// délègue la décision au domaine : rôle + propriété + état source vérifiés AVANT
// toute mutation, sinon erreur métier typée (403/409/422), jamais 500.
type AnnonceService struct {
	repo repository.AnnonceRepo
}

func NewAnnonceService() *AnnonceService { return &AnnonceService{} }

// resoudreParticulier mappe l'utilisateur authentifié vers son Id_Particuliers.
// Absence de ligne => compte non particulier (admin/salarié/pro) : 403 métier.
func (s *AnnonceService) resoudreParticulier(q repository.Querier, idUtilisateur int) (int, error) {
	idPart, err := s.repo.IdParticulier(q, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Forbidden("Action réservée aux particuliers")
	}
	if err != nil {
		return 0, err
	}
	return idPart, nil
}

// CreationAnnonceInput : intention de dépôt reçue du handler (sans identité ni
// statut — tous deux dérivés côté serveur).
type CreationAnnonceInput struct {
	Titre       string
	Description string
	Categorie   string
	Etat        string
	Type        string
	Prix        float64
	Ville       string
	CodePostal  string
}

// CreerAnnonce valide les invariants métier (titre, type↔prix) PUIS insère sous
// l'identité du particulier authentifié, au statut en_attente (modération).
func (s *AnnonceService) CreerAnnonce(idUtilisateur int, in CreationAnnonceInput) (int64, error) {
	if err := domain.ValiderCreationAnnonce(in.Titre, in.Type, in.Prix); err != nil {
		return 0, err
	}
	var newID int64
	err := withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}
		id, err := s.repo.Creer(tx, repository.AnnonceCreation{
			Titre: in.Titre, Description: in.Description, Categorie: in.Categorie,
			Etat: in.Etat, Type: in.Type, Prix: in.Prix, Ville: in.Ville,
			CodePostal: in.CodePostal, IdParticulier: idPart,
		})
		if err != nil {
			return err
		}
		newID = id
		return nil
	})
	return newID, err
}

// RetirerAnnonce : transition PROPRIÉTAIRE en_attente|validee -> retiree. Retrait
// DOUX (pas de DELETE) : l'historique reste auditable et l'état canonique 'retiree'
// existe précisément pour ça.
func (s *AnnonceService) RetirerAnnonce(idUtilisateur, idAnnonce int) error {
	return s.transitionProprietaire(idUtilisateur, idAnnonce,
		domain.AnnonceSnapshot.PeutRetirer, domain.StatutAnnRetiree)
}

// MarquerVendue : transition PROPRIÉTAIRE validee -> vendue.
func (s *AnnonceService) MarquerVendue(idUtilisateur, idAnnonce int) error {
	return s.transitionProprietaire(idUtilisateur, idAnnonce,
		domain.AnnonceSnapshot.PeutMarquerVendue, domain.StatutAnnVendue)
}

// transitionProprietaire factorise les transitions réservées à l'auteur : verrou,
// résolution du particulier, contrôle de PROPRIÉTÉ, garde d'état du domaine, écriture.
func (s *AnnonceService) transitionProprietaire(
	idUtilisateur, idAnnonce int,
	garde func(domain.AnnonceSnapshot) error,
	cible string,
) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}
		snap, err := s.repo.PourMAJ(tx, idAnnonce)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Annonce introuvable")
		}
		if err != nil {
			return err
		}
		if snap.Proprietaire != idPart {
			return domain.Forbidden("Cette annonce ne vous appartient pas")
		}
		if err := garde(snap); err != nil {
			return err
		}
		return s.repo.MettreStatut(tx, idAnnonce, cible)
	})
}

// ValiderAnnonce / RefuserAnnonce : transitions ADMIN depuis en_attente. Le rôle
// admin est garanti en amont par le middleware ; ici on vérifie l'état source et
// on borne la cible aux valeurs canoniques — fini le statut libre qui violait le CHECK.
func (s *AnnonceService) ValiderAnnonce(idAnnonce int) error {
	return s.transitionAdmin(idAnnonce,
		domain.AnnonceSnapshot.PeutValider, domain.StatutAnnValidee)
}

func (s *AnnonceService) RefuserAnnonce(idAnnonce int) error {
	return s.transitionAdmin(idAnnonce,
		domain.AnnonceSnapshot.PeutRefuser, domain.StatutAnnRefusee)
}

func (s *AnnonceService) transitionAdmin(
	idAnnonce int,
	garde func(domain.AnnonceSnapshot) error,
	cible string,
) error {
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.PourMAJ(tx, idAnnonce)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Annonce introuvable")
		}
		if err != nil {
			return err
		}
		if err := garde(snap); err != nil {
			return err
		}
		return s.repo.MettreStatut(tx, idAnnonce, cible)
	})
}

// SupprimerAnnonce : suppression dure réservée à l'admin (feature existante).
func (s *AnnonceService) SupprimerAnnonce(idAnnonce int) error {
	n, err := s.repo.Supprimer(database.DB, idAnnonce)
	if err != nil {
		return err
	}
	if n == 0 {
		return domain.Introuvable("Annonce introuvable")
	}
	return nil
}

// FicheAnnonceDTO : projection d'affichage + état dérivé serveur. Email est
// `omitempty` : il n'apparaît que lorsqu'il est révélé (visiteur authentifié).
type FicheAnnonceDTO struct {
	ID                int      `json:"id"`
	Titre             string   `json:"titre"`
	Description       string   `json:"description"`
	Categorie         string   `json:"categorie"`
	Etat              string   `json:"etat"`
	TypeAnnonce       string   `json:"type_annonce"`
	Prix              float64  `json:"prix"`
	Ville             string   `json:"ville"`
	CodePostal        string   `json:"code_postal"`
	Statut            string   `json:"statut"`
	Date              string   `json:"date"`
	Auteur            string   `json:"auteur"`
	Email             string   `json:"email,omitempty"`
	EstProprietaire   bool     `json:"est_proprietaire"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

// FicheAnnonce charge l'annonce pour affichage en appliquant, CÔTÉ SERVEUR, la
// VISIBILITÉ (une annonce non publiée reste invisible — 404 — pour qui n'est ni
// propriétaire ni admin) et calcule allowed_actions pour ce requérant. L'email du
// déposant n'est révélé qu'à un visiteur authentifié (jamais au public anonyme).
func (s *AnnonceService) FicheAnnonce(idUtilisateur int, role string, idAnnonce int) (FicheAnnonceDTO, error) {
	var dto FicheAnnonceDTO
	f, err := s.repo.Fiche(database.DB, idAnnonce)
	if errors.Is(err, sql.ErrNoRows) {
		return dto, domain.Introuvable("Annonce introuvable")
	}
	if err != nil {
		return dto, err
	}

	estAdmin := role == "admin"
	estProprietaire := false
	if idUtilisateur != 0 && !estAdmin {
		if idPart, e := s.repo.IdParticulier(database.DB, idUtilisateur); e == nil {
			estProprietaire = idPart == f.Proprietaire
		}
	}

	if !domain.AnnonceVisible(f.Statut, estProprietaire, estAdmin) {
		// Même réponse qu'une annonce inexistante : on ne divulgue pas l'existence
		// d'une annonce en attente / refusée / retirée à un tiers.
		return dto, domain.Introuvable("Annonce introuvable")
	}

	snap := domain.AnnonceSnapshot{
		Statut: f.Statut, Type: f.Type, Prix: f.Prix, Proprietaire: f.Proprietaire,
	}
	dto = FicheAnnonceDTO{
		ID: f.ID, Titre: f.Titre, Description: f.Description, Categorie: f.Categorie,
		Etat: f.Etat, TypeAnnonce: f.Type, Prix: f.Prix, Ville: f.Ville, CodePostal: f.CodePostal,
		Statut: f.Statut, Date: f.Date, Auteur: f.Auteur, EstProprietaire: estProprietaire,
		ActionsAutorisees: snap.ActionsAnnonce(estProprietaire, estAdmin),
	}
	if idUtilisateur != 0 {
		dto.Email = f.Email
	}
	return dto, nil
}

// AnnonceListeDTO : ligne de liste. Auteur est `omitempty` — présent sur la place
// de marché publique, absent de la liste privée du propriétaire.
type AnnonceListeDTO struct {
	ID          int     `json:"id"`
	Titre       string  `json:"titre"`
	Description string  `json:"description"`
	Categorie   string  `json:"categorie"`
	Etat        string  `json:"etat"`
	TypeAnnonce string  `json:"type_annonce"`
	Prix        float64 `json:"prix"`
	Ville       string  `json:"ville"`
	CodePostal  string  `json:"code_postal"`
	Statut      string  `json:"statut"`
	Date        string  `json:"date"`
	Auteur      string  `json:"auteur,omitempty"`
	// allowed_actions : dérivé de l'état (vide sur la place publique, actions du
	// propriétaire sur « mes annonces ») — le front n'affiche que ça.
	ActionsAutorisees []string `json:"allowed_actions"`
}

// versListeDTO mappe les lignes en DTO et dérive allowed_actions CÔTÉ SERVEUR.
// estProprietaire vaut true pour la liste privée (« mes annonces ») et false pour
// la place publique — où ActionsAnnonce renvoie [] (un visiteur n'a aucune action).
func versListeDTO(rows []repository.AnnonceListe, estProprietaire bool) []AnnonceListeDTO {
	out := make([]AnnonceListeDTO, 0, len(rows))
	for _, a := range rows {
		snap := domain.AnnonceSnapshot{Statut: a.Statut, Type: a.Type, Prix: a.Prix}
		out = append(out, AnnonceListeDTO{
			ID: a.ID, Titre: a.Titre, Description: a.Description, Categorie: a.Categorie,
			Etat: a.Etat, TypeAnnonce: a.Type, Prix: a.Prix, Ville: a.Ville,
			CodePostal: a.CodePostal, Statut: a.Statut, Date: a.Date, Auteur: a.Auteur,
			ActionsAutorisees: snap.ActionsAnnonce(estProprietaire, false),
		})
	}
	return out
}

// ListerPubliees : place de marché publique (annonces publiées uniquement).
func (s *AnnonceService) ListerPubliees() ([]AnnonceListeDTO, error) {
	rows, err := s.repo.ListerPubliees(database.DB)
	if err != nil {
		return nil, err
	}
	return versListeDTO(rows, false), nil
}

// MesAnnonces : liste privée de l'utilisateur AUTHENTIFIÉ (tous statuts). Un
// compte non particulier n'a simplement aucune annonce -> liste vide (pas une erreur).
func (s *AnnonceService) MesAnnonces(idUtilisateur int) ([]AnnonceListeDTO, error) {
	idPart, err := s.repo.IdParticulier(database.DB, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return []AnnonceListeDTO{}, nil
	}
	if err != nil {
		return nil, err
	}
	rows, err := s.repo.ListerParProprietaire(database.DB, idPart)
	if err != nil {
		return nil, err
	}
	return versListeDTO(rows, true), nil
}
