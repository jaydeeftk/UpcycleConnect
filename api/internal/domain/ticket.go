package domain

import "strings"

const (
	StatutTicketEnAttente = "en_attente"
	StatutTicketFerme     = "ferme"
)

func ValiderContenuMessageTicket(contenu string) error {
	c := strings.TrimSpace(contenu)
	if c == "" {
		return Invalide("Le message ne peut pas être vide")
	}
	if len(c) > 1000 {
		return Invalide("Le message est trop long (1000 caractères maximum)")
	}
	return nil
}

func PeutEnvoyerMessageTicket(statut string) error {
	if statut == StatutTicketFerme {
		return EtatInvalide("Ce ticket est fermé")
	}
	return nil
}

func PeutAccepterTicket(statut string) error {
	if statut != StatutTicketEnAttente {
		return EtatInvalide("Ce ticket a déjà été pris en charge")
	}
	return nil
}

func PeutFermerTicket(statut string) error {
	if statut == StatutTicketFerme {
		return EtatInvalide("Ce ticket est déjà fermé")
	}
	return nil
}
