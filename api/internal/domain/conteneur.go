package domain

import (
	"errors"
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
	if _, err := parseDateSouple(v); err != nil {
		return Invalide("Format de date invalide")
	}
	return nil
}

func parseDateSouple(valeur string) (time.Time, error) {
	v := strings.TrimSpace(valeur)
	for _, layout := range []string{"2006-01-02", "2006-01-02T15:04", "2006-01-02T15:04:05", "2006-01-02 15:04:05"} {
		if t, err := time.Parse(layout, v); err == nil {
			return t, nil
		}
	}
	return time.Time{}, errNoDateLayout
}

var errNoDateLayout = errors.New("aucun format de date reconnu")

// FenetreProgrammationMax borne la programmation d'un événement/formation/atelier/dépôt
// à un horizon raisonnable (2 ans) pour éviter les dates aberrantes.
const FenetreProgrammationMax = 2 * 365 * 24 * time.Hour

func dateAvantAujourdhui(t time.Time) bool {
	maintenant := time.Now()
	aujourdhui := time.Date(maintenant.Year(), maintenant.Month(), maintenant.Day(), 0, 0, 0, 0, time.UTC)
	jour := time.Date(t.Year(), t.Month(), t.Day(), 0, 0, 0, 0, time.UTC)
	return jour.Before(aujourdhui)
}

// ValiderDateProgrammation vérifie le format ET que la date n'est ni dans le
// passé, ni trop éloignée dans le futur. À utiliser pour toute date programmée
// (événement, formation, atelier, dépôt d'objet).
func ValiderDateProgrammation(valeur string) error {
	if err := ValiderDate(valeur); err != nil {
		return err
	}
	t, _ := parseDateSouple(valeur)
	maintenant := time.Now()
	if dateAvantAujourdhui(t) {
		return Invalide("La date ne peut pas être dans le passé")
	}
	if t.After(maintenant.Add(FenetreProgrammationMax)) {
		return Invalide("La date est trop éloignée dans le futur (2 ans maximum)")
	}
	return nil
}

func ValiderCreationDepot(typeObjet, destination string, prix float64, dateDepot string) error {
	if strings.TrimSpace(typeObjet) == "" {
		return Invalide("Le type d'objet est obligatoire")
	}
	if err := ValiderDateProgrammation(dateDepot); err != nil {
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
	IdAnnonce    int
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

func TailleObjetRequise(typeObjet string) string {
	t := strings.ToLower(strings.TrimSpace(typeObjet))
	for _, kw := range []string{"meuble", "gros", "encombrant", "electromenager", "matelas", "canape", "table"} {
		if strings.Contains(t, kw) {
			return TailleBoxEncombrant
		}
	}
	return TailleBoxStandard
}

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
