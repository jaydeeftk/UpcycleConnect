package domain

import "strings"

const (
	StatutDevisPropose = "propose"
	StatutDevisAccepte = "accepte"
)

const (
	StatutDemandePrestaOuverte = "ouverte"
)

func ValiderDevis(prix float64, message string) error {
	if prix <= 0 {
		return Invalide("Le prix du devis doit être positif")
	}
	if strings.TrimSpace(message) == "" {
		return Invalide("Un message d'accompagnement est requis")
	}
	return nil
}

func PeutModifierDevis(statut string) error {
	if statut != StatutDevisPropose {
		return EtatInvalide("Ce devis ne peut plus être modifié")
	}
	return nil
}

func PeutRetirerDevis(statut string) error {
	if statut != StatutDevisPropose {
		return EtatInvalide("Ce devis ne peut plus être retiré")
	}
	return nil
}

func PeutAccepterDevis(statutDevis, statutDemande string) error {
	if statutDemande != StatutDemandePrestaOuverte {
		return EtatInvalide("Cette demande n'est plus ouverte")
	}
	if statutDevis != StatutDevisPropose {
		return EtatInvalide("Ce devis n'est plus disponible")
	}
	return nil
}

func PeutAnnulerDemandePresta(statut string) error {
	if statut != StatutDemandePrestaOuverte {
		return EtatInvalide("Seule une demande ouverte peut être annulée")
	}
	return nil
}
