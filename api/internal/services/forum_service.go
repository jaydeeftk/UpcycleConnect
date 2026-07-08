package services

import (
	"database/sql"
	"errors"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ForumService struct {
	repo repository.ForumRepo
}

func NewForumService() *ForumService { return &ForumService{} }

type SujetInput struct {
	Titre     string
	Contenu   string
	Categorie string
}

type SujetListeDTO struct {
	ID         int    `json:"id"`
	Titre      string `json:"titre"`
	Contenu    string `json:"contenu"`
	Categorie  string `json:"categorie"`
	Statut     string `json:"statut"`
	Date       string `json:"date"`
	Vues       int    `json:"vues"`
	Auteur     string `json:"auteur"`
	NbReponses int    `json:"nb_reponses"`
	Resolu     bool   `json:"resolu"`
}

type ReponseDTO struct {
	ID           int    `json:"id"`
	Contenu      string `json:"contenu"`
	Date         string `json:"date"`
	EstSolution  bool   `json:"est_solution"`
	AuteurID     int    `json:"auteur_id"`
	Auteur       string `json:"auteur"`
	AuteurStatut string `json:"auteur_statut"`
}

type SujetDetailDTO struct {
	ID                int          `json:"id"`
	Titre             string       `json:"titre"`
	Contenu           string       `json:"contenu"`
	Categorie         string       `json:"categorie"`
	Statut            string       `json:"statut"`
	Date              string       `json:"date"`
	Vues              int          `json:"vues"`
	AuteurID          int          `json:"auteur_id"`
	Auteur            string       `json:"auteur"`
	Resolu            bool         `json:"resolu"`
	Reponses          []ReponseDTO `json:"reponses"`
	ActionsAutorisees []string     `json:"allowed_actions"`
}

type SujetAdminDTO struct {
	ID         int    `json:"id"`
	Titre      string `json:"titre"`
	Categorie  string `json:"categorie"`
	Statut     string `json:"statut"`
	Date       string `json:"date"`
	Auteur     string `json:"auteur"`
	NbReponses int    `json:"nb_reponses"`
}

func nomComplet(nom, prenom string) string {
	return strings.TrimSpace(strings.TrimSpace(nom) + " " + strings.TrimSpace(prenom))
}

func (s *ForumService) ListerSujets(categorie string) ([]SujetListeDTO, error) {
	lignes, err := s.repo.ListerSujets(database.DB, categorie)
	if err != nil {
		return nil, err
	}
	out := make([]SujetListeDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, SujetListeDTO{
			ID: l.ID, Titre: l.Titre, Contenu: l.Contenu, Categorie: l.Categorie,
			Statut: l.Statut, Date: formatDate(l.Date), Vues: l.Vues,
			Auteur: nomComplet(l.AuteurNom, l.AuteurPrenom), NbReponses: l.NbReponses,
			Resolu: l.Statut == domain.StatutSujetResolu,
		})
	}
	return out, nil
}

func (s *ForumService) ConsulterSujet(idUtilisateur int, estAdmin bool, idSujet int) (SujetDetailDTO, error) {
	var dto SujetDetailDTO

	if err := s.repo.IncrementerVues(database.DB, idSujet); err != nil {
		return dto, err
	}

	e, err := s.repo.SujetParID(database.DB, idSujet)
	if errors.Is(err, sql.ErrNoRows) {
		return dto, domain.Introuvable("Sujet introuvable")
	}
	if err != nil {
		return dto, err
	}

	reps, err := s.repo.ReponsesDuSujet(database.DB, idSujet)
	if err != nil {
		return dto, err
	}
	reponses := make([]ReponseDTO, 0, len(reps))
	for _, r := range reps {
		reponses = append(reponses, ReponseDTO{
			ID: r.ID, Contenu: r.Contenu, Date: formatDate(r.Date), EstSolution: r.EstSolution,
			AuteurID: r.IdAuteur, Auteur: nomComplet(r.AuteurNom, r.AuteurPrenom),
			AuteurStatut: r.AuteurStatut,
		})
	}

	estAuthentifie := idUtilisateur != 0
	estAuteur := estAuthentifie && idUtilisateur == e.IdAuteur

	dto = SujetDetailDTO{
		ID: e.ID, Titre: e.Titre, Contenu: e.Contenu, Categorie: e.Categorie,
		Statut: e.Statut, Date: formatDate(e.Date), Vues: e.Vues,
		AuteurID: e.IdAuteur, Auteur: nomComplet(e.AuteurNom, e.AuteurPrenom),
		Resolu:            e.Statut == domain.StatutSujetResolu,
		Reponses:          reponses,
		ActionsAutorisees: domain.ActionsSujet(e.Statut, estAuthentifie, estAuteur, estAdmin),
	}
	return dto, nil
}

func (s *ForumService) ListerReponses(idSujet int) ([]ReponseDTO, error) {
	reps, err := s.repo.ReponsesDuSujet(database.DB, idSujet)
	if err != nil {
		return nil, err
	}
	out := make([]ReponseDTO, 0, len(reps))
	for _, r := range reps {
		out = append(out, ReponseDTO{
			ID: r.ID, Contenu: r.Contenu, Date: formatDate(r.Date), EstSolution: r.EstSolution,
			AuteurID: r.IdAuteur, Auteur: nomComplet(r.AuteurNom, r.AuteurPrenom),
			AuteurStatut: r.AuteurStatut,
		})
	}
	return out, nil
}

func (s *ForumService) AdminListerSujets() ([]SujetAdminDTO, error) {
	lignes, err := s.repo.ListerSujets(database.DB, "")
	if err != nil {
		return nil, err
	}
	out := make([]SujetAdminDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, SujetAdminDTO{
			ID: l.ID, Titre: l.Titre, Categorie: l.Categorie, Statut: l.Statut,
			Date: formatDate(l.Date), Auteur: nomComplet(l.AuteurNom, l.AuteurPrenom),
			NbReponses: l.NbReponses,
		})
	}
	return out, nil
}

func (s *ForumService) CreerSujet(idUtilisateur int, in SujetInput) (int64, error) {
	if idUtilisateur <= 0 {
		return 0, domain.Forbidden("Authentification requise pour publier")
	}
	cat := domain.NettoyerCategorie(in.Categorie)
	if err := domain.ValiderSujet(in.Titre, in.Contenu, cat); err != nil {
		return 0, err
	}
	return s.repo.CreerSujet(database.DB, idUtilisateur,
		strings.TrimSpace(in.Titre), strings.TrimSpace(in.Contenu), cat)
}

func (s *ForumService) RepondreSujet(idUtilisateur, idSujet int, contenu string) (int64, error) {
	if idUtilisateur <= 0 {
		return 0, domain.Forbidden("Authentification requise pour répondre")
	}
	if err := domain.ValiderReponse(contenu); err != nil {
		return 0, err
	}
	var id int64
	err := withTx(func(tx *sql.Tx) error {
		statut, _, err := s.repo.SujetStatutAuteurPourMAJ(tx, idSujet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Sujet introuvable")
		}
		if err != nil {
			return err
		}
		if err := domain.PeutRepondre(statut); err != nil {
			return err
		}
		id, err = s.repo.CreerReponse(tx, idSujet, idUtilisateur, strings.TrimSpace(contenu))
		return err
	})
	return id, err
}

func (s *ForumService) MarquerSolution(idUtilisateur int, estSalarie bool, idSujet, idReponse int) error {
	if idUtilisateur <= 0 {
		return domain.Forbidden("Authentification requise")
	}
	if !estSalarie {
		return domain.Forbidden("Seul un salarié peut désigner la solution")
	}
	return withTx(func(tx *sql.Tx) error {
		statut, _, err := s.repo.SujetStatutAuteurPourMAJ(tx, idSujet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Sujet introuvable")
		}
		if err != nil {
			return err
		}
		if err := domain.PeutMarquerSolution(statut); err != nil {
			return err
		}
		existe, err := s.repo.ReponseDansSujet(tx, idReponse, idSujet)
		if err != nil {
			return err
		}
		if !existe {
			return domain.Introuvable("Réponse introuvable dans ce sujet")
		}
		if err := s.repo.ReinitialiserSolutions(tx, idSujet); err != nil {
			return err
		}
		if err := s.repo.MarquerReponseSolution(tx, idReponse); err != nil {
			return err
		}
		return s.repo.MajStatutSujet(tx, idSujet, domain.StatutSujetResolu)
	})
}

func (s *ForumService) ModererSujet(idSujet int, action string) error {
	return withTx(func(tx *sql.Tx) error {
		statut, _, err := s.repo.SujetStatutAuteurPourMAJ(tx, idSujet)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Sujet introuvable")
		}
		if err != nil {
			return err
		}
		nouveau, err := domain.TransitionSujetModeration(statut, action)
		if err != nil {
			return err
		}
		return s.repo.MajStatutSujet(tx, idSujet, nouveau)
	})
}

func (s *ForumService) SupprimerSujet(idSujet int) error {
	return withTx(func(tx *sql.Tx) error {
		if err := s.repo.SupprimerReponsesDuSujet(tx, idSujet); err != nil {
			return err
		}
		n, err := s.repo.SupprimerSujet(tx, idSujet)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.Introuvable("Sujet introuvable")
		}
		return nil
	})
}

func (s *ForumService) SupprimerReponse(idReponse int) error {
	n, err := s.repo.SupprimerReponse(database.DB, idReponse)
	if err != nil {
		return err
	}
	if n == 0 {
		return domain.Introuvable("Réponse introuvable")
	}
	return nil
}

func (s *ForumService) SupprimerReponseUtilisateur(idUtilisateur, idReponse int) error {
	auteur, err := s.repo.AuteurReponse(database.DB, idReponse)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Introuvable("Réponse introuvable")
	}
	if err != nil {
		return err
	}
	if auteur != idUtilisateur {
		return domain.Forbidden("Vous ne pouvez supprimer que vos propres messages")
	}
	return s.SupprimerReponse(idReponse)
}
