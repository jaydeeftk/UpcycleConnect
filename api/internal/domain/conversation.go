package domain

import "strings"

func ValiderContenuMessageConversation(contenu string) error {
	c := strings.TrimSpace(contenu)
	if c == "" {
		return Invalide("Le message ne peut pas être vide")
	}
	if len(c) > 1000 {
		return Invalide("Le message est trop long (1000 caractères maximum)")
	}
	return nil
}
