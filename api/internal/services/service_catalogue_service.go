package services

import (
	"database/sql"
	"errors"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ServiceCatalogueDTO struct {
	ID            int     `json:"id"`
	Titre         string  `json:"titre"`
	Description   string  `json:"description"`
	Prix          float64 `json:"prix"`
	Duree         int     `json:"duree"`
	Categorie     string  `json:"categorie"`
	NomAuteur     string  `json:"nom_auteur,omitempty"`
	TypeAuteur    string  `json:"type_auteur"`
	Booste        bool    `json:"booste"`
	AuteurPremium bool    `json:"auteur_premium"`
}

type CommandeServiceDTO struct {
	ID               int     `json:"id"`
	IdService        int     `json:"id_service"`
	TitreService     string  `json:"titre_service"`
	NomObjet         string  `json:"nom_objet"`
	PhotoURL         string  `json:"photo_url"`
	DescriptionObjet string  `json:"description_objet"`
	Prix             float64 `json:"prix"`
	Statut           string  `json:"statut"`
	DateCreation     string  `json:"date_creation"`
	NomClient        string  `json:"nom_client"`
}

type ServiceCatalogueService struct {
	repo repository.ServiceCatalogueRepo
}

func NewServiceCatalogueService() *ServiceCatalogueService {
	return &ServiceCatalogueService{repo: repository.ServiceCatalogueRepo{}}
}

func (s *ServiceCatalogueService) ListerCommandesUtilisateur(idUtilisateur int) ([]CommandeServiceDTO, error) {
	lignes, err := s.repo.ListerCommandesParUtilisateur(database.DB, idUtilisateur)
	if err != nil {
		return nil, err
	}
	out := make([]CommandeServiceDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, CommandeServiceDTO{
			ID: l.ID, IdService: l.IdService, TitreService: l.TitreService,
			NomObjet: l.NomObjet, PhotoURL: l.PhotoURL, DescriptionObjet: l.DescriptionObjet,
			Prix: l.Prix, Statut: l.Statut, DateCreation: l.DateCreation, NomClient: l.NomClient,
		})
	}
	return out, nil
}

func (s *ServiceCatalogueService) ListerCommandesPro(idPro int) ([]CommandeServiceDTO, error) {
	lignes, err := s.repo.ListerCommandesParPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]CommandeServiceDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, CommandeServiceDTO{
			ID: l.ID, IdService: l.IdService, TitreService: l.TitreService,
			NomObjet: l.NomObjet, PhotoURL: l.PhotoURL, DescriptionObjet: l.DescriptionObjet,
			Prix: l.Prix, Statut: l.Statut, DateCreation: l.DateCreation, NomClient: l.NomClient,
		})
	}
	return out, nil
}

func (s *ServiceCatalogueService) ListerCatalogue() ([]ServiceCatalogueDTO, error) {
	lignes, err := s.repo.ListerCatalogue(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]ServiceCatalogueDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, ServiceCatalogueDTO{
			ID: l.ID, Titre: l.Titre, Description: l.Description, Prix: l.Prix,
			Duree: l.Duree, Categorie: l.Categorie, NomAuteur: l.NomAuteur, TypeAuteur: l.TypeAuteur,
			Booste: l.Booste, AuteurPremium: l.AuteurPremium,
		})
	}
	return out, nil
}

func (s *ServiceCatalogueService) IdProDuService(idService int) (int, error) {
	return s.repo.IdProDuService(database.DB, idService)
}

func (s *ServiceCatalogueService) ListerPourPro(idPro int) ([]ServiceCatalogueDTO, error) {
	lignes, err := s.repo.ListerParPro(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]ServiceCatalogueDTO, 0, len(lignes))
	for _, l := range lignes {
		out = append(out, ServiceCatalogueDTO{
			ID: l.ID, Titre: l.Titre, Description: l.Description, Prix: l.Prix,
			Duree: l.Duree, Categorie: l.Categorie, TypeAuteur: "pro",
		})
	}
	return out, nil
}

func (s *ServiceCatalogueService) Creer(idPro int, titre, description string, prix float64, duree int, categorie string) (int, error) {
	if idPro <= 0 {
		return 0, domain.Forbidden("Action réservée aux professionnels")
	}
	if err := domain.ValiderServiceCatalogue(titre, prix); err != nil {
		return 0, err
	}
	id, err := s.repo.Creer(database.DB, idPro, titre, description, prix, duree, categorie)
	return int(id), err
}

func (s *ServiceCatalogueService) Modifier(idPro, idService int, titre, description string, prix float64, duree int, categorie string) error {
	if err := domain.ValiderServiceCatalogue(titre, prix); err != nil {
		return err
	}
	idProDuService, err := s.repo.IdProDuService(database.DB, idService)
	if err != nil {
		return err
	}
	if err := domain.PeutModifierServiceCatalogue(idProDuService, idPro); err != nil {
		return err
	}
	return s.repo.Modifier(database.DB, idService, titre, description, prix, duree, categorie)
}

func (s *ServiceCatalogueService) Supprimer(idPro, idService int) error {
	idProDuService, err := s.repo.IdProDuService(database.DB, idService)
	if err != nil {
		return err
	}
	if err := domain.PeutModifierServiceCatalogue(idProDuService, idPro); err != nil {
		return err
	}
	return s.repo.Supprimer(database.DB, idService)
}

func (s *ServiceCatalogueService) CreerCommande(idUtilisateur, idService int, nomObjet, descriptionObjet, photoURL string) (idCommande int, prix float64, titre string, err error) {
	if err = domain.ValiderCommandeService(nomObjet); err != nil {
		return
	}
	titre, prix, idPro, err := s.repo.ServicePourAchat(database.DB, idService)
	if errors.Is(err, sql.ErrNoRows) {
		err = domain.Introuvable("Prestation introuvable")
		return
	}
	if err != nil {
		return
	}
	if idPro == 0 {
		err = domain.Introuvable("Prestation introuvable")
		return
	}
	id, err := s.repo.CreerCommande(database.DB, idService, idUtilisateur, nomObjet, descriptionObjet, photoURL, prix)
	if err != nil {
		return
	}
	idCommande = int(id)
	return
}

func (s *ServiceCatalogueService) PreparerCheckout(idUtilisateur, idCommande int) (prix float64, titre string, err error) {
	idService, idUtilisateurCommande, prix, statut, err := s.repo.CommandePourMAJ(database.DB, idCommande)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, "", domain.Introuvable("Commande introuvable")
	}
	if err != nil {
		return 0, "", err
	}
	if idUtilisateurCommande != idUtilisateur {
		return 0, "", domain.Forbidden("Cette commande ne vous appartient pas")
	}
	if statut != domain.StatutCommandeServiceEnAttente {
		return 0, "", domain.EtatInvalide("Cette commande a déjà été traitée")
	}
	titreService, _, _, err := s.repo.ServicePourAchat(database.DB, idService)
	if err != nil {
		return 0, "", err
	}
	return prix, titreService, nil
}

func (s *ServiceCatalogueService) FinaliserPaiement(idCommande int, referenceStripe string) error {
	return withTx(func(tx *sql.Tx) error {
		idService, idUtilisateur, prix, statut, err := s.repo.CommandePourMAJ(tx, idCommande)
		if err != nil {
			return err
		}
		if statut == domain.StatutCommandeServicePayee {
			return nil
		}
		if err := s.repo.MarquerCommandePayee(tx, idCommande, referenceStripe); err != nil {
			return err
		}
		titreService, _, idPro, err := s.repo.ServicePourAchat(tx, idService)
		if err != nil {
			return err
		}
		nomObjet, err := s.repo.NomObjetCommande(tx, idCommande)
		if err != nil {
			return err
		}

		ttc := domain.Round2(prix)
		ht := domain.Round2(ttc / (1 + domain.TVAParDefaut/100))
		ttcCoherent := domain.CalculerTTC(ht, domain.TVAParDefaut)
		if err := domain.ValiderMontantsFacture(ht, domain.TVAParDefaut, ttcCoherent); err != nil {
			return err
		}
		facRepo := repository.FacturationRepo{}
		var idFacture int64
		var numero string
		for i := 0; i < nbTentativesNumero; i++ {
			numero = "FAC-" + time.Now().Format("20060102") + "-" + suffixeAleatoire(6)
			idFacture, err = facRepo.CreerFacture(tx, repository.FactureCreation{
				Numero: numero, MontantHT: ht, TVA: domain.TVAParDefaut, MontantTTC: ttcCoherent,
				Statut: domain.StatutFacturePayee, Type: "prestation_catalogue", IdUtilisateur: idUtilisateur,
			})
			if err == nil {
				break
			}
			if !facRepo.EstViolationUnicite(err) {
				return err
			}
		}
		if err != nil {
			return domain.Conflit("Impossible de générer un numéro de facture unique")
		}
		if err := facRepo.CreerLigneFacture(tx, repository.LigneFactureCreation{
			Description: titreService + " — " + nomObjet, Quantite: 1, PrixUnitaireHT: ht, TotalHT: ht, IdFacture: idFacture,
		}); err != nil {
			return err
		}
		if _, err := facRepo.CreerPaiement(tx, repository.PaiementCreation{
			Montant: ttcCoherent, Statut: domain.StatutPaiementPaye, Methode: domain.MethodePaiementCarte,
			ReferenceStripe: referenceStripe, IdFacture: idFacture, IdUtilisateur: idUtilisateur,
		}); err != nil {
			return err
		}
		taux := domain.TauxCommission()
		if err := facRepo.CreerCommission(tx, repository.CommissionCreation{
			Taux: domain.Round2(taux * 100), TauxApplique: taux,
			Montant: domain.Round2(taux * prix), IdCommandesServices: idCommande, IdFacture: idFacture,
		}); err != nil {
			return err
		}

		if _, err := (repository.ProjetRepo{}).CreerPourCommandeService(tx, titreService+" — "+nomObjet, idPro, idCommande); err != nil {
			return err
		}
		idProUtilisateur, err := facRepo.IdUtilisateurDuPro(tx, idPro)
		if err != nil {
			return err
		}
		return NewConversationService().DemarrerAvecMessageAutomatiquePourCommandeService(
			tx, idCommande, idUtilisateur, idProUtilisateur,
			"Prestation \""+titreService+"\" achetée.", "achat_service",
		)
	})
}
