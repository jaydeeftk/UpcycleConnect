package handlers

import (
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetPlanning(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 3 {
		httpx.JSONError(w, http.StatusBadRequest, "ID manquant")
		return
	}
	userId := parts[2]

	rows, err := database.DB.Query(
		"SELECT Id_Evenements, Titre, Description, Date_evenement, Lieu FROM Evenements ORDER BY Date_evenement ASC LIMIT 20",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	evenements := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var titre, description, date, lieu string
		rows.Scan(&id, &titre, &description, &date, &lieu)
		evenements = append(evenements, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"date":        date,
			"lieu":        lieu,
		})
	}

	var totalEvenements int
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&totalEvenements)

	_ = userId

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"evenements": evenements,
		"stats": map[string]interface{}{
			"total": totalEvenements,
		},
	})
}
