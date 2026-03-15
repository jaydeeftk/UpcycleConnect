package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
)

func GetEvenements(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query("SELECT id_evenement, titre, description, lieu, date_evenement FROM evenements")

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var evenements []map[string]interface{}

	for rows.Next() {

		var id int
		var titre, description, lieu string
		var date string

		if err := rows.Scan(&id, &titre, &description, &lieu, &date); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		evenements = append(evenements, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"lieu":        lieu,
			"date":        date,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(evenements)
}

func GetEvenement(w http.ResponseWriter, r *http.Request) {

	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow("SELECT id_evenement, titre, description, lieu, date_evenement FROM evenements WHERE id_evenement = ?", id)

	var idE int
	var titre, description, lieu, date string

	if err := row.Scan(&idE, &titre, &description, &lieu, &date); err != nil {
		http.Error(w, "Événement non trouvé", http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"id":          idE,
		"titre":       titre,
		"description": description,
		"lieu":        lieu,
		"date":        date,
	})
}
