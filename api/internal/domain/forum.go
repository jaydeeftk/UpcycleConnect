package domain

import (
	"fmt"
	"strings"
	"unicode/utf8"
)

const (
	StatutSujetOuvert = "ouvert"
	StatutSujetResolu = "resolu"
	StatutSujetFerme  = "ferme"
)

const (
	SujetTitreMax     = 100
	SujetCategorieMax = 100
	ReponseContenuMax = 255
)

func NettoyerCategorie(c string) string {
	c = strings.TrimSpace(c)
	if c == "" {
		return "general"
	}
	return c
}

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

func PeutRepondre(statut string) error {
	if statut == StatutSujetFerme {
		return EtatInvalide("Ce sujet est fermé : aucune réponse ne peut y être ajoutée")
	}
	if statut == StatutSujetResolu {
		return EtatInvalide("Ce sujet est marqué comme résolu : aucune réponse ne peut y être ajoutée")
	}
	return nil
}

func PeutMarquerSolution(statut string) error {
	if statut == StatutSujetFerme {
		return EtatInvalide("Ce sujet est fermé : la solution ne peut être modifiée")
	}
	return nil
}

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
