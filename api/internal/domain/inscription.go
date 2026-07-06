package domain

import "time"

const (
	StatutEvtAVenir = "a_venir"
)

const (
	StatutFormActif = "actif"
)

type EvenementSnapshot struct {
	Statut       string
	Date         time.Time
	Capacite     int
	Participants int
	Prix         float64
}

func (e EvenementSnapshot) PeutParticiper(now time.Time) error {
	if e.Statut != StatutEvtAVenir {
		return EtatInvalide("Les inscriptions sont fermées pour cet événement")
	}
	if !e.Date.After(now) {
		return EtatInvalide("Cet événement est déjà passé")
	}
	if e.Participants >= e.Capacite {
		return Complet("Cet événement est complet")
	}
	return nil
}

func (e EvenementSnapshot) PeutDesinscrire(now time.Time) error {
	if !e.Date.After(now) {
		return EtatInvalide("Impossible de se désinscrire d'un événement passé")
	}
	return nil
}

func (e EvenementSnapshot) ActionsParticulier(now time.Time, estParticulier, dejaInscrit, aPaye bool) []string {
	actions := []string{}
	if !estParticulier {
		return actions
	}
	if dejaInscrit {
		if e.PeutDesinscrire(now) == nil {
			actions = append(actions, "desinscrire")
		}
		return actions
	}
	if e.PeutParticiper(now) == nil {
		if e.Prix > 0 && !aPaye {
			actions = append(actions, "payer")
		} else {
			actions = append(actions, "participer")
		}
	}
	return actions
}

type FormationSnapshot struct {
	Statut      string
	Date        time.Time
	PlacesDispo int
	PlacesTotal int
	Prix        float64
}

func (f FormationSnapshot) PeutInscrire(now time.Time) error {
	if f.Statut != StatutFormActif {
		return EtatInvalide("Les inscriptions sont fermées pour cette formation")
	}
	if !f.Date.After(now) {
		return EtatInvalide("Cette formation est déjà passée")
	}
	if f.PlacesDispo <= 0 {
		return Complet("Cette formation est complète")
	}
	return nil
}

func (f FormationSnapshot) PeutDesinscrire(now time.Time) error {
	if !f.Date.After(now) {
		return EtatInvalide("Impossible de se désinscrire d'une formation passée")
	}
	return nil
}

func (f FormationSnapshot) ActionsParticulier(now time.Time, estParticulier, dejaInscrit, aPaye bool) []string {
	actions := []string{}
	if !estParticulier {
		return actions
	}
	if dejaInscrit {
		if f.PeutDesinscrire(now) == nil {
			actions = append(actions, "desinscrire")
		}
		return actions
	}
	if f.PeutInscrire(now) == nil {
		if f.Prix > 0 && !aPaye {
			actions = append(actions, "payer")
		} else {
			actions = append(actions, "inscrire")
		}
	}
	return actions
}
