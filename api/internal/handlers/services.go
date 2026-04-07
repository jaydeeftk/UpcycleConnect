package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/database"
)

func GetServices(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Services, Titre, Description, Prix, Duree, Categorie FROM Services")

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var services []map[string]interface{}

	for rows.Next() {
		var id, duree int
		var titre, description, categorie string
		var prix float64

		if err := rows.Scan(&id, &titre, &description, &prix, &duree, &categorie); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		services = append(services, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"prix":        prix,
			"duree":       duree,
			"categorie":   categorie,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(services)
}
