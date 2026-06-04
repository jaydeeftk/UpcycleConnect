package main

import (
	_ "embed"
	"html/template"
	"net/http"
)

//go:embed templates/dashboard.html
var dashboardHTML string

// dashboardTpl est compilé une seule fois au démarrage. html/template
// échappe automatiquement toutes les valeurs injectées (anti-XSS).
var dashboardTpl = template.Must(template.New("dashboard").Parse(dashboardHTML))

type dashboardData struct {
	Identity         string
	TotalActifs      int
	TotalAnonymes    int
	DemandesOuvertes int
	Adherents        []Adherent
}

// handleDashboard rend le tableau de bord du DPO : compteurs agrégés et
// liste des 100 adhérents les plus récents. Aucune donnée n'est exposée
// hors du service ; l'accès n'est possible qu'à travers le bastion.
func handleDashboard(w http.ResponseWriter, r *http.Request) {
	d := dashboardData{Identity: identityOf(r)}
	_ = db.QueryRow("SELECT COUNT(*) FROM Adherents WHERE Statut='actif'").Scan(&d.TotalActifs)
	_ = db.QueryRow("SELECT COUNT(*) FROM Adherents WHERE Statut='anonymise'").Scan(&d.TotalAnonymes)
	_ = db.QueryRow("SELECT COUNT(*) FROM Demandes_Droits WHERE Statut IN ('recue','en_cours')").Scan(&d.DemandesOuvertes)

	if rows, err := db.Query(adherentSelect + " ORDER BY Id_Adherent DESC LIMIT 100"); err == nil {
		defer rows.Close()
		for rows.Next() {
			if a, e := scanAdherent(rows); e == nil {
				d.Adherents = append(d.Adherents, a)
			}
		}
	}

	audit(r, "consultation_tableau_de_bord", "dashboard", "", "")
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	if err := dashboardTpl.Execute(w, d); err != nil {
		jsonError(w, http.StatusInternalServerError, "rendu impossible")
	}
}
