package domain

import (
	"fmt"
	"math"
	"strings"
	"time"
)

// =============================================================================
// Vertical Facturation : Contrat / Abonnement / Facture / Commission / Paiement
//
// Ce fichier porte la logique métier PURE de la facturation : machines à états
// (contrat, abonnement), invariants monétaires (HT/TVA/TTC, commission) et la
// règle d'accès « article payant => paiement préalable » (402). Aucune I/O.
// =============================================================================

// -----------------------------------------------------------------------------
// Contrat — machine à états (chk_contrats_statut borne le même vocabulaire en base)
//
//	brouillon --activer--> actif
//	actif     --suspendre--> suspendu
//	suspendu  --reactiver--> actif
//	actif|suspendu --resilier--> resilie   (terminal)
//	actif|suspendu --expirer--> expire     (terminal, échéance atteinte)
//
// -----------------------------------------------------------------------------
const (
	StatutContratBrouillon = "brouillon"
	StatutContratActif     = "actif"
	StatutContratSuspendu  = "suspendu"
	StatutContratResilie   = "resilie"
	StatutContratExpire    = "expire"
)

// StatutContratValide garde la création/màj : on n'écrit jamais un statut hors
// vocabulaire (dernière ligne de défense : chk_contrats_statut).
func StatutContratValide(s string) bool {
	switch s {
	case StatutContratBrouillon, StatutContratActif, StatutContratSuspendu,
		StatutContratResilie, StatutContratExpire:
		return true
	}
	return false
}

// TransitionContrat est la SOURCE DE VÉRITÉ des transitions admin : elle renvoie
// le statut cible si la transition est licite depuis le statut courant, sinon une
// erreur typée (409 EtatInvalide / 422 Invalide). ActionsContratAdmin en dérive
// les boutons : les deux ne peuvent diverger.
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

// ActionsContratAdmin dérive du seul statut les transitions déclenchables. Le
// front n'affiche QUE ces actions ; le serveur refuserait toute autre.
func ActionsContratAdmin(statut string) []string {
	switch statut {
	case StatutContratBrouillon:
		return []string{"activer"}
	case StatutContratActif:
		return []string{"suspendre", "resilier", "expirer"}
	case StatutContratSuspendu:
		return []string{"reactiver", "resilier", "expirer"}
	default: // resilie, expire : terminal
		return []string{}
	}
}

// ValiderContrat vérifie les invariants d'un contrat AVANT insertion : type
// présent, professionnel rattaché, et cohérence des dates (fin >= début). Les
// dates « zéro » (non fournies) ne déclenchent pas la comparaison.
func ValiderContrat(typeContrat string, dateDebut, dateFin time.Time, idProfessionnel int) error {
	if strings.TrimSpace(typeContrat) == "" {
		return Invalide("Le type de contrat est obligatoire")
	}
	if idProfessionnel <= 0 {
		return Invalide("Le professionnel rattaché est obligatoire")
	}
	if !dateDebut.IsZero() && !dateFin.IsZero() && dateFin.Before(dateDebut) {
		return Invalide("La date de fin ne peut précéder la date de début")
	}
	return nil
}

// -----------------------------------------------------------------------------
// Abonnement — machine à états (catalogue d'offres). Id_Abonnement est un VARCHAR.
// -----------------------------------------------------------------------------
const (
	StatutAbonnementActif    = "actif"
	StatutAbonnementSuspendu = "suspendu"
	StatutAbonnementResilie  = "resilie"
	StatutAbonnementExpire   = "expire"
)

func StatutAbonnementValide(s string) bool {
	switch s {
	case StatutAbonnementActif, StatutAbonnementSuspendu,
		StatutAbonnementResilie, StatutAbonnementExpire:
		return true
	}
	return false
}

// ValiderAbonnement : type présent, prix non négatif.
func ValiderAbonnement(typ string, prix float64) error {
	if strings.TrimSpace(typ) == "" {
		return Invalide("Le type d'abonnement est obligatoire")
	}
	if prix < 0 {
		return Invalide("Le prix d'un abonnement ne peut être négatif")
	}
	return nil
}

// TransitionAbonnement : même algèbre que le contrat (sans l'état brouillon).
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

// -----------------------------------------------------------------------------
// Facture — invariants monétaires. Le schéma stocke HT, TVA (%), TTC en
// DECIMAL(_,2) : la cohérence TTC = HT x (1 + TVA/100) est une INVARIANTE, pas
// une valeur libre transmise par un appelant.
// -----------------------------------------------------------------------------
const TVAParDefaut = 20.0

const (
	StatutFactureBrouillon = "brouillon"
	StatutFactureEmise     = "emise"
	StatutFacturePayee     = "payee"
	StatutFactureAnnulee   = "annulee"
)

func StatutFactureValide(s string) bool {
	switch s {
	case StatutFactureBrouillon, StatutFactureEmise, StatutFacturePayee, StatutFactureAnnulee:
		return true
	}
	return false
}

// Round2 reproduit la précision DECIMAL(_,2) : tous les montants métier sont
// arrondis au centime pour que les comparaisons d'invariants soient stables.
func Round2(x float64) float64 { return math.Round(x*100) / 100 }

// CalculerTTC dérive le TTC d'un HT et d'un taux de TVA exprimé en pourcentage.
func CalculerTTC(montantHT, tauxTVA float64) float64 {
	return Round2(montantHT * (1 + tauxTVA/100))
}

// ValiderMontantsFacture garantit l'invariant HT/TVA/TTC. La tolérance 0.005
// absorbe les erreurs de représentation flottante sans laisser passer un écart
// d'un centime.
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

// -----------------------------------------------------------------------------
// Commission — prélèvement de la plateforme sur une vente d'annonce. Montant
// stocké = Taux(%) x base ; on ne fait jamais confiance à un montant pré-calculé
// par l'appelant.
// -----------------------------------------------------------------------------

// CalculerCommission dérive le montant d'une commission d'un taux (%) et d'une base.
func CalculerCommission(taux, base float64) float64 {
	return Round2(taux / 100 * base)
}

// ValiderCommission garantit l'invariant Montant = Taux/100 x base.
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

// -----------------------------------------------------------------------------
// Paiement — vocabulaires bornés (chk_paiements_statut / chk_paiements_methode).
// -----------------------------------------------------------------------------
const (
	StatutPaiementEnAttente = "en_attente"
	StatutPaiementPaye      = "paye"
	StatutPaiementEchoue    = "echoue"
	StatutPaiementRembourse = "rembourse"
)

const (
	MethodePaiementCarte    = "carte"
	MethodePaiementVirement = "virement"
	MethodePaiementEspeces  = "especes"
	MethodePaiementCheque   = "cheque"
)

// -----------------------------------------------------------------------------
// Règle d'accès payant — le 402 du vertical inscription. Un article gratuit
// (prix <= 0) ne déclenche jamais la règle ; un article payant exige un paiement
// préalable enregistré.
// -----------------------------------------------------------------------------

// ExigePaiement interdit l'accès gratuit à un article payant : si le prix est
// strictement positif et qu'aucun paiement n'est enregistré, l'action requiert
// d'abord un règlement (402 PaiementRequis).
func ExigePaiement(prix float64, aPaye bool) error {
	if prix > 0 && !aPaye {
		return PaiementRequis("Cet article est payant : réglez-le avant de vous inscrire")
	}
	return nil
}
