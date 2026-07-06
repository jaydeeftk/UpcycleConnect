package domain

import (
	"strings"
	"time"
)

const (
	StatutPubliciteActive  = "active"
	StatutPubliciteAnnulee = "annulee"
)

func ValiderPublicite(typ string, prix float64, dateDebut, dateFin time.Time) error {
	if strings.TrimSpace(typ) == "" {
		return Invalide("Le type de campagne est obligatoire")
	}
	if prix <= 0 {
		return Invalide("Le prix de la campagne doit être positif")
	}
	if dateDebut.IsZero() {
		return Invalide("La date de début est obligatoire")
	}
	if !dateFin.IsZero() && dateFin.Before(dateDebut) {
		return Invalide("La date de fin ne peut pas précéder la date de début")
	}
	return nil
}

func PeutAnnulerPublicite(statut string) error {
	if statut != StatutPubliciteActive {
		return EtatInvalide("Seule une campagne active peut être annulée")
	}
	return nil
}
