package domain

import (
	"fmt"
	"math"
	"os"
	"strconv"
	"strings"
	"time"
)

const (
	StatutContratBrouillon = "brouillon"
	StatutContratActif     = "actif"
	StatutContratSuspendu  = "suspendu"
	StatutContratResilie   = "resilie"
	StatutContratExpire    = "expire"
)

func TransitionContrat(statut, action string) (string, error) {
	switch action {
	case "activer":
		if statut != StatutContratBrouillon {
			return "", EtatInvalide("Seul un contrat en brouillon peut être activé")
		}
		return StatutContratActif, nil
	case "suspendre":
		if statut != StatutContratActif {
			return "", EtatInvalide("Seul un contrat actif peut être suspendu")
		}
		return StatutContratSuspendu, nil
	case "reactiver":
		if statut != StatutContratSuspendu {
			return "", EtatInvalide("Seul un contrat suspendu peut être réactivé")
		}
		return StatutContratActif, nil
	case "resilier":
		if statut != StatutContratActif && statut != StatutContratSuspendu {
			return "", EtatInvalide("Seul un contrat actif ou suspendu peut être résilié")
		}
		return StatutContratResilie, nil
	case "expirer":
		if statut != StatutContratActif && statut != StatutContratSuspendu {
			return "", EtatInvalide("Seul un contrat actif ou suspendu peut expirer")
		}
		return StatutContratExpire, nil
	default:
		return "", Invalide("Action de contrat inconnue")
	}
}

func ActionsContratAdmin(statut string) []string {
	switch statut {
	case StatutContratBrouillon:
		return []string{"activer"}
	case StatutContratActif:
		return []string{"suspendre", "resilier", "expirer"}
	case StatutContratSuspendu:
		return []string{"reactiver", "resilier", "expirer"}
	default:
		return []string{}
	}
}

func ValiderContrat(typeContrat string, dateDebut, dateFin time.Time, idProfessionnel int) error {
	if strings.TrimSpace(typeContrat) == "" {
		return Invalide("Le type de contrat est obligatoire")
	}
	if idProfessionnel <= 0 {
		return Invalide("Le professionnel rattaché est obligatoire")
	}
	if !dateDebut.IsZero() && dateAvantAujourdhui(dateDebut) {
		return Invalide("La date de début ne peut pas être dans le passé")
	}
	if !dateDebut.IsZero() && !dateFin.IsZero() && dateFin.Before(dateDebut) {
		return Invalide("La date de fin ne peut précéder la date de début")
	}
	return nil
}

const (
	StatutAbonnementActif    = "actif"
	StatutAbonnementSuspendu = "suspendu"
	StatutAbonnementResilie  = "resilie"
	StatutAbonnementExpire   = "expire"
)

func ValiderAbonnement(typ string, prix float64) error {
	if strings.TrimSpace(typ) == "" {
		return Invalide("Le type d'abonnement est obligatoire")
	}
	if prix < 0 {
		return Invalide("Le prix d'un abonnement ne peut être négatif")
	}
	return nil
}

func TransitionAbonnement(statut, action string) (string, error) {
	switch action {
	case "suspendre":
		if statut != StatutAbonnementActif {
			return "", EtatInvalide("Seul un abonnement actif peut être suspendu")
		}
		return StatutAbonnementSuspendu, nil
	case "reactiver":
		if statut != StatutAbonnementSuspendu {
			return "", EtatInvalide("Seul un abonnement suspendu peut être réactivé")
		}
		return StatutAbonnementActif, nil
	case "resilier":
		if statut != StatutAbonnementActif && statut != StatutAbonnementSuspendu {
			return "", EtatInvalide("Seul un abonnement actif ou suspendu peut être résilié")
		}
		return StatutAbonnementResilie, nil
	case "expirer":
		if statut != StatutAbonnementActif && statut != StatutAbonnementSuspendu {
			return "", EtatInvalide("Seul un abonnement actif ou suspendu peut expirer")
		}
		return StatutAbonnementExpire, nil
	default:
		return "", Invalide("Action d'abonnement inconnue")
	}
}

func ActionsAbonnementAdmin(statut string) []string {
	switch statut {
	case StatutAbonnementActif:
		return []string{"suspendre", "resilier", "expirer"}
	case StatutAbonnementSuspendu:
		return []string{"reactiver", "resilier", "expirer"}
	default:
		return []string{}
	}
}

const TVAParDefaut = 20.0

const (
	TauxCommissionMin    = 0.05
	TauxCommissionMax    = 0.10
	TauxCommissionDefaut = 0.10
)

const TauxCommissionProPremium = 0.07

func TauxCommission() float64 {
	v := os.Getenv("COMMISSION_RATE")
	if v == "" {
		return TauxCommissionDefaut
	}
	f, err := strconv.ParseFloat(v, 64)
	if err != nil {
		return TauxCommissionDefaut
	}
	if f < TauxCommissionMin {
		return TauxCommissionMin
	}
	if f > TauxCommissionMax {
		return TauxCommissionMax
	}
	return f
}

const (
	StatutFacturePayee = "payee"
)

func Round2(x float64) float64 { return math.Round(x*100) / 100 }

func CalculerTTC(montantHT, tauxTVA float64) float64 {
	return Round2(montantHT * (1 + tauxTVA/100))
}

func ValiderMontantsFacture(montantHT, tauxTVA, montantTTC float64) error {
	if montantHT < 0 {
		return Invalide("Le montant HT ne peut être négatif")
	}
	if tauxTVA < 0 || tauxTVA > 100 {
		return Invalide("Le taux de TVA doit être compris entre 0 et 100")
	}
	attendu := CalculerTTC(montantHT, tauxTVA)
	if math.Abs(Round2(montantTTC)-attendu) > 0.005 {
		return Invalide(fmt.Sprintf("Montant TTC incohérent (attendu %.2f, reçu %.2f)", attendu, Round2(montantTTC)))
	}
	return nil
}

func CalculerCommission(taux, base float64) float64 {
	return Round2(taux / 100 * base)
}

func ValiderCommission(taux, base, montant float64) error {
	if taux < 0 || taux > 100 {
		return Invalide("Le taux de commission doit être compris entre 0 et 100")
	}
	if base < 0 {
		return Invalide("La base de commission ne peut être négative")
	}
	attendu := CalculerCommission(taux, base)
	if math.Abs(Round2(montant)-attendu) > 0.005 {
		return Invalide(fmt.Sprintf("Montant de commission incohérent (attendu %.2f)", attendu))
	}
	return nil
}

const (
	StatutPaiementPaye                   = "paye"
	StatutPaiementRembourse              = "rembourse"
	StatutPaiementRemboursementEnCours   = "remboursement_en_cours"
	StatutPaiementEnAttenteRemboursement = "en_attente_remboursement"
)

const (
	StatutDemandeRembEnAttente  = "en_attente"
	StatutDemandeRembApprouvee  = "approuvee"
	StatutDemandeRembRefusee    = "refusee"
	StatutDemandeRembRemboursee = "remboursee"
	StatutDemandeRembEchouee    = "echouee"
)

const (
	MethodePaiementCarte = "carte"
)

func ExigePaiement(prix float64, aPaye bool) error {
	if prix > 0 && !aPaye {
		return PaiementRequis("Cet article est payant : réglez-le avant de vous inscrire")
	}
	return nil
}
