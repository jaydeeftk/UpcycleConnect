package services

import (
	"database/sql"
	"errors"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type PubliciteDTO struct {
	ID                string   `json:"id"`
	Type              string   `json:"type"`
	Prix              float64  `json:"prix"`
	DateDebut         string   `json:"date_debut"`
	DateFin           string   `json:"date_fin"`
	Statut            string   `json:"statut"`
	Description       string   `json:"description"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

type PubliciteService struct {
	repo repository.PubliciteRepo
}

func NewPubliciteService() *PubliciteService {
	return &PubliciteService{repo: repository.PubliciteRepo{}}
}

func actionsPublicite(statut string) []string {
	if statut == domain.StatutPubliciteActive {
		return []string{"annuler"}
	}
	return []string{}
}

func (s *PubliciteService) ListerPourPro(idPro int) ([]PubliciteDTO, error) {
	lignes, err := s.repo.ListerParPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]PubliciteDTO, 0, len(lignes))
	for _, p := range lignes {
		out = append(out, PubliciteDTO{
			ID: p.ID, Type: p.Type, Prix: p.Prix, DateDebut: p.DateDebut, DateFin: p.DateFin,
			Statut: p.Statut, Description: p.Description, ActionsAutorisees: actionsPublicite(p.Statut),
		})
	}
	return out, nil
}

type PubliciteInput struct {
	Type        string
	Prix        float64
	DateDebut   string
	DateFin     string
	Description string
}

func (s *PubliciteService) ValiderAvantPaiement(in PubliciteInput) error {
	debut, _ := time.Parse("2006-01-02", in.DateDebut)
	fin, _ := time.Parse("2006-01-02", in.DateFin)
	return domain.ValiderPublicite(in.Type, in.Prix, debut, fin)
}

func (s *PubliciteService) CreerPourPro(idPro int, in PubliciteInput, referenceStripe string) (string, error) {
	if idPro <= 0 {
		return "", domain.Forbidden("Action réservée aux professionnels")
	}
	debut, _ := time.Parse("2006-01-02", in.DateDebut)
	fin, _ := time.Parse("2006-01-02", in.DateFin)
	if err := domain.ValiderPublicite(in.Type, in.Prix, debut, fin); err != nil {
		return "", err
	}
	id := "PUB-" + suffixeAleatoire(8)
	err := s.repo.Creer(database.DB, repository.PubliciteCreation{
		ID: id, Type: in.Type, Prix: domain.Round2(in.Prix),
		DateDebut: in.DateDebut, DateFin: in.DateFin,
		Statut: domain.StatutPubliciteActive, Description: in.Description, IdPro: idPro,
		ReferenceStripe: referenceStripe,
	})
	if err != nil {
		return "", err
	}
	return id, nil
}

// CompleterPourProStripe cree la campagne apres paiement Stripe confirme.
// Idempotent via la contrainte UNIQUE sur Reference_Stripe.
func (s *PubliciteService) CompleterPourProStripe(idPro int, in PubliciteInput, referenceStripe string) error {
	_, err := s.CreerPourPro(idPro, in, referenceStripe)
	if err != nil && (repository.FacturationRepo{}).EstViolationUnicite(err) {
		return nil
	}
	return err
}

func (s *PubliciteService) AnnulerPourPro(idPro int, id string) error {
	return withTx(func(tx *sql.Tx) error {
		appartient, err := s.repo.AppartientAuPro(tx, id, idPro)
		if err != nil {
			return err
		}
		if !appartient {
			return domain.Introuvable("Campagne introuvable")
		}
		statut, err := s.repo.StatutPourMAJ(tx, id)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Campagne introuvable")
		}
		if err != nil {
			return err
		}
		if err := domain.PeutAnnulerPublicite(statut); err != nil {
			return err
		}
		return s.repo.MajStatut(tx, id, domain.StatutPubliciteAnnulee)
	})
}
