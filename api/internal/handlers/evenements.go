package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
)

func GetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Evenements, Titre, Description, Lieu, Date_Evenement, Capacite FROM Evenements")

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var evenements []map[string]interface{}

	for rows.Next() {
		var id, capacite int
		var titre, description, lieu string
		var date *string

		if err := rows.Scan(&id, &titre, &description, &lieu, &date, &capacite); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		evenements = append(evenements, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"lieu":        lieu,
			"date":        date,
			"capacite":    capacite,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(evenements)
}

func GetEvenement(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow(
		"SELECT Id_Evenements, Titre, Description, Lieu, Date_Evenement, Capacite FROM Evenements WHERE Id_Evenements = ?", id,
	)

	var idE, capacite int
	var titre, description, lieu string
	var date *string

	if err := row.Scan(&idE, &titre, &description, &lieu, &date, &capacite); err != nil {
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
		"capacite":    capacite,
	})
}
