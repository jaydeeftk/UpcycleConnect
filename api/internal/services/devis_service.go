package services

import (
	"database/sql"
	"errors"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type DevisDTO struct {
	ID           int     `json:"id"`
	IdDemande    int     `json:"id_demande"`
	Prix         float64 `json:"prix"`
	Message      string  `json:"message"`
	Statut       string  `json:"statut"`
	DateCreation string  `json:"date_creation"`
	NomPro       string  `json:"nom_pro"`
}

type DemandePrestaDTO struct {
	ID             int     `json:"id"`
	NomObjet       string  `json:"nom_objet"`
	Categorie      string  `json:"categorie"`
	TypeObjet      string  `json:"type_objet"`
	Etat           string  `json:"etat"`
	Description    string  `json:"description"`
	Localisation   string  `json:"localisation"`
	Budget         string  `json:"budget"`
	Statut         string  `json:"statut"`
	DateCreation   string  `json:"date_creation"`
	MonDevisID     int     `json:"mon_devis_id"`
	MonDevisStatut string  `json:"mon_devis_statut"`
	MonDevisPrix   float64 `json:"mon_devis_prix"`
}

type DevisService struct {
	repo repository.DevisRepo
}

func NewDevisService() *DevisService {
	return &DevisService{repo: repository.DevisRepo{}}
}

func (s *DevisService) ListerDemandesOuvertesPourPro(idPro int) ([]DemandePrestaDTO, error) {
	lignes, err := s.repo.ListerDemandesOuvertes(database.DB, idPro)
	if err != nil {
		return nil, err
	}
	out := make([]DemandePrestaDTO, 0, len(lignes))
	for _, d := range lignes {
		out = append(out, DemandePrestaDTO{
			ID: d.ID, NomObjet: d.NomObjet, Categorie: d.Categorie, TypeObjet: d.TypeObjet,
			Etat: d.Etat, Description: d.Description, Localisation: d.Localisation, Budget: d.Budget,
			Statut: d.Statut, DateCreation: d.DateCreation,
			MonDevisID: d.MonDevisID, MonDevisStatut: d.MonDevisStatut, MonDevisPrix: d.MonDevisPrix,
		})
	}
	return out, nil
}

func (s *DevisService) ProposerDevis(idPro, idDemande int, prix float64, message string) (int, error) {
	if idPro <= 0 {
		return 0, domain.Forbidden("Action réservée aux professionnels")
	}
	if err := domain.ValiderDevis(prix, message); err != nil {
		return 0, err
	}
	statutDemande, _, err := s.repo.DemandePourMAJ(database.DB, idDemande)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Introuvable("Demande introuvable")
	}
	if err != nil {
		return 0, err
	}
	if statutDemande != domain.StatutDemandePrestaOuverte {
		return 0, domain.EtatInvalide("Cette demande n'est plus ouverte aux devis")
	}

	idExistant, statutExistant, err := s.repo.TrouverExistant(database.DB, idDemande, idPro)
	if err == nil {
		if err := domain.PeutModifierDevis(statutExistant); err != nil {
			return 0, err
		}
		if err := s.repo.Modifier(database.DB, idExistant, prix, message); err != nil {
			return 0, err
		}
		return idExistant, nil
	}
	if !errors.Is(err, sql.ErrNoRows) {
		return 0, err
	}
	newID, err := s.repo.Creer(database.DB, idDemande, idPro, prix, message)
	return int(newID), err
}

func (s *DevisService) RetirerDevis(idPro, idDevis int) error {
	_, idProDuDevis, _, statut, err := s.repo.DevisPourMAJ(database.DB, idDevis)
	if errors.Is(err, sql.ErrNoRows) {
		return domain.Introuvable("Devis introuvable")
	}
	if err != nil {
		return err
	}
	if idProDuDevis != idPro {
		return domain.Forbidden("Ce devis ne vous appartient pas")
	}
	if err := domain.PeutRetirerDevis(statut); err != nil {
		return err
	}
	return s.repo.Retirer(database.DB, idDevis)
}

func (s *DevisService) ListerDevisPourDemande(idUtilisateur, idDemande int) ([]DevisDTO, error) {
	appartient, err := s.repo.DemandeAppartientA(database.DB, idDemande, idUtilisateur)
	if err != nil {
		return nil, err
	}
	if !appartient {
		return nil, domain.Forbidden("Cette demande ne vous appartient pas")
	}
	lignes, err := s.repo.ListerParDemande(database.DB, idDemande)
	if err != nil {
		return nil, err
	}
	out := make([]DevisDTO, 0, len(lignes))
	for _, d := range lignes {
		out = append(out, DevisDTO{
			ID: d.ID, IdDemande: d.IdDemande, Prix: d.Prix, Message: d.Message,
			Statut: d.Statut, DateCreation: d.DateCreation, NomPro: d.NomPro,
		})
	}
	return out, nil
}

func (s *DevisService) AnnulerDemande(idUtilisateur, idDemande int) error {
	appartient, err := s.repo.DemandeAppartientA(database.DB, idDemande, idUtilisateur)
	if err != nil {
		return err
	}
	if !appartient {
		return domain.Forbidden("Cette demande ne vous appartient pas")
	}
	statut, _, err := s.repo.DemandePourMAJ(database.DB, idDemande)
	if err != nil {
		return err
	}
	if err := domain.PeutAnnulerDemandePresta(statut); err != nil {
		return err
	}
	return s.repo.AnnulerDemande(database.DB, idDemande)
}

func (s *DevisService) PreparerAcceptation(idUtilisateur, idDevis int) (prix float64, nomObjet string, err error) {
	idDemande, _, prix, statutDevis, err := s.repo.DevisPourMAJ(database.DB, idDevis)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, "", domain.Introuvable("Devis introuvable")
	}
	if err != nil {
		return 0, "", err
	}
	appartient, err := s.repo.DemandeAppartientA(database.DB, idDemande, idUtilisateur)
	if err != nil {
		return 0, "", err
	}
	if !appartient {
		return 0, "", domain.Forbidden("Cette demande ne vous appartient pas")
	}
	statutDemande, _, err := s.repo.DemandePourMAJ(database.DB, idDemande)
	if err != nil {
		return 0, "", err
	}
	if err := domain.PeutAccepterDevis(statutDevis, statutDemande); err != nil {
		return 0, "", err
	}
	nomObjet, err = s.repo.NomEtObjetDemande(database.DB, idDemande)
	return prix, nomObjet, err
}

func (s *DevisService) FinaliserAcceptation(idUtilisateur, idDevis int, referenceStripe string) error {
	return withTx(func(tx *sql.Tx) error {
		idDemande, idPro, prix, statutDevis, err := s.repo.DevisPourMAJ(tx, idDevis)
		if err != nil {
			return err
		}
		if statutDevis == domain.StatutDevisAccepte {
			return nil
		}
		statutDemande, _, err := s.repo.DemandePourMAJ(tx, idDemande)
		if err != nil {
			return err
		}
		if err := domain.PeutAccepterDevis(statutDevis, statutDemande); err != nil {
			return err
		}
		if err := s.repo.MarquerAccepteAvecReference(tx, idDevis, referenceStripe); err != nil {
			return err
		}
		if err := s.repo.RefuserAutres(tx, idDemande, idDevis); err != nil {
			return err
		}
		if err := s.repo.AssignerProDemande(tx, idDemande, idPro); err != nil {
			return err
		}
		nomObjet, err := s.repo.NomEtObjetDemande(tx, idDemande)
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
				Statut: domain.StatutFacturePayee, Type: "devis_prestation", IdUtilisateur: idUtilisateur,
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
			Description: "Prestation : " + nomObjet, Quantite: 1, PrixUnitaireHT: ht, TotalHT: ht, IdFacture: idFacture,
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
			Montant: domain.Round2(taux * prix), IdDevis: idDevis, IdFacture: idFacture,
		}); err != nil {
			return err
		}

		if _, err := (repository.ProjetRepo{}).CreerPourPrestation(tx, nomObjet, idPro, idDemande); err != nil {
			return err
		}
		idProUtilisateur, err := facRepo.IdUtilisateurDuPro(tx, idPro)
		if err != nil {
			return err
		}
		return NewConversationService().DemarrerAvecMessageAutomatiquePourPrestation(
			tx, idDemande, idUtilisateur, idProUtilisateur,
			"Devis accepté pour \""+nomObjet+"\".", "devis_accepte",
		)
	})
}
