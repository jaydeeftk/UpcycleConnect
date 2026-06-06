package domain

import "strings"

const (
	StatutAnnEnAttente = "en_attente"
	StatutAnnValidee   = "validee"
	StatutAnnRefusee   = "refusee"
	StatutAnnRetiree   = "retiree"
	StatutAnnVendue    = "vendue"
)

const (
	TypeAnnDon   = "don"
	TypeAnnVente = "vente"
)

func ValiderCreationAnnonce(titre, typeAnnonce string, prix float64) error {
	if strings.TrimSpace(titre) == "" {
		return Invalide("Le titre est obligatoire")
	}
	switch typeAnnonce {
	case TypeAnnDon:
		if prix != 0 {
			return Invalide("Un don ne peut pas avoir de prix")
		}
	case TypeAnnVente:
		if prix <= 0 {
			return Invalide("Une vente exige un prix supérieur à 0")
		}
	default:
		return Invalide("Type d'annonce invalide")
	}
	return nil
}

type AnnonceSnapshot struct {
	Statut       string
	Type         string
	Prix         float64
	Proprietaire int
}

func (a AnnonceSnapshot) PeutValider() error {
	if a.Statut != StatutAnnEnAttente {
		return EtatInvalide("Seule une annonce en attente peut être validée")
	}
	return nil
}

func (a AnnonceSnapshot) PeutRefuser() error {
	if a.Statut != StatutAnnEnAttente {
		return EtatInvalide("Seule une annonce en attente peut être refusée")
	}
	return nil
}

func (a AnnonceSnapshot) PeutRetirer() error {
	if a.Statut != StatutAnnEnAttente && a.Statut != StatutAnnValidee {
		return EtatInvalide("Cette annonce n'est plus retirable")
	}
	return nil
}

func (a AnnonceSnapshot) PeutMarquerVendue() error {
	if a.Statut != StatutAnnValidee {
		return EtatInvalide("Seule une annonce publiée peut être marquée vendue")
	}
	return nil
}

func AnnonceVisible(statut string, estProprietaire, estAdmin bool) bool {
	if estProprietaire || estAdmin {
		return true
	}
	return statut == StatutAnnValidee || statut == StatutAnnVendue
}

func (a AnnonceSnapshot) ActionsAnnonce(estProprietaire, estAdmin bool) []string {
	actions := []string{}
	if estAdmin && a.Statut == StatutAnnEnAttente {
		actions = append(actions, "valider", "refuser")
	}
	if estProprietaire {
		if a.PeutRetirer() == nil {
			actions = append(actions, "retirer")
		}
		if a.PeutMarquerVendue() == nil {
			actions = append(actions, "vendre")
		}
	}
	return actions
}
