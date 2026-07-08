package domain

import (
	"strings"
	"time"
)

const (
	StatutPubliciteActive  = "active"
	StatutPubliciteAnnulee = "annulee"
)

const ReductionPubPremium = 0.20

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
	maintenant := time.Now()
	if dateAvantAujourdhui(dateDebut) {
		return Invalide("La date de début ne peut pas être dans le passé")
	}
	if dateDebut.After(maintenant.Add(FenetreProgrammationMax)) {
		return Invalide("La date de début est trop éloignée dans le futur (2 ans maximum)")
	}
	if !dateFin.IsZero() {
		if dateFin.Before(dateDebut) {
			return Invalide("La date de fin ne peut pas précéder la date de début")
		}
		if dateFin.After(maintenant.Add(FenetreProgrammationMax)) {
			return Invalide("La date de fin est trop éloignée dans le futur (2 ans maximum)")
		}
	}
	return nil
}

func PeutAnnulerPublicite(statut string) error {
	if statut != StatutPubliciteActive {
		return EtatInvalide("Seule une campagne active peut être annulée")
	}
	return nil
}
