package domain

import "strconv"

// Gamification du PARTICULIER : score écologique dérivé de l'activité, et badges
// dérivés du score. Tout est PUR et sans I/O — donc testable et unique source de
// vérité. Avant ce vertical, les poids vivaient dans un fat handler (qui écrivait
// un cache dans un GET) et les PALIERS DE BADGES vivaient... dans la vue PHP
// (règle métier côté front). Ici les deux remontent au serveur.

// ScoreMax borne la jauge de progression (cohérent avec le palier terminal).
const ScoreMax = 1000

// ActiviteParticulier = volumes d'activité comptés, entrées du calcul de score.
type ActiviteParticulier struct {
	Annonces   int // annonces validées
	Evenements int // participations à des événements
	Sujets     int // sujets créés au forum
	Depots     int // dépôts en conteneur validés
	Formations int // formations réservées
}

// regleScore : une source de points. Le barème est DONNÉE (pas du code branché) :
// ajouter une façon de gagner des points = ajouter une ligne (Open/Closed).
type regleScore struct {
	compte  func(ActiviteParticulier) int
	poids   int
	action  string
	unite   string // libellé pluralisé : "annonce(s)", "événement(s)"...
	icone   string
	couleur string
}

// Barème (repris à l'identique de l'ancien handler, désormais testable).
var reglesScore = []regleScore{
	{func(a ActiviteParticulier) int { return a.Annonces }, 30, "Annonces validées", "annonce(s)", "fa-bullhorn", "text-green-500"},
	{func(a ActiviteParticulier) int { return a.Evenements }, 20, "Participations à des événements", "événement(s)", "fa-calendar-alt", "text-purple-500"},
	{func(a ActiviteParticulier) int { return a.Sujets }, 10, "Sujets créés dans le forum", "sujet(s)", "fa-comments", "text-orange-500"},
	{func(a ActiviteParticulier) int { return a.Depots }, 50, "Dépôts en conteneur validés", "dépôt(s)", "fa-recycle", "text-teal-500"},
	{func(a ActiviteParticulier) int { return a.Formations }, 15, "Formations réservées", "formation(s)", "fa-graduation-cap", "text-blue-500"},
}

// LigneHistorique : une entrée de l'historique des points, telle que l'affiche le
// front (champs action/points/detail/icône/couleur préservés à l'identique).
type LigneHistorique struct {
	Action  string
	Points  string // "+90"
	Detail  string // "3 annonce(s) × 30 pts"
	Icone   string
	Couleur string
}

// CalculerScore additionne les points et construit l'historique. Une règle dont
// le compte est nul n'apparaît PAS dans l'historique (comportement d'origine).
func CalculerScore(a ActiviteParticulier) (int, []LigneHistorique) {
	total := 0
	hist := []LigneHistorique{}
	for _, r := range reglesScore {
		n := r.compte(a)
		if n <= 0 {
			continue
		}
		pts := n * r.poids
		total += pts
		hist = append(hist, LigneHistorique{
			Action:  r.action,
			Points:  "+" + strconv.Itoa(pts),
			Detail:  strconv.Itoa(n) + " " + r.unite + " × " + strconv.Itoa(r.poids) + " pts",
			Icone:   r.icone,
			Couleur: r.couleur,
		})
	}
	return total, hist
}

// PalierBadge : un palier de la progression. Min inclus ; Max sert d'affichage de
// borne supérieure du palier. Un badge est débloqué dès que score >= Min.
type PalierBadge struct {
	Min     int
	Max     int
	Icone   string
	Label   string
	Couleur string
	Bg      string
}

// Échelle de badges, reprise VERBATIM de la vue front (aucune règle inventée) —
// désormais servie par l'API pour que le front cesse de la recalculer en PHP.
var paliersBadge = []PalierBadge{
	{0, 100, "🌱", "Éco-Débutant", "text-green-500", "bg-green-50"},
	{100, 300, "♻️", "Recycleur Actif", "text-blue-500", "bg-blue-50"},
	{300, 600, "🌍", "Éco-Engagé", "text-purple-500", "bg-purple-50"},
	{600, ScoreMax, "🏆", "Phénix Vert", "text-yellow-500", "bg-yellow-50"},
}

// BadgeEtat : un palier + s'il est débloqué pour un score donné.
type BadgeEtat struct {
	PalierBadge
	Debloque bool
}

// BadgesPour dérive, d'un score, le badge ACTUEL (le plus haut palier débloqué),
// le badge SUIVANT (nil si palier terminal) et l'état de TOUS les paliers.
//
// « Le plus haut palier débloqué » corrige un bug de la vue front, qui retombait
// sur le premier badge pour un score == ScoreMax (sa condition score < Max
// excluait la borne) : ici un score de 1000 reste « Phénix Vert ».
func BadgesPour(score int) (actuel PalierBadge, suivant *PalierBadge, tous []BadgeEtat) {
	tous = make([]BadgeEtat, len(paliersBadge))
	actuelIdx := 0
	for i, p := range paliersBadge {
		deb := score >= p.Min
		tous[i] = BadgeEtat{PalierBadge: p, Debloque: deb}
		if deb {
			actuelIdx = i
		}
	}
	actuel = paliersBadge[actuelIdx]
	if actuelIdx+1 < len(paliersBadge) {
		s := paliersBadge[actuelIdx+1]
		suivant = &s
	}
	return actuel, suivant, tous
}
