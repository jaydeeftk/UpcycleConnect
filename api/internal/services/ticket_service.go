package services

import (
	"database/sql"
	"errors"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type TicketDTO struct {
	ID             int    `json:"id"`
	Statut         string `json:"statut"`
	DateCreation   string `json:"date_creation"`
	IdAdminAssigne *int   `json:"id_admin_assigne,omitempty"`
	NomParticulier string `json:"nom_particulier,omitempty"`
	NomAdmin       string `json:"nom_admin,omitempty"`
	DernierMessage string `json:"dernier_message,omitempty"`
	DateDernierMsg string `json:"date_dernier_message,omitempty"`
}

type MessageTicketDTO struct {
	ID        int    `json:"id"`
	Contenu   string `json:"contenu"`
	DateEnvoi string `json:"date_envoi"`
	EstMoi    bool   `json:"est_moi"`
}

type TicketService struct {
	repo repository.TicketRepo
}

func NewTicketService() *TicketService {
	return &TicketService{repo: repository.TicketRepo{}}
}

func (s *TicketService) MonTicketOuvert(idParticulier int) (*TicketDTO, error) {
	t, err := s.repo.TicketOuvertDuParticulier(database.DB, idParticulier)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &TicketDTO{ID: t.ID, Statut: t.Statut}, nil
}

func (s *TicketService) ParticulierEnvoyerMessage(idParticulier int, contenu string) (int, error) {
	if err := domain.ValiderContenuMessageTicket(contenu); err != nil {
		return 0, err
	}
	t, err := s.repo.TicketOuvertDuParticulier(database.DB, idParticulier)
	var idTicket int
	if errors.Is(err, sql.ErrNoRows) {
		idTicket, err = s.repo.Creer(database.DB, idParticulier)
		if err != nil {
			return 0, err
		}
	} else if err != nil {
		return 0, err
	} else {
		idTicket = t.ID
	}
	if _, err := s.repo.CreerMessage(database.DB, idTicket, idParticulier, contenu); err != nil {
		return 0, err
	}
	return idTicket, nil
}

func (s *TicketService) AdminEnvoyerMessage(idAdmin, idUtilisateur int, contenu string) (int, error) {
	if err := domain.ValiderContenuMessageTicket(contenu); err != nil {
		return 0, err
	}
	var exists int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&exists)
	if exists == 0 {
		return 0, domain.Introuvable("Utilisateur introuvable")
	}
	t, err := s.repo.TicketOuvertEntreAdminEtUtilisateur(database.DB, idUtilisateur, idAdmin)
	var idTicket int
	if errors.Is(err, sql.ErrNoRows) {
		idTicket, err = s.repo.CreerParAdmin(database.DB, idUtilisateur, idAdmin)
		if err != nil {
			return 0, err
		}
	} else if err != nil {
		return 0, err
	} else {
		idTicket = t.ID
	}
	if _, err := s.repo.CreerMessage(database.DB, idTicket, idAdmin, contenu); err != nil {
		return 0, err
	}
	return idTicket, nil
}

func (s *TicketService) Messages(idUtilisateur int, estAdmin bool, idTicket int) ([]MessageTicketDTO, error) {
	t, err := s.repo.ParID(database.DB, idTicket)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, domain.Introuvable("Ticket introuvable")
	}
	if err != nil {
		return nil, err
	}
	autorise := t.IdParticulier == idUtilisateur
	if estAdmin && t.IdAdminAssigne != nil && *t.IdAdminAssigne == idUtilisateur {
		autorise = true
	}
	if !autorise {
		return nil, domain.Forbidden("Accès refusé à ce ticket")
	}
	lignes, err := s.repo.ListerMessages(database.DB, idTicket)
	if err != nil {
		return nil, err
	}
	out := make([]MessageTicketDTO, 0, len(lignes))
	for _, m := range lignes {
		out = append(out, MessageTicketDTO{
			ID: m.ID, Contenu: m.Contenu, DateEnvoi: m.DateEnvoi,
			EstMoi: m.IdExpediteur == idUtilisateur,
		})
	}
	return out, nil
}

func (s *TicketService) EnvoyerDansTicket(idUtilisateur int, estAdmin bool, idTicket int, contenu string) error {
	if err := domain.ValiderContenuMessageTicket(contenu); err != nil {
		return err
	}
	t, err := s.repo.ParID(database.DB, idTicket)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Introuvable("Ticket introuvable")
	}
	if err != nil {
		return err
	}
	autorise := t.IdParticulier == idUtilisateur
	if estAdmin && t.IdAdminAssigne != nil && *t.IdAdminAssigne == idUtilisateur {
		autorise = true
	}
	if !autorise {
		return domain.Forbidden("Accès refusé à ce ticket")
	}
	if err := domain.PeutEnvoyerMessageTicket(t.Statut); err != nil {
		return err
	}
	_, err = s.repo.CreerMessage(database.DB, idTicket, idUtilisateur, contenu)
	return err
}

func (s *TicketService) Fermer(idUtilisateur int, estAdmin bool, idTicket int) error {
	t, err := s.repo.ParID(database.DB, idTicket)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Introuvable("Ticket introuvable")
	}
	if err != nil {
		return err
	}
	autorise := estAdmin && t.IdAdminAssigne != nil && *t.IdAdminAssigne == idUtilisateur
	if !autorise {
		return domain.Forbidden("Seul un administrateur peut fermer ce ticket")
	}
	if err := domain.PeutFermerTicket(t.Statut); err != nil {
		return err
	}
	return s.repo.Fermer(database.DB, idTicket)
}

func (s *TicketService) HistoriqueParticulier(idParticulier int) ([]TicketDTO, error) {
	lignes, err := s.repo.ListerDuParticulier(database.DB, idParticulier)
	if err != nil {
		return nil, err
	}
	out := make([]TicketDTO, 0, len(lignes))
	for _, t := range lignes {
		out = append(out, TicketDTO{
			ID: t.ID, Statut: t.Statut, DateCreation: t.DateCreation, IdAdminAssigne: t.IdAdminAssigne,
			NomAdmin: t.NomAdmin, DernierMessage: t.DernierMessage, DateDernierMsg: t.DateDernierMsg,
		})
	}
	return out, nil
}

func (s *TicketService) ListerPourAdmin() ([]TicketDTO, error) {
	lignes, err := s.repo.ListerTous(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]TicketDTO, 0, len(lignes))
	for _, t := range lignes {
		out = append(out, TicketDTO{
			ID: t.ID, Statut: t.Statut, DateCreation: t.DateCreation, IdAdminAssigne: t.IdAdminAssigne,
			NomParticulier: t.NomParticulier, NomAdmin: t.NomAdmin,
			DernierMessage: t.DernierMessage, DateDernierMsg: t.DateDernierMsg,
		})
	}
	return out, nil
}

func (s *TicketService) Accepter(idAdmin, idTicket int) error {
	t, err := s.repo.ParID(database.DB, idTicket)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Introuvable("Ticket introuvable")
	}
	if err != nil {
		return err
	}
	if err := domain.PeutAccepterTicket(t.Statut); err != nil {
		return err
	}
	n, err := s.repo.Accepter(database.DB, idTicket, idAdmin)
	if err != nil {
		return err
	}
	if n == 0 {
		return domain.Conflit("Ce ticket vient d'être pris en charge par un autre administrateur")
	}
	return nil
}
