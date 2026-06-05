package domain

import "time"

// Vocabulaires de statut — source de vérité unique, dupliquée en dernière ligne
// de défense par les CHECK de database/migrations/003_statuts_canoniques.sql.
const (
	StatutEvtBrouillon = "brouillon"
	StatutEvtAVenir    = "a_venir"
	StatutEvtEnCours   = "en_cours"
	StatutEvtTermine   = "termine"
	StatutEvtAnnule    = "annule"
)

const (
	StatutFormEnAttente = "en_attente"
	StatutFormActif     = "actif"
	StatutFormRejete    = "rejete"
	StatutFormCloturee  = "cloturee"
)

// EvenementSnapshot — état serveur d'un événement au moment précis d'une
// transition. Tous les champs viennent de la base (sous verrou pour les écritures).
// L'occupation (Participants) est DÉRIVÉE : c'est le COUNT des inscrits, opposé
// à Capacite. Aucune logique métier ne lit le corps de la requête.
type EvenementSnapshot struct {
	Statut       string
	Date         time.Time
	Capacite     int
	Participants int
	Prix         float64
}

// PeutParticiper applique la règle d'inscription à un événement :
// inscriptible ssi statut 'a_venir', date strictement future, capacité libre.
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

// PeutDesinscrire : on ne modifie pas sa participation à un événement passé
// (l'historique de présence devient immuable une fois la date atteinte).
func (e EvenementSnapshot) PeutDesinscrire(now time.Time) error {
	if !e.Date.After(now) {
		return EtatInvalide("Impossible de se désinscrire d'un événement passé")
	}
	return nil
}

// ActionsParticulier renvoie les actions d'inscription autorisées POUR CE
// requérant, dérivées de l'état serveur — base de la règle d'or « le front
// n'affiche que ce que le serveur autorise ». Liste vide pour un anonyme.
// aPaye : l'utilisateur a-t-il déjà réglé cet événement ? Pour un événement
// payant non réglé, l'action exposée est « payer » (et NON « participer »),
// strict reflet du 402 que renverrait le serveur.
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

// FormationSnapshot — état serveur d'une formation. Ici la capacité est portée
// par un COMPTEUR (Places_dispo / Places_total) et non un COUNT : la décrémentation
// doit donc être atomique sous verrou (cf. service), faute de quoi on sur-réserve.
type FormationSnapshot struct {
	Statut      string
	Date        time.Time
	PlacesDispo int
	PlacesTotal int
	Prix        float64
}

// PeutInscrire : inscriptible ssi statut 'actif', date future, places restantes.
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

// PeutDesinscrire : symétrique de l'événement.
func (f FormationSnapshot) PeutDesinscrire(now time.Time) error {
	if !f.Date.After(now) {
		return EtatInvalide("Impossible de se désinscrire d'une formation passée")
	}
	return nil
}

// aPaye : cf. EvenementSnapshot.ActionsParticulier — une formation payante non
// réglée expose « payer », pas « inscrire ».
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
