package services

import (
	"database/sql"
	"errors"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type AnnonceService struct {
	repo repository.AnnonceRepo
}

func NewAnnonceService() *AnnonceService { return &AnnonceService{} }

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

func (s *AnnonceService) RetirerAnnonce(idUtilisateur, idAnnonce int) error {
	return s.transitionProprietaire(idUtilisateur, idAnnonce,
		domain.AnnonceSnapshot.PeutRetirer, domain.StatutAnnRetiree)
}

func (s *AnnonceService) MarquerVendue(idUtilisateur, idAnnonce int) error {
	return s.transitionProprietaire(idUtilisateur, idAnnonce,
		domain.AnnonceSnapshot.PeutMarquerVendue, domain.StatutAnnVendue)
}

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

type AnnonceListeDTO struct {
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
	Auteur            string   `json:"auteur,omitempty"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

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

func (s *AnnonceService) ListerPubliees() ([]AnnonceListeDTO, error) {
	rows, err := s.repo.ListerPubliees(database.DB)
	if err != nil {
		return nil, err
	}
	return versListeDTO(rows, false), nil
}

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
