package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/database"
)

func GetServices(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query("SELECT id_service, titre, description, prix, categorie FROM services")

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var services []map[string]interface{}

	for rows.Next() {

		var id int
		var titre string
		var description string
		var prix float64
		var categorie string

		if err := rows.Scan(&id, &titre, &description, &prix, &categorie); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		services = append(services, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"prix":        prix,
			"categorie":   categorie,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(services)
}
