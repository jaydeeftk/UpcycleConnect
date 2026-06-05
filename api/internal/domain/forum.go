package domain

import (
	"fmt"
	"strings"
	"unicode/utf8"
)

// =============================================================================
// Vertical Forum : Sujet / Réponse
//
// Logique métier PURE du forum d'entraide. L'auteur d'un sujet ou d'une réponse
// est un UTILISATEUR (un particulier OU un professionnel peut poster). Ce fichier
// porte : la machine à états du sujet, les invariants de contenu (bornes alignées
// sur le schéma) et la dérivation des actions autorisées (UI dérivée du serveur).
// Aucune I/O.
// =============================================================================

// -----------------------------------------------------------------------------
// Sujet — machine à états (chk_sujets_statut borne le même vocabulaire en base) :
//
//	ouvert  --marquer_solution (auteur)--> resolu
//	resolu  --retirer_solution (auteur)--> ouvert
//	ouvert|resolu --fermer (admin)--> ferme   (verrou de modération)
//	ferme   --rouvrir (admin)--> ouvert
//
// « ferme » interdit toute nouvelle réponse et tout (dé)marquage de solution.
// -----------------------------------------------------------------------------
const (
	StatutSujetOuvert = "ouvert"
	StatutSujetResolu = "resolu"
	StatutSujetFerme  = "ferme"
)

// StatutSujetValide garde la création/màj : on n'écrit jamais un statut hors
// vocabulaire (dernière ligne de défense : chk_sujets_statut).
func StatutSujetValide(s string) bool {
	switch s {
	case StatutSujetOuvert, StatutSujetResolu, StatutSujetFerme:
		return true
	}
	return false
}

// Bornes de contenu, alignées sur le schéma (Sujets.Titre VARCHAR(100),
// Sujets.Categorie VARCHAR(100), Reponses.Contenu VARCHAR(255)). On valide AVANT
// écriture pour renvoyer un 422 explicite plutôt que de laisser MySQL tronquer ou
// rejeter en aval.
const (
	SujetTitreMax     = 100
	SujetCategorieMax = 100
	ReponseContenuMax = 255
)

// NettoyerCategorie normalise la catégorie : vide => 'general'. La catégorie reste
// un vocabulaire LIBRE (fourni par le front) — pas de CHECK en base : on ne borne
// pas un vocabulaire qu'on ne maîtrise pas, à la différence du statut qui est
// dérivé du serveur. Seule sa longueur est validée (cf. ValiderSujet).
func NettoyerCategorie(c string) string {
	c = strings.TrimSpace(c)
	if c == "" {
		return "general"
	}
	return c
}

// ValiderSujet garantit les invariants d'un sujet AVANT insertion : titre et
// contenu présents, longueurs dans les bornes du schéma. La catégorie reçue est
// supposée déjà normalisée (NettoyerCategorie).
func ValiderSujet(titre, contenu, categorie string) error {
	t := strings.TrimSpace(titre)
	if t == "" {
		return Invalide("Le titre du sujet est obligatoire")
	}
	if utf8.RuneCountInString(t) > SujetTitreMax {
		return Invalide(fmt.Sprintf("Le titre ne peut dépasser %d caractères", SujetTitreMax))
	}
	if strings.TrimSpace(contenu) == "" {
		return Invalide("Le contenu du sujet est obligatoire")
	}
	if utf8.RuneCountInString(categorie) > SujetCategorieMax {
		return Invalide(fmt.Sprintf("La catégorie ne peut dépasser %d caractères", SujetCategorieMax))
	}
	return nil
}

// ValiderReponse garantit l'invariant d'une réponse : contenu présent et dans la
// borne du schéma (VARCHAR(255)).
func ValiderReponse(contenu string) error {
	c := strings.TrimSpace(contenu)
	if c == "" {
		return Invalide("Le contenu de la réponse est obligatoire")
	}
	if utf8.RuneCountInString(c) > ReponseContenuMax {
		return Invalide(fmt.Sprintf("La réponse ne peut dépasser %d caractères", ReponseContenuMax))
	}
	return nil
}

// PeutRepondre : on ne répond pas à un sujet fermé (modération). Ouvert ET resolu
// acceptent des réponses — une question résolue peut continuer à être discutée.
func PeutRepondre(statut string) error {
	if statut == StatutSujetFerme {
		return EtatInvalide("Ce sujet est fermé : aucune réponse ne peut y être ajoutée")
	}
	return nil
}

// PeutMarquerSolution : (dé)marquer une solution n'a de sens que sur un sujet non
// fermé. L'autorisation « auteur » est vérifiée par le service (propriété).
func PeutMarquerSolution(statut string) error {
	if statut == StatutSujetFerme {
		return EtatInvalide("Ce sujet est fermé : la solution ne peut être modifiée")
	}
	return nil
}

// TransitionSujetModeration est la SOURCE DE VÉRITÉ des leviers de modération
// admin : fermer verrouille le fil, rouvrir le remet en discussion. Renvoie le
// statut cible si la transition est licite, sinon une erreur typée (409/422).
func TransitionSujetModeration(statut, action string) (string, error) {
	switch action {
	case "fermer":
		if statut == StatutSujetFerme {
			return "", EtatInvalide("Le sujet est déjà fermé")
		}
		return StatutSujetFerme, nil
	case "rouvrir":
		if statut != StatutSujetFerme {
			return "", EtatInvalide("Seul un sujet fermé peut être rouvert")
		}
		return StatutSujetOuvert, nil
	default:
		return "", Invalide("Action de modération inconnue")
	}
}

// ActionsSujet dérive du SEUL état serveur les actions que le requérant peut
// entreprendre sur un sujet — base de la règle d'or « le front n'affiche que ce
// que le serveur autorise ». Un anonyme n'a aucune action.
//
//   - repondre        : tout authentifié, sujet non fermé ;
//   - marquer_solution: l'auteur du sujet, sujet non fermé (désigne la réponse) ;
//   - fermer/rouvrir/supprimer : l'admin (modération).
func ActionsSujet(statut string, estAuthentifie, estAuteur, estAdmin bool) []string {
	actions := []string{}
	if !estAuthentifie {
		return actions
	}
	if PeutRepondre(statut) == nil {
		actions = append(actions, "repondre")
	}
	if estAuteur && PeutMarquerSolution(statut) == nil {
		actions = append(actions, "marquer_solution")
	}
	if estAdmin {
		if statut == StatutSujetFerme {
			actions = append(actions, "rouvrir")
		} else {
			actions = append(actions, "fermer")
		}
		actions = append(actions, "supprimer")
	}
	return actions
}
