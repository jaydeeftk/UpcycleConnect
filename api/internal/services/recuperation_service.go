package services

import (
	"database/sql"
	"errors"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type RecuperationService struct {
	objets repository.ObjetRepo
	codes  repository.CodeBarreRepo
}

func NewRecuperationService() *RecuperationService {
	return &RecuperationService{}
}

type ObjetDTO struct {
	ID             int      `json:"id"`
	Type           string   `json:"type"`
	Poids          string   `json:"poids"`
	Statut         string   `json:"statut"`
	IdConteneur    int      `json:"id_conteneur"`
	Conteneur      string   `json:"conteneur"`
	CodeBarre      string   `json:"code_barre"`
	Titre          string   `json:"titre"`
	TypeAnnonce    string   `json:"type_annonce"`
	AllowedActions []string `json:"allowed_actions"`
}

func (s *RecuperationService) ListerDisponibles(idPro, idConteneur int) ([]ObjetDTO, error) {
	lignes, err := s.objets.ListerDisponibles(database.DB, idConteneur)
	if err != nil {
		return nil, err
	}
	return s.versDTO(lignes, idPro), nil
}

func (s *RecuperationService) MesReservations(idPro int) ([]ObjetDTO, error) {
	lignes, err := s.objets.ListerParPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	return s.versDTO(lignes, idPro), nil
}

func (s *RecuperationService) versDTO(lignes []repository.ObjetLigne, idPro int) []ObjetDTO {
	out := make([]ObjetDTO, 0, len(lignes))
	for _, l := range lignes {
		proprio := 0
		if l.IdPro.Valid {
			proprio = int(l.IdPro.Int64)
		}
		out = append(out, ObjetDTO{
			ID:             l.ID,
			Type:           l.Type,
			Poids:          l.Poids,
			Statut:         l.Statut,
			IdConteneur:    l.IdConteneur,
			Conteneur:      l.Conteneur,
			CodeBarre:      l.CodeBarre,
			Titre:          l.TitreAnnonce,
			TypeAnnonce:    l.TypeAnnonce,
			AllowedActions: domain.ActionsObjetPro(l.Statut, proprio, idPro),
		})
	}
	return out
}

func (s *RecuperationService) Reserver(idPro, idObjet int) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.objets.ObjetPourMAJ(tx, idObjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Objet introuvable")
		} else if err != nil {
			return err
		}
		if err := snap.PeutReserver(); err != nil {
			return err
		}
		return s.objets.Reserver(tx, idObjet, idPro)
	})
}

func (s *RecuperationService) Recuperer(idPro, idObjet int) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.objets.ObjetPourMAJ(tx, idObjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Objet introuvable")
		} else if err != nil {
			return err
		}
		if err := snap.PeutRecuperer(); err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Cette réservation n'est pas la vôtre")
		}
		if err := s.objets.Recuperer(tx, idObjet); err != nil {
			return err
		}

		return s.codes.MarquerUtiliseParObjet(tx, idObjet)
	})
}

func (s *RecuperationService) RecupererParCodeBarre(idPro int, code string) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	if strings.TrimSpace(code) == "" {
		return domain.Invalide("Code-barres manquant")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.codes.ResoudrePourMAJ(tx, code)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Code-barres inconnu")
		} else if err != nil {
			return err
		}
		if err := snap.PeutServirARecuperer(); err != nil {
			return err
		}
		obj := snap.Objet()
		if err := obj.PeutRecuperer(); err != nil {
			return err
		}
		if !obj.AppartientAuPro(idPro) {
			return domain.Forbidden("Cette réservation n'est pas la vôtre")
		}
		if err := s.objets.Recuperer(tx, snap.IdObjet); err != nil {
			return err
		}
		return s.codes.MarquerUtilise(tx, snap.ID)
	})
}

func (s *RecuperationService) MesAchatsParticulier(idUtilisateur int) ([]ObjetDTO, error) {
	lignes, err := s.objets.ListerAchatsParticulier(database.DB, idUtilisateur)
	if err != nil {
		return nil, err
	}
	out := make([]ObjetDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, ObjetDTO{
			ID: l.ID, Type: l.Type, Poids: l.Poids, Statut: l.Statut,
			IdConteneur: l.IdConteneur, Conteneur: l.Conteneur, CodeBarre: l.CodeBarre,
			Titre: l.TitreAnnonce, TypeAnnonce: l.TypeAnnonce,
			AllowedActions: domain.ActionsObjetParticulier(l.Statut),
		})
	}
	return out, nil
}

func (s *RecuperationService) RecupererParticulier(idUtilisateur, idObjet int) error {
	if idUtilisateur <= 0 {
		return domain.Forbidden("Authentification requise")
	}
	return withTx(func(tx *sql.Tx) error {
		appartient, err := s.objets.ObjetAppartientAAcheteur(tx, idObjet, idUtilisateur)
		if err != nil {
			return err
		}
		if !appartient {
			return domain.Forbidden("Cet objet ne vous appartient pas")
		}
		snap, err := s.objets.ObjetPourMAJ(tx, idObjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Objet introuvable")
		} else if err != nil {
			return err
		}
		if err := snap.PeutRecupererParticulier(); err != nil {
			return err
		}
		if err := s.objets.RecupererDirect(tx, idObjet); err != nil {
			return err
		}
		return s.codes.MarquerUtiliseParObjet(tx, idObjet)
	})
}

func (s *RecuperationService) RecupererParCodeBarreParticulier(idUtilisateur int, code string) error {
	if idUtilisateur <= 0 {
		return domain.Forbidden("Authentification requise")
	}
	if strings.TrimSpace(code) == "" {
		return domain.Invalide("Code-barres manquant")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.codes.ResoudrePourMAJParticulier(tx, code, idUtilisateur)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Code-barres inconnu ou ne vous appartenant pas")
		} else if err != nil {
			return err
		}
		if snap.Statut != domain.StatutCodeBarreActive {
			return domain.Conflit("Ce code n'est plus valide")
		}
		if snap.StatutObjet != domain.StatutObjetEnStock {
			return domain.Conflit("Cet objet n'est plus disponible à la récupération")
		}
		if err := s.objets.RecupererDirect(tx, snap.IdObjet); err != nil {
			return err
		}
		return s.codes.MarquerUtilise(tx, snap.ID)
	})
}

func (s *RecuperationService) AnnulerReservation(idPro, idObjet int) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.objets.ObjetPourMAJ(tx, idObjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Objet introuvable")
		} else if err != nil {
			return err
		}
		if err := snap.PeutAnnulerReservation(); err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Cette réservation n'est pas la vôtre")
		}
		return s.objets.AnnulerReservation(tx, idObjet)
	})
}
