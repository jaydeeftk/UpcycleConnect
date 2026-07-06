package services

import (
	"database/sql"
	"errors"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type InscriptionService struct {
	repo repository.InscriptionRepo
	fact repository.FacturationRepo
}

func NewInscriptionService() *InscriptionService { return &InscriptionService{} }

func (s *InscriptionService) resoudreParticulier(q repository.Querier, idUtilisateur int) (int, error) {
	idPart, err := s.repo.IdParticulier(q, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Forbidden("Action réservée aux particuliers")
	}
	if err != nil {
		return 0, err
	}
	return idPart, nil
}

func (s *InscriptionService) ParticiperEvenement(idUtilisateur, idEvenement int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.EvenementPourMAJ(tx, idEvenement)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Événement introuvable")
		}
		if err != nil {
			return err
		}

		deja, err := s.repo.EstInscritEvenement(tx, idPart, idEvenement)
		if err != nil {
			return err
		}
		if deja {
			return domain.Deja("Vous participez déjà à cet événement")
		}

		snap.Participants, err = s.repo.CompterParticipantsEvenement(tx, idEvenement)
		if err != nil {
			return err
		}
		if err := snap.PeutParticiper(time.Now()); err != nil {
			return err
		}

		aPaye, err := s.fact.UtilisateurAPayeEvenement(tx, idUtilisateur, idEvenement)
		if err != nil {
			return err
		}
		if err := domain.ExigePaiement(snap.Prix, aPaye); err != nil {
			return err
		}

		return s.repo.InsererParticipationEvenement(tx, idPart, idEvenement)
	})
}

func (s *InscriptionService) DesinscrireEvenement(idUtilisateur, idEvenement int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.EvenementPourMAJ(tx, idEvenement)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Événement introuvable")
		}
		if err != nil {
			return err
		}
		if err := snap.PeutDesinscrire(time.Now()); err != nil {
			return err
		}
		if snap.Prix > 0 {
			return domain.EtatInvalide("Désinscription d'un événement payant : merci de faire une demande de remboursement.")
		}

		retiree, err := s.LibererPlaceTx(tx, idPart, "evenement", idEvenement)
		if err != nil {
			return err
		}
		if !retiree {
			return domain.EtatInvalide("Vous n'êtes pas inscrit à cet événement")
		}
		return nil
	})
}

func (s *InscriptionService) LibererPlaceTx(tx *sql.Tx, idPart int, typ string, idItem int) (bool, error) {
	switch typ {
	case "formation":
		if _, err := s.repo.FormationPourMAJ(tx, idItem); err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				return false, domain.Introuvable("Formation introuvable")
			}
			return false, err
		}
		n, err := s.repo.SupprimerReservationFormation(tx, idPart, idItem)
		if err != nil {
			return false, err
		}
		if n == 0 {
			return false, nil
		}
		return true, s.repo.IncrementerPlacesFormation(tx, idItem)
	case "evenement":
		n, err := s.repo.SupprimerParticipationEvenement(tx, idPart, idItem)
		if err != nil {
			return false, err
		}
		return n > 0, nil
	}
	return false, domain.Invalide("Type d'inscription invalide")
}

func (s *InscriptionService) AnnulerInscription(idUtilisateur int, typ string, idItem int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}
		_, err = s.LibererPlaceTx(tx, idPart, typ, idItem)
		return err
	})
}

type FicheEvenementDTO struct {
	ID                int      `json:"id"`
	Titre             string   `json:"titre"`
	Description       string   `json:"description"`
	Date              string   `json:"date"`
	Lieu              string   `json:"lieu"`
	Capacite          int      `json:"capacite"`
	Participants      int      `json:"participants"`
	Statut            string   `json:"statut"`
	Prix              float64  `json:"prix"`
	EstInscrit        bool     `json:"est_inscrit"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

func (s *InscriptionService) FicheEvenement(idUtilisateur, idEvenement int) (FicheEvenementDTO, error) {
	var dto FicheEvenementDTO
	f, err := s.repo.FicheEvenement(database.DB, idEvenement)
	if errors.Is(err, sql.ErrNoRows) {
		return dto, domain.Introuvable("Événement introuvable")
	}
	if err != nil {
		return dto, err
	}

	estParticulier, dejaInscrit, aPaye := s.contexteParticulierEvenement(idUtilisateur, idEvenement)

	snap := domain.EvenementSnapshot{
		Statut:       f.Statut,
		Date:         f.Date.Time,
		Capacite:     f.Capacite,
		Participants: f.Participants,
		Prix:         f.Prix,
	}
	dto = FicheEvenementDTO{
		ID: f.ID, Titre: f.Titre, Description: f.Description, Lieu: f.Lieu,
		Date: formatDate(f.Date), Capacite: f.Capacite, Participants: f.Participants,
		Statut: f.Statut, Prix: f.Prix, EstInscrit: dejaInscrit,
		ActionsAutorisees: snap.ActionsParticulier(time.Now(), estParticulier, dejaInscrit, aPaye),
	}
	return dto, nil
}

func (s *InscriptionService) contexteParticulierEvenement(idUtilisateur, idEvenement int) (estParticulier, dejaInscrit, aPaye bool) {
	if idUtilisateur == 0 {
		return false, false, false
	}
	idPart, err := s.repo.IdParticulier(database.DB, idUtilisateur)
	if err != nil {
		return false, false, false
	}
	deja, _ := s.repo.EstInscritEvenement(database.DB, idPart, idEvenement)
	paye, _ := s.fact.UtilisateurAPayeEvenement(database.DB, idUtilisateur, idEvenement)
	return true, deja, paye
}

func (s *InscriptionService) InscrireFormation(idUtilisateur, idFormation int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.FormationPourMAJ(tx, idFormation)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Formation introuvable")
		}
		if err != nil {
			return err
		}

		deja, err := s.repo.EstInscritFormation(tx, idPart, idFormation)
		if err != nil {
			return err
		}
		if deja {
			return domain.Deja("Vous êtes déjà inscrit à cette formation")
		}

		if err := snap.PeutInscrire(time.Now()); err != nil {
			return err
		}

		aPaye, err := s.fact.UtilisateurAPayeFormation(tx, idUtilisateur, idFormation)
		if err != nil {
			return err
		}
		if err := domain.ExigePaiement(snap.Prix, aPaye); err != nil {
			return err
		}

		if err := s.repo.InsererReservationFormation(tx, idPart, idFormation); err != nil {
			return err
		}
		return s.repo.DecrementerPlacesFormation(tx, idFormation)
	})
}

func (s *InscriptionService) DesinscrireFormation(idUtilisateur, idFormation int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.FormationPourMAJ(tx, idFormation)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Formation introuvable")
		}
		if err != nil {
			return err
		}
		if err := snap.PeutDesinscrire(time.Now()); err != nil {
			return err
		}
		if snap.Prix > 0 {
			return domain.EtatInvalide("Désinscription d'une formation payante : merci de faire une demande de remboursement.")
		}

		retiree, err := s.LibererPlaceTx(tx, idPart, "formation", idFormation)
		if err != nil {
			return err
		}
		if !retiree {
			return domain.EtatInvalide("Vous n'êtes pas inscrit à cette formation")
		}
		return nil
	})
}

type FicheFormationDTO struct {
	ID                int      `json:"id"`
	Titre             string   `json:"titre"`
	Description       string   `json:"description"`
	Prix              float64  `json:"prix"`
	Duree             int      `json:"duree"`
	Statut            string   `json:"statut"`
	Date              string   `json:"date"`
	DateFin           string   `json:"date_fin"`
	PlacesTotal       int      `json:"places_total"`
	PlacesDispo       int      `json:"places_dispo"`
	Localisation      string   `json:"localisation"`
	Categorie         string   `json:"categorie"`
	EstInscrit        bool     `json:"est_inscrit"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

func (s *InscriptionService) FicheFormation(idUtilisateur, idFormation int) (FicheFormationDTO, error) {
	var dto FicheFormationDTO
	f, err := s.repo.FicheFormation(database.DB, idFormation)
	if errors.Is(err, sql.ErrNoRows) {
		return dto, domain.Introuvable("Formation introuvable")
	}
	if err != nil {
		return dto, err
	}

	estParticulier, dejaInscrit, aPaye := s.contexteParticulierFormation(idUtilisateur, idFormation)

	snap := domain.FormationSnapshot{
		Statut:      f.Statut,
		Date:        f.Date.Time,
		PlacesDispo: f.PlacesDispo,
		PlacesTotal: f.PlacesTotal,
		Prix:        f.Prix,
	}
	dto = FicheFormationDTO{
		ID: f.ID, Titre: f.Titre, Description: f.Description, Prix: f.Prix,
		Duree: f.Duree, Statut: f.Statut, Date: formatDate(f.Date), DateFin: formatDate(f.DateFin),
		PlacesTotal: f.PlacesTotal, PlacesDispo: f.PlacesDispo,
		Localisation: f.Localisation, Categorie: f.Categorie, EstInscrit: dejaInscrit,
		ActionsAutorisees: snap.ActionsParticulier(time.Now(), estParticulier, dejaInscrit, aPaye),
	}
	return dto, nil
}

func (s *InscriptionService) contexteParticulierFormation(idUtilisateur, idFormation int) (estParticulier, dejaInscrit, aPaye bool) {
	if idUtilisateur == 0 {
		return false, false, false
	}
	idPart, err := s.repo.IdParticulier(database.DB, idUtilisateur)
	if err != nil {
		return false, false, false
	}
	deja, _ := s.repo.EstInscritFormation(database.DB, idPart, idFormation)
	paye, _ := s.fact.UtilisateurAPayeFormation(database.DB, idUtilisateur, idFormation)
	return true, deja, paye
}

func formatDate(t sql.NullTime) string {
	if !t.Valid {
		return ""
	}
	return t.Time.Format(time.RFC3339Nano)
}
