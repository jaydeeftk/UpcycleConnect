package services

import (
	"database/sql"
	"errors"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// ProjetService orchestre les PROJETS D'UPCYCLING d'un professionnel et leurs
// étapes : création, édition de contenu, machine à états (suspendre / reprendre /
// terminer / rouvrir), suppression, et CRUD des étapes.
//
// L'identité (idPro = Id_Professionnels) est TOUJOURS fournie par le handler
// depuis le JWT — jamais par le corps ni l'URL. Pour chaque écriture, le service
// verrouille le projet (FOR UPDATE), vérifie d'abord la PROPRIÉTÉ puis la
// précondition d'ÉTAT, avant d'écrire. La propriété est testée AVANT l'état :
// un projet ayant toujours un propriétaire, on refuse (403) sans révéler son état
// à un pro qui n'en est pas le propriétaire.
type ProjetService struct {
	repo repository.ProjetRepo
}

func NewProjetService() *ProjetService {
	return &ProjetService{}
}

// ProjetInput : données reçues à la création d'un projet.
type ProjetInput struct {
	Titre       string
	Description string
	DateDebut   string
	Statut      string
}

// ProjetContenuInput : données reçues à l'édition de contenu (statut exclu — il
// ne change que par transition explicite).
type ProjetContenuInput struct {
	Titre       string
	Description string
}

// EtapeInput : données reçues à l'ajout d'une étape.
type EtapeInput struct {
	Nom         string
	Description string
	Visuel      string
}

// ProjetDTO : vue d'un projet pour le professionnel. allowed_actions est dérivé
// de l'état serveur — le front n'affiche que ces actions.
type ProjetDTO struct {
	ID             int      `json:"id"`
	Titre          string   `json:"titre"`
	Description    string   `json:"description"`
	Statut         string   `json:"statut"`
	DateDebut      string   `json:"date_debut"`
	NbEtapes       int      `json:"nb_etapes"`
	AllowedActions []string `json:"allowed_actions"`
}

// EtapeDTO : vue d'une étape illustrée d'un projet.
type EtapeDTO struct {
	ID          int    `json:"id"`
	Nom         string `json:"nom"`
	Description string `json:"description"`
	Visuel      string `json:"visuel"`
}

// ListerProjets : projets du pro (lecture déjà bornée à sa propriété par le repo),
// chacun enrichi de ses allowed_actions dérivées de son statut.
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

// CreerProjet : crée un projet au nom du pro. Le titre est obligatoire ; le statut
// initial par défaut est en_cours et doit appartenir au vocabulaire canonique
// (sinon 422, avant toute écriture). Le propriétaire est l'idPro du JWT.
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

// ModifierProjet : édite titre/description d'un projet non figé. Propriété puis
// état (un projet terminé est figé -> 409) avant l'écriture.
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

// Suspendre : en_cours -> pause.
func (s *ProjetService) Suspendre(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutSuspendre, domain.StatutProjetPause)
}

// Reprendre : pause -> en_cours.
func (s *ProjetService) Reprendre(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutReprendre, domain.StatutProjetEnCours)
}

// Terminer : {en_cours, pause} -> termine.
func (s *ProjetService) Terminer(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutTerminer, domain.StatutProjetTermine)
}

// Rouvrir : termine -> en_cours.
func (s *ProjetService) Rouvrir(idPro, idProjet int) error {
	return s.appliquerTransition(idPro, idProjet, domain.ProjetSnapshot.PeutRouvrir, domain.StatutProjetEnCours)
}

// appliquerTransition factorise les quatre transitions de statut : verrou,
// propriété, garde d'état (passée en paramètre), puis écriture du statut cible.
// Tout en transaction (pas d'effet de bord partiel).
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

// SupprimerProjet : retire le projet et ses étapes (FK), en transaction. Possible
// dans n'importe quel état mais réservé au propriétaire.
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

// ListerEtapes : étapes d'un projet, après vérification de propriété (un pro ne
// lit pas les étapes du projet d'un autre -> 403 ; projet absent -> 404).
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

// AjouterEtape : ajoute une étape à un projet non figé. Propriété puis état
// (terminé = figé -> 409) ; le nom est obligatoire.
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
		idEtape, err = s.repo.CreerEtape(tx, idProjet, repository.EtapeCreation{
			Nom: nom, Description: in.Description, Visuel: in.Visuel,
		})
		return err
	})
	return idEtape, err
}

// SupprimerEtape : retire une étape après avoir résolu son projet parent pour en
// vérifier la propriété et l'état (projet figé -> 409). Étape absente -> 404.
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
