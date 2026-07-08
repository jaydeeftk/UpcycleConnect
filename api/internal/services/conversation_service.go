package services

import (
	"database/sql"
	"errors"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ConversationDTO struct {
	ID             int    `json:"id"`
	IdAnnonce      int    `json:"id_annonce"`
	TitreAnnonce   string `json:"titre_annonce"`
	AutreNom       string `json:"autre_nom"`
	DernierMessage string `json:"dernier_message"`
	DateDernierMsg string `json:"date_dernier_message"`
	NonLus         int    `json:"non_lus"`
}

type MessageConversationDTO struct {
	ID             int    `json:"id"`
	Contenu        string `json:"contenu"`
	DateEnvoi      string `json:"date_envoi"`
	EstMoi         bool   `json:"est_moi"`
	EstAutomatique bool   `json:"est_automatique"`
	PeutDeposer    bool   `json:"peut_deposer"`
}

type ConversationService struct {
	repo repository.ConversationRepo
}

func NewConversationService() *ConversationService {
	return &ConversationService{repo: repository.ConversationRepo{}}
}

func texteEvenementPourRole(typeEvenement string, estVendeur bool, nomAutre string) (texte string, peutDeposer bool) {
	switch typeEvenement {
	case "achat":
		if estVendeur {
			return nomAutre + " a acheté votre annonce ! Vous devez maintenant faire votre demande de dépôt en conteneur.", true
		}
		return nomAutre + " a bien reçu votre achat. Nous allons procéder au dépôt de l'objet dans un conteneur.", false
	case "reservation_don":
		if estVendeur {
			return nomAutre + " a réservé votre don ! Vous devez maintenant faire votre demande de dépôt en conteneur.", true
		}
		return nomAutre + " a bien reçu votre réservation. Nous allons procéder au dépôt de l'objet dans un conteneur.", false
	case "devis_accepte":
		if estVendeur {
			return nomAutre + " a accepté votre devis ! Le suivi de la prestation se fait dans votre espace Projets.", false
		}
		return "Devis accepté ! Vous pouvez suivre l'avancement de la prestation avec " + nomAutre + " dans \"Mes prestations réservées\".", false
	case "achat_service":
		if estVendeur {
			return nomAutre + " a acheté votre prestation ! Le suivi se fait dans votre espace Projets.", false
		}
		return "Votre prestation avec " + nomAutre + " est confirmée. Vous pouvez suivre son avancement dans \"Mes prestations réservées\".", false
	default:
		return "", false
	}
}

func (s *ConversationService) DemarrerConversation(idUtilisateur, idAnnonce int) (int, error) {
	idVendeur, err := s.repo.VendeurDeAnnonce(database.DB, idAnnonce)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Introuvable("Annonce introuvable")
	}
	if err != nil {
		return 0, err
	}
	if idVendeur == 0 {
		return 0, domain.Introuvable("Annonce introuvable")
	}
	if idVendeur == idUtilisateur {
		return 0, domain.Invalide("Vous ne pouvez pas vous contacter vous-même")
	}
	return s.repo.TrouverOuCreer(database.DB, idAnnonce, idUtilisateur, idVendeur)
}

func (s *ConversationService) Lister(idUtilisateur int) ([]ConversationDTO, error) {
	lignes, err := s.repo.ListerPourUtilisateur(database.DB, idUtilisateur)
	if err != nil {
		return nil, err
	}
	out := make([]ConversationDTO, 0, len(lignes))
	for _, c := range lignes {
		dernierMessage := c.DernierMessage
		if c.DernierEstAuto && c.DernierTypeEvt != "" {
			estVendeur := idUtilisateur == c.IdVendeur
			texte, _ := texteEvenementPourRole(c.DernierTypeEvt, estVendeur, c.AutreNom)
			if texte != "" {
				dernierMessage = texte
			}
		}
		out = append(out, ConversationDTO{
			ID: c.ID, IdAnnonce: c.IdAnnonce, TitreAnnonce: c.TitreAnnonce,
			AutreNom: c.AutreNom, DernierMessage: dernierMessage,
			DateDernierMsg: c.DateDernierMsg, NonLus: c.NonLus,
		})
	}
	return out, nil
}

func (s *ConversationService) Masquer(idUtilisateur, idConversation int) error {
	appartient, err := s.repo.AppartientAUtilisateur(database.DB, idConversation, idUtilisateur)
	if err != nil {
		return err
	}
	if !appartient {
		return domain.Forbidden("Cette conversation ne vous appartient pas")
	}
	return s.repo.MasquerPourUtilisateur(database.DB, idConversation, idUtilisateur)
}

func (s *ConversationService) Messages(idUtilisateur, idConversation int) ([]MessageConversationDTO, error) {
	appartient, err := s.repo.AppartientAUtilisateur(database.DB, idConversation, idUtilisateur)
	if err != nil {
		return nil, err
	}
	if !appartient {
		return nil, domain.Forbidden("Accès refusé à cette conversation")
	}
	s.repo.MarquerLu(database.DB, idConversation, idUtilisateur)
	lignes, err := s.repo.ListerMessages(database.DB, idConversation)
	if err != nil {
		return nil, err
	}

	_, idVendeur, nomAcheteur, nomVendeur, err := s.repo.Parties(database.DB, idConversation)
	if err != nil {
		return nil, err
	}
	estVendeur := idUtilisateur == idVendeur
	nomAutre := nomVendeur
	if estVendeur {
		nomAutre = nomAcheteur
	}

	out := make([]MessageConversationDTO, 0, len(lignes))
	for _, m := range lignes {
		dto := MessageConversationDTO{
			ID: m.ID, DateEnvoi: m.DateEnvoi, EstMoi: m.IdExpediteur == idUtilisateur,
			EstAutomatique: m.EstAutomatique,
		}
		if m.EstAutomatique && m.TypeEvenement != "" {
			texte, peutDeposer := texteEvenementPourRole(m.TypeEvenement, estVendeur, nomAutre)
			if texte != "" {
				dto.Contenu = texte
			} else {
				dto.Contenu = m.Contenu
			}
			dto.PeutDeposer = peutDeposer
		} else {
			dto.Contenu = m.Contenu
		}
		out = append(out, dto)
	}
	return out, nil
}

func (s *ConversationService) Envoyer(idUtilisateur, idConversation int, contenu string) error {
	if err := domain.ValiderContenuMessageConversation(contenu); err != nil {
		return err
	}
	appartient, err := s.repo.AppartientAUtilisateur(database.DB, idConversation, idUtilisateur)
	if err != nil {
		return err
	}
	if !appartient {
		return domain.Forbidden("Accès refusé à cette conversation")
	}
	_, err = s.repo.CreerMessage(database.DB, idConversation, idUtilisateur, contenu)
	return err
}

func (s *ConversationService) DemarrerAvecMessageAutomatique(q repository.Querier, idAnnonce, idAcheteur, idVendeur int, contenuParDefaut, typeEvenement string) error {
	idConv, err := s.repo.TrouverOuCreer(q, idAnnonce, idAcheteur, idVendeur)
	if err != nil {
		return err
	}
	_, err = s.repo.CreerMessageAutomatique(q, idConv, idAcheteur, contenuParDefaut, typeEvenement)
	return err
}

func (s *ConversationService) DemarrerAvecMessageAutomatiquePourPrestation(q repository.Querier, idDemande, idAcheteur, idVendeur int, contenuParDefaut, typeEvenement string) error {
	idConv, err := s.repo.TrouverOuCreerPourPrestation(q, idDemande, idAcheteur, idVendeur)
	if err != nil {
		return err
	}
	_, err = s.repo.CreerMessageAutomatique(q, idConv, idAcheteur, contenuParDefaut, typeEvenement)
	return err
}

func (s *ConversationService) DemarrerAvecMessageAutomatiquePourCommandeService(q repository.Querier, idCommande, idAcheteur, idVendeur int, contenuParDefaut, typeEvenement string) error {
	idConv, err := s.repo.TrouverOuCreerPourCommandeService(q, idCommande, idAcheteur, idVendeur)
	if err != nil {
		return err
	}
	_, err = s.repo.CreerMessageAutomatique(q, idConv, idAcheteur, contenuParDefaut, typeEvenement)
	return err
}

type InfoConversationDTO struct {
	IdAnnonce    int    `json:"id_annonce"`
	TitreAnnonce string `json:"titre_annonce"`
}

func (s *ConversationService) InfoConversation(idUtilisateur, idConversation int) (*InfoConversationDTO, error) {
	appartient, err := s.repo.AppartientAUtilisateur(database.DB, idConversation, idUtilisateur)
	if err != nil {
		return nil, err
	}
	if !appartient {
		return nil, domain.Forbidden("Accès refusé à cette conversation")
	}
	idAnnonce, titre, err := s.repo.InfoConversation(database.DB, idConversation)
	if err != nil {
		return nil, err
	}
	return &InfoConversationDTO{IdAnnonce: idAnnonce, TitreAnnonce: titre}, nil
}
