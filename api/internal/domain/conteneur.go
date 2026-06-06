package domain

import "strings"

const (
	StatutDemandeEnAttente = "en_attente"
	StatutDemandeValidee   = "validee"
	StatutDemandeRefusee   = "refusee"
	StatutDemandeDeposee   = "deposee"
)

const (
	DestinationDon   = "don"
	DestinationVente = "vente"
)

const (
	StatutObjetEnStock    = "en_stock"
	StatutObjetReservePro = "reserve_pro"
	StatutObjetRecupere   = "recupere"
)

const StatutBoxDisponible = "disponible"

const StatutConteneurDisponible = "disponible"

func ValiderCreationDepot(typeObjet, destination string, prix float64) error {
	if strings.TrimSpace(typeObjet) == "" {
		return Invalide("Le type d'objet est obligatoire")
	}
	switch destination {
	case DestinationDon:
		if prix != 0 {
			return Invalide("Un don ne peut pas avoir de prix de vente")
		}
	case DestinationVente:
		if prix <= 0 {
			return Invalide("Une mise en vente exige un prix strictement positif")
		}
	default:
		return Invalide("Destination invalide (attendu : don ou vente)")
	}
	return nil
}

type DemandeSnapshot struct {
	Statut       string
	Proprietaire int
	IdConteneur  int
	Type         string
}

func (d DemandeSnapshot) PeutValider() error {
	if d.Statut != StatutDemandeEnAttente {
		return EtatInvalide("Seule une demande en attente peut être validée")
	}
	return nil
}

func (d DemandeSnapshot) PeutRefuser() error {
	if d.Statut != StatutDemandeEnAttente {
		return EtatInvalide("Seule une demande en attente peut être refusée")
	}
	return nil
}

func (d DemandeSnapshot) PeutDeposer() error {
	if d.Statut != StatutDemandeValidee {
		return EtatInvalide("Le dépôt n'est possible qu'après validation")
	}
	return nil
}

func ActionsDemandeAdmin(statut string) []string {
	switch statut {
	case StatutDemandeEnAttente:
		return []string{"valider", "refuser"}
	case StatutDemandeValidee:
		return []string{"deposer"}
	default:
		return []string{}
	}
}

type BoxSnapshot struct {
	ID         int
	Capacite   int
	Statut     string
	Occupation int
}

func (b BoxSnapshot) PeutAccueillir() bool {
	return b.Statut == StatutBoxDisponible && b.Occupation < b.Capacite
}

func ChoisirBox(boxes []BoxSnapshot) (int, bool) {
	for _, b := range boxes {
		if b.PeutAccueillir() {
			return b.ID, true
		}
	}
	return 0, false
}

func TauxRemplissage(occupation, capacite int) int {
	if capacite <= 0 {
		return 0
	}
	taux := occupation * 100 / capacite
	if taux > 100 {
		return 100
	}
	if taux < 0 {
		return 0
	}
	return taux
}
