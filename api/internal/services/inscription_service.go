package services

import (
	"database/sql"
	"errors"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// InscriptionService porte les cas d'usage d'inscription aux événements et
// formations. L'IDENTITÉ est toujours celle de l'utilisateur AUTHENTIFIÉ
// (idUtilisateur vient du JWT, jamais du corps de requête). Chaque écriture est
// transactionnelle, verrouille l'agrégat (FOR UPDATE) et délègue la décision au
// domaine — rôle + propriété + état source vérifiés avant toute mutation.
type InscriptionService struct {
	repo repository.InscriptionRepo
	fact repository.FacturationRepo
}

func NewInscriptionService() *InscriptionService { return &InscriptionService{} }

// resoudreParticulier mappe l'utilisateur authentifié vers son Id_Particuliers.
// Absence de ligne => l'utilisateur n'est pas un particulier (admin/salarié/pro) :
// 403 métier. Les inscriptions sont réservées aux particuliers (clé structurelle
// des tables Participer_evenements / Reserver_formation).
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

// --- Événements --------------------------------------------------------------

func (s *InscriptionService) ParticiperEvenement(idUtilisateur, idEvenement int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.EvenementPourMAJ(tx, idEvenement) // verrou FOR UPDATE
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

		// Occupation dérivée, lue sous le verrou : cohérente vis-à-vis des
		// inscriptions concurrentes au même événement (elles se sérialisent).
		snap.Participants, err = s.repo.CompterParticipantsEvenement(tx, idEvenement)
		if err != nil {
			return err
		}
		if err := snap.PeutParticiper(time.Now()); err != nil {
			return err
		}

		// Garde paiement (402) : un événement payant exige un règlement 'paye'
		// rattaché à CET événement avant l'inscription. Un événement gratuit
		// (Prix <= 0) passe sans condition — aucune régression du flux gratuit.
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

		n, err := s.repo.SupprimerParticipationEvenement(tx, idPart, idEvenement)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.EtatInvalide("Vous n'êtes pas inscrit à cet événement")
		}
		return nil
	})
}

// FicheEvenementDTO : projection d'affichage + état dérivé serveur.
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

// FicheEvenement charge l'événement pour affichage et calcule, CÔTÉ SERVEUR,
// est_inscrit et allowed_actions pour ce requérant (idUtilisateur == 0 => anonyme).
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

// contexteParticulierEvenement renvoie, pour le requérant, son statut de
// particulier, s'il est déjà inscrit et s'il a déjà réglé l'événement. aPaye
// pilote l'action exposée : un événement payant non réglé montre « payer »,
// strict reflet du 402 que renverrait l'inscription.
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

// --- Formations --------------------------------------------------------------

func (s *InscriptionService) InscrireFormation(idUtilisateur, idFormation int) error {
	return withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}

		snap, err := s.repo.FormationPourMAJ(tx, idFormation) // verrou FOR UPDATE
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

		// Décision AVANT toute écriture : ferme la fenêtre de sur-réservation où
		// l'INSERT précédait la décrémentation gardée.
		if err := snap.PeutInscrire(time.Now()); err != nil {
			return err
		}

		// Garde paiement (402) : une formation payante exige un règlement 'paye'
		// rattaché à CETTE formation. Une formation gratuite (Prix <= 0) passe.
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

		n, err := s.repo.SupprimerReservationFormation(tx, idPart, idFormation)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.EtatInvalide("Vous n'êtes pas inscrit à cette formation")
		}
		// Ne ré-incrémente QUE si une réservation a réellement été retirée, et
		// borné à Places_total : pas d'inflation du compteur.
		return s.repo.IncrementerPlacesFormation(tx, idFormation)
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
		Duree: f.Duree, Statut: f.Statut, Date: formatDate(f.Date),
		PlacesTotal: f.PlacesTotal, PlacesDispo: f.PlacesDispo,
		Localisation: f.Localisation, Categorie: f.Categorie, EstInscrit: dejaInscrit,
		ActionsAutorisees: snap.ActionsParticulier(time.Now(), estParticulier, dejaInscrit, aPaye),
	}
	return dto, nil
}

// contexteParticulierFormation : cf. contexteParticulierEvenement — aPaye expose
// « payer » plutôt qu'« inscrire » pour une formation payante non encore réglée.
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

// formatDate reproduit la sérialisation héritée (driver MySQL, parseTime=true)
// pour ne pas casser l'affichage côté front : DATETIME -> string RFC3339Nano.
func formatDate(t sql.NullTime) string {
	if !t.Valid {
		return ""
	}
	return t.Time.Format(time.RFC3339Nano)
}
