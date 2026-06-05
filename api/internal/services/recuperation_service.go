package services

import (
	"database/sql"
	"errors"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// RecuperationService orchestre la RÉCUPÉRATION PRO : un professionnel réserve un
// objet déposé (en_stock -> reserve_pro), puis le récupère physiquement
// (reserve_pro -> recupere) ou annule sa réservation (reserve_pro -> en_stock).
//
// L'identité (idPro = Id_Professionnels) est TOUJOURS fournie par le handler
// depuis le JWT — jamais par le corps ni l'URL. Chaque transition verrouille
// l'objet (FOR UPDATE), valide l'état via le domaine, puis vérifie la propriété
// (le pro qui a réservé) avant d'écrire : rôle + propriété + précondition d'état.
type RecuperationService struct {
	objets repository.ObjetRepo
	codes  repository.CodeBarreRepo
}

func NewRecuperationService() *RecuperationService {
	return &RecuperationService{}
}

// ObjetDTO : vue d'un objet pour le professionnel. allowed_actions est dérivé de
// l'état serveur ET de la propriété POUR CE pro — le front n'affiche que ça.
type ObjetDTO struct {
	ID             int      `json:"id"`
	Type           string   `json:"type"`
	Poids          string   `json:"poids"`
	Statut         string   `json:"statut"`
	IdConteneur    int      `json:"id_conteneur"`
	Conteneur      string   `json:"conteneur"`
	CodeBarre      string   `json:"code_barre"` // code à scanner pour récupérer ('' si consommé)
	AllowedActions []string `json:"allowed_actions"`
}

// ListerDisponibles : catalogue des objets en_stock qu'un pro peut réserver.
// idConteneur > 0 restreint à un conteneur. idPro alimente allowed_actions (un
// objet en_stock n'appartient à personne -> ["reserver"]).
func (s *RecuperationService) ListerDisponibles(idPro, idConteneur int) ([]ObjetDTO, error) {
	lignes, err := s.objets.ListerDisponibles(database.DB, idConteneur)
	if err != nil {
		return nil, err
	}
	return s.versDTO(lignes, idPro), nil
}

// MesReservations : objets réservés ou déjà récupérés par CE pro. allowed_actions
// reflète l'état (reserve_pro -> recuperer/annuler ; recupere -> aucune).
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
			AllowedActions: domain.ActionsObjetPro(l.Statut, proprio, idPro),
		})
	}
	return out
}

// Reserver : en_stock -> reserve_pro, sous verrou. Deux pros concurrents ne peuvent
// pas réserver le même objet (le second relit reserve_pro et reçoit 409).
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

// Recuperer : reserve_pro -> recupere. Garde d'état PUIS garde de propriété —
// seul le pro qui a réservé peut récupérer (403 sinon), impossible de récupérer
// la réservation d'un autre.
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
		// L'objet est récupéré : son code-barres est consommé dans la même
		// transaction (objet recupere <-> code utilise, jamais désynchronisés).
		return s.codes.MarquerUtiliseParObjet(tx, idObjet)
	})
}

// RecupererParCodeBarre : récupération déclenchée par le SCAN du code-barres
// physique de l'objet (le pro ne saisit aucun identifiant interne, qui voyagerait
// dans l'URL). Le code est résolu vers son objet SOUS VERROU, puis on rejoue
// EXACTEMENT les gardes de la récupération par identifiant — code encore actif,
// objet en reserve_pro, propriété du pro — avant de consommer l'objet ET le code
// dans la même transaction. Code inconnu -> 404, déjà utilisé -> 409, objet d'un
// autre pro -> 403.
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

// AnnulerReservation : reserve_pro -> en_stock, libère le propriétaire. Mêmes
// gardes que Recuperer (état + propriété). L'occupation de la box est neutre
// (un objet réservé occupait déjà sa place) : aucun risque de sur-remplissage.
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
