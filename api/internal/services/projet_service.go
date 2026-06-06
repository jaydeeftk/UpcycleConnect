package services

import (
	"database/sql"
	"errors"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ProjetService struct {
	repo repository.ProjetRepo
}

func NewProjetService() *ProjetService {
	return &ProjetService{}
}

type ProjetInput struct {
	Titre       string
	Description string
	DateDebut   string
	Statut      string
}

type ProjetContenuInput struct {
	Titre       string
	Description string
}

type EtapeInput struct {
	Nom         string
	Description string
	Visuel      string
}

type ProjetDTO struct {
	ID             int      `json:"id"`
	Titre          string   `json:"titre"`
	Description    string   `json:"description"`
	Statut         string   `json:"statut"`
	DateDebut      string   `json:"date_debut"`
	NbEtapes       int      `json:"nb_etapes"`
	AllowedActions []string `json:"allowed_actions"`
}

type EtapeDTO struct {
	ID          int    `json:"id"`
	Nom         string `json:"nom"`
	Description string `json:"description"`
	Visuel      string `json:"visuel"`
}

func (s *ProjetService) ListerProjets(idPro int) ([]ProjetDTO, error) {
	lignes, err := s.repo.ListerParPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]ProjetDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, ProjetDTO{
			ID:             l.ID,
			Titre:          l.Titre,
			Description:    l.Description,
			Statut:         l.Statut,
			DateDebut:      l.DateDebut,
			NbEtapes:       l.NbEtapes,
			AllowedActions: domain.ActionsProjetPro(l.Statut),
		})
	}
	return out, nil
}

func (s *ProjetService) CreerProjet(idPro int, in ProjetInput) (int, error) {
	if idPro <= 0 {
		return 0, domain.Forbidden("Action réservée aux professionnels")
	}
	titre := strings.TrimSpace(in.Titre)
	if titre == "" {
		return 0, domain.Invalide("Le titre du projet est obligatoire")
	}
	statut := in.Statut
	if statut == "" {
		statut = domain.StatutProjetEnCours
	}
	if !domain.StatutProjetValide(statut) {
		return 0, domain.Invalide("Statut de projet invalide")
	}
	return s.repo.Creer(database.DB, repository.ProjetCreation{
		Titre:       titre,
		Description: in.Description,
		DateDebut:   in.DateDebut,
		Statut:      statut,
		IdPro:       idPro,
	})
}

func (s *ProjetService) ModifierProjet(idPro, idProjet int, in ProjetContenuInput) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	titre := strings.TrimSpace(in.Titre)
	if titre == "" {
		return domain.Invalide("Le titre du projet est obligatoire")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.ProjetPourMAJ(tx, idProjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Projet introuvable")
		} else if err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Ce projet n'est pas le vôtre")
		}
		if err := snap.PeutModifierContenu(); err != nil {
			return err
		}
		return s.repo.MettreAJourContenu(tx, idProjet, titre, in.Description)
	})
}

func (s *ProjetService) Suspendre(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutSuspendre, domain.StatutProjetPause)
}

func (s *ProjetService) Reprendre(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutReprendre, domain.StatutProjetEnCours)
}

func (s *ProjetService) Terminer(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutTerminer, domain.StatutProjetTermine)
}

func (s *ProjetService) Rouvrir(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutRouvrir, domain.StatutProjetEnCours)
}

func (s *ProjetService) appliquerTransition(idPro, idProjet int, garde func(domain.ProjetSnapshot) error, statutCible string) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.ProjetPourMAJ(tx, idProjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Projet introuvable")
		} else if err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Ce projet n'est pas le vôtre")
		}
		if err := garde(snap); err != nil {
			return err
		}
		return s.repo.MettreAJourStatut(tx, idProjet, statutCible)
	})
}

func (s *ProjetService) SupprimerProjet(idPro, idProjet int) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.ProjetPourMAJ(tx, idProjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Projet introuvable")
		} else if err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Ce projet n'est pas le vôtre")
		}
		if err := s.repo.SupprimerEtapesDuProjet(tx, idProjet); err != nil {
			return err
		}
		return s.repo.Supprimer(tx, idProjet)
	})
}

func (s *ProjetService) ListerEtapes(idPro, idProjet int) ([]EtapeDTO, error) {
	if idPro <= 0 {
		return nil, domain.Forbidden("Action réservée aux professionnels")
	}
	snap, err := s.repo.ChargerProjet(database.DB, idProjet)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, domain.Introuvable("Projet introuvable")
	} else if err != nil {
		return nil, err
	}
	if !snap.AppartientAuPro(idPro) {
		return nil, domain.Forbidden("Ce projet n'est pas le vôtre")
	}
	lignes, err := s.repo.ListerEtapes(database.DB, idProjet)
	if err != nil {
		return nil, err
	}
	out := make([]EtapeDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, EtapeDTO{ID: l.ID, Nom: l.Nom, Description: l.Description, Visuel: l.Visuel})
	}
	return out, nil
}

func (s *ProjetService) AjouterEtape(idPro, idProjet int, in EtapeInput) (int, error) {
	if idPro <= 0 {
		return 0, domain.Forbidden("Action réservée aux professionnels")
	}
	nom := strings.TrimSpace(in.Nom)
	if nom == "" {
		return 0, domain.Invalide("Le nom de l'étape est obligatoire")
	}
	var idEtape int
	err := withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.ProjetPourMAJ(tx, idProjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Projet introuvable")
		} else if err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Ce projet n'est pas le vôtre")
		}
		if err := snap.PeutModifierContenu(); err != nil {
			return err
		}
		idEtape, err = s.repo.CreerEtape(tx, idProjet, repository.EtapeCreation{Nom: nom, Description: in.Description, Visuel: in.Visuel})
		return err
	})
	return idEtape, err
}

func (s *ProjetService) SupprimerEtape(idPro, idEtape int) error {
	if idPro <= 0 {
		return domain.Forbidden("Action réservée aux professionnels")
	}
	return withTx(func(tx *sql.Tx) error {
		idProjet, err := s.repo.ProjetIdDeLEtape(tx, idEtape)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Étape introuvable")
		} else if err != nil {
			return err
		}
		snap, err := s.repo.ProjetPourMAJ(tx, idProjet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Projet introuvable")
		} else if err != nil {
			return err
		}
		if !snap.AppartientAuPro(idPro) {
			return domain.Forbidden("Ce projet n'est pas le vôtre")
		}
		if err := snap.PeutModifierContenu(); err != nil {
			return err
		}
		return s.repo.SupprimerEtape(tx, idEtape)
	})
}
