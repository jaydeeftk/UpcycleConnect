package domain

import "strconv"

const ScoreMax = 1000

type ActiviteParticulier struct {
	Annonces            int
	Evenements          int
	Sujets              int
	Depots              int
	Formations          int
	DonsReserves        int
	PrestationsAchetees int
	DevisAcceptes       int
}

type LigneHistorique struct {
	Action  string
	Points  string
	Detail  string
	Icone   string
	Couleur string
}

func CalculerScore(a ActiviteParticulier) (int, []LigneHistorique) {
	regles := []struct {
		n       int
		poids   int
		action  string
		unite   string
		icone   string
		couleur string
	}{
		{a.Annonces, 30, "Annonces validées", "annonce(s)", "fa-bullhorn", "text-green-500"},
		{a.Evenements, 20, "Participations à des événements", "événement(s)", "fa-calendar-alt", "text-purple-500"},
		{a.Sujets, 10, "Sujets créés dans le forum", "sujet(s)", "fa-comments", "text-orange-500"},
		{a.Depots, 50, "Dépôts en conteneur validés", "dépôt(s)", "fa-recycle", "text-teal-500"},
		{a.Formations, 15, "Formations réservées", "formation(s)", "fa-graduation-cap", "text-blue-500"},
		{a.DonsReserves, 15, "Dons récupérés", "don(s)", "fa-hand-holding-heart", "text-pink-500"},
		{a.PrestationsAchetees, 20, "Prestations catalogue achetées", "prestation(s)", "fa-store", "text-indigo-500"},
		{a.DevisAcceptes, 25, "Demandes sur mesure abouties", "demande(s)", "fa-file-signature", "text-amber-500"},
	}
	total := 0
	hist := []LigneHistorique{}
	for _, r := range regles {
		if r.n <= 0 {
			continue
		}
		pts := r.n * r.poids
		total += pts
		hist = append(hist, LigneHistorique{
			Action:  r.action,
			Points:  "+" + strconv.Itoa(pts),
			Detail:  strconv.Itoa(r.n) + " " + r.unite + " × " + strconv.Itoa(r.poids) + " pts",
			Icone:   r.icone,
			Couleur: r.couleur,
		})
	}
	return total, hist
}

type PalierBadge struct {
	Min     int
	Max     int
	Icone   string
	Label   string
	Couleur string
	Bg      string
}

type BadgeEtat struct {
	PalierBadge
	Debloque bool
}

var paliersBadge = []PalierBadge{
	{0, 100, "🌱", "Éco-Débutant", "text-green-500", "bg-green-50"},
	{100, 300, "♻️", "Recycleur Actif", "text-blue-500", "bg-blue-50"},
	{300, 600, "🌍", "Éco-Engagé", "text-purple-500", "bg-purple-50"},
	{600, ScoreMax, "🏆", "Phénix Vert", "text-yellow-500", "bg-yellow-50"},
}

func BadgesPour(score int) (PalierBadge, *PalierBadge, []BadgeEtat) {
	tous := make([]BadgeEtat, len(paliersBadge))
	actuel := 0
	for i, p := range paliersBadge {
		deb := score >= p.Min
		tous[i] = BadgeEtat{PalierBadge: p, Debloque: deb}
		if deb {
			actuel = i
		}
	}
	var suivant *PalierBadge
	if actuel+1 < len(paliersBadge) {
		s := paliersBadge[actuel+1]
		suivant = &s
	}
	return paliersBadge[actuel], suivant, tous
}

func TauxCommissionPourScore(score int) float64 {
	switch {
	case score >= 600:
		return 0.06
	case score >= 300:
		return 0.08
	case score >= 100:
		return 0.09
	default:
		return 0.10
	}
}

func ReductionFormationEvenementPourScore(score int) float64 {
	switch {
	case score >= 600:
		return 0.10
	case score >= 300:
		return 0.07
	case score >= 100:
		return 0.03
	default:
		return 0
	}
}
