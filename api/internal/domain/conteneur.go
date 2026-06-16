package domain

import (
	"strings"
	"time"
)

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

func ValiderDate(valeur string) error {
	v := strings.TrimSpace(valeur)
	if v == "" {
		return Invalide("La date est obligatoire")
	}
	for _, layout := range []string{"2006-01-02", "2006-01-02T15:04", "2006-01-02T15:04:05", "2006-01-02 15:04:05"} {
		if _, err := time.Parse(layout, v); err == nil {
			return nil
		}
	}
	return Invalide("Format de date invalide")
}

func ValiderCreationDepot(typeObjet, destination string, prix float64, dateDepot string) error {
	if strings.TrimSpace(typeObjet) == "" {
		return Invalide("Le type d'objet est obligatoire")
	}
	if err := ValiderDate(dateDepot); err != nil {
		return err
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
	Taille     string
}

const (
	TailleBoxStandard   = "standard"
	TailleBoxEncombrant = "encombrant"
)

func (b BoxSnapshot) PeutAccueillir() bool {
	return b.Statut == StatutBoxDisponible && b.Occupation < b.Capacite
}

// TailleObjetRequise associe un Type_objet a la taille minimale d'UpcycleBox
// requise (l'audit cible 'encombrant' / 'standard'). Toute chose contenant les
// mots-cles 'meuble', 'gros', 'electromenager'... va en encombrant ; le reste
// en standard.
func TailleObjetRequise(typeObjet string) string {
	t := strings.ToLower(strings.TrimSpace(typeObjet))
	for _, kw := range []string{"meuble", "gros", "encombrant", "electromenager", "matelas", "canape", "table"} {
		if strings.Contains(t, kw) {
			return TailleBoxEncombrant
		}
	}
	return TailleBoxStandard
}

// ChoisirBox prend l'UpcycleBox disponible le plus adapte a la taille requise.
// On essaie d'abord la taille exacte ; si rien ne convient et que la taille
// requise est 'standard', on accepte un 'encombrant' (un objet standard tient
// dans un grand tiroir, l'inverse non).
func ChoisirBox(boxes []BoxSnapshot, tailleRequise string) (int, bool) {
	for _, b := range boxes {
		if b.PeutAccueillir() && b.Taille == tailleRequise {
			return b.ID, true
		}
	}
	if tailleRequise == TailleBoxStandard {
		for _, b := range boxes {
			if b.PeutAccueillir() && b.Taille == TailleBoxEncombrant {
				return b.ID, true
			}
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
