package handlers

import (
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetFactures(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT f.Id_Facture, f.Numero_facture, f.Date_emission, f.Montant_TTC, f.Statut, f.Type,
			u.Nom, u.Prenom
		FROM Factures f
		JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		ORDER BY f.Date_emission DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Facture struct {
		ID      int     `json:"id"`
		Numero  string  `json:"numero"`
		Date    string  `json:"date_emission"`
		Montant float64 `json:"montant_ttc"`
		Statut  string  `json:"statut"`
		Type    string  `json:"type"`
		Nom     string  `json:"nom"`
		Prenom  string  `json:"prenom"`
	}

	factures := []Facture{}
	for rows.Next() {
		var f Facture
		rows.Scan(&f.ID, &f.Numero, &f.Date, &f.Montant, &f.Statut, &f.Type, &f.Nom, &f.Prenom)
		factures = append(factures, f)
	}
	httpx.JSONOK(w, http.StatusOK, factures)
}

func AdminGetFacture(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/factures/")
	row := database.DB.QueryRow(
		`SELECT f.Id_Facture, f.Numero_facture, f.Date_emission, f.Montant_HT, f.TVA, f.Montant_TTC, f.Statut, f.Type,
			u.Nom, u.Prenom, u.Email
		FROM Factures f
		JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		WHERE f.Id_Facture=?`, id,
	)
	var f struct {
		ID        int     `json:"id"`
		Numero    string  `json:"numero"`
		Date      string  `json:"date_emission"`
		MontantHT float64 `json:"montant_ht"`
		TVA       float64 `json:"tva"`
		Montant   float64 `json:"montant_ttc"`
		Statut    string  `json:"statut"`
		Type      string  `json:"type"`
		Nom       string  `json:"nom"`
		Prenom    string  `json:"prenom"`
		Email     string  `json:"email"`
	}
	if err := row.Scan(&f.ID, &f.Numero, &f.Date, &f.MontantHT, &f.TVA, &f.Montant, &f.Statut, &f.Type, &f.Nom, &f.Prenom, &f.Email); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Facture introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, f)
}
