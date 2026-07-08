package domain

import "strings"

const (
	StatutCommandeServiceEnAttente = "en_attente_paiement"
	StatutCommandeServicePayee     = "payee"
)

func ValiderServiceCatalogue(titre string, prix float64) error {
	if strings.TrimSpace(titre) == "" {
		return Invalide("Le titre de la prestation est requis")
	}
	if prix <= 0 {
		return Invalide("Le prix doit être positif")
	}
	return nil
}

func ValiderCommandeService(nomObjet, photoURL string) error {
	if strings.TrimSpace(nomObjet) == "" {
		return Invalide("Merci de préciser l'objet concerné")
	}
	if strings.TrimSpace(photoURL) == "" {
		return Invalide("Une photo de l'objet est obligatoire")
	}
	return nil
}

func PeutModifierServiceCatalogue(idProService, idPro int) error {
	if idProService != idPro {
		return Forbidden("Cette prestation ne vous appartient pas")
	}
	return nil
}
