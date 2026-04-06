package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConseils(w http.ResponseWriter, r *http.Request) {
	categorie := r.URL.Query().Get("categorie")

	query := `
		SELECT c.Id_Conseils, c.Titre, c.Contenu, c.Categorie, COALESCE(c.Tags, ''), c.Date_d_ajout,
			u.Nom, u.Prenom, COALESCE(s.Poste, '')
		FROM Conseils c
		JOIN Salaries s ON s.Id_Salaries = c.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
	`
	args := []interface{}{}

	if categorie != "" && categorie != "tous" {
		query += " WHERE c.Categorie = ?"
		args = append(args, categorie)
	}

	query += " ORDER BY c.Date_d_ajout DESC"

	rows, err := database.DB.Query(query, args...)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var conseils []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, contenu, cat, tags, nom, prenom, poste string
		var date *string
		if err := rows.Scan(&id, &titre, &contenu, &cat, &tags, &date, &nom, &prenom, &poste); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		conseils = append(conseils, map[string]interface{}{
			"id":        id,
			"titre":     titre,
			"contenu":   contenu,
			"categorie": cat,
			"tags":      strings.Split(tags, ","),
			"date":      date,
			"auteur":    nom + " " + prenom,
			"role":      poste,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(conseils)
}

func GetConseil(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow(`
		SELECT c.Id_Conseils, c.Titre, c.Contenu, c.Categorie, COALESCE(c.Tags, ''), c.Date_d_ajout,
			u.Nom, u.Prenom, COALESCE(s.Poste, '')
		FROM Conseils c
		JOIN Salaries s ON s.Id_Salaries = c.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		WHERE c.Id_Conseils = ?
	`, id)

	var idC int
	var titre, contenu, cat, tags, nom, prenom, poste string
	var date *string

	if err := row.Scan(&idC, &titre, &contenu, &cat, &tags, &date, &nom, &prenom, &poste); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Conseil non trouvé")
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"id":        idC,
		"titre":     titre,
		"contenu":   contenu,
		"categorie": cat,
		"tags":      strings.Split(tags, ","),
		"date":      date,
		"auteur":    nom + " " + prenom,
		"role":      poste,
	})
}
