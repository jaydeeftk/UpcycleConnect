package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConseils(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT c.Id_Conseils, c.Contenu, c.Date_d_ajout,
			u.Nom, u.Prenom, COALESCE(s.Poste, '')
		FROM Conseils c
		JOIN Salaries s ON s.Id_Salaries = c.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY c.Date_d_ajout DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var conseils []map[string]interface{}
	for rows.Next() {
		var id int
		var contenu, nom, prenom, poste string
		var date *string
		if err := rows.Scan(&id, &contenu, &date, &nom, &prenom, &poste); err != nil {
			continue
		}
		conseils = append(conseils, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"date":    date,
			"auteur":  nom + " " + prenom,
			"role":    poste,
		})
	}
	if conseils == nil {
		conseils = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, conseils)
}

func GetConseil(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow(`
		SELECT c.Id_Conseils, c.Contenu, c.Date_d_ajout,
			u.Nom, u.Prenom, COALESCE(s.Poste, '')
		FROM Conseils c
		JOIN Salaries s ON s.Id_Salaries = c.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		WHERE c.Id_Conseils = ?
	`, id)

	var idC int
	var contenu, nom, prenom, poste string
	var date *string

	if err := row.Scan(&idC, &contenu, &date, &nom, &prenom, &poste); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Conseil non trouvé")
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"id":      idC,
		"contenu": contenu,
		"date":    date,
		"auteur":  nom + " " + prenom,
		"role":    poste,
	})
}
