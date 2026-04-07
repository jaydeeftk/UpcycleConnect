package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetPaiements(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT Id_Paiements, Montant, Statut, Date_Paiement FROM Paiements WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var paiements []map[string]interface{}

	for rows.Next() {
		var id int
		var montant float64
		var statut bool
		var date string

		if err := rows.Scan(&id, &montant, &statut, &date); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}

		paiements = append(paiements, map[string]interface{}{
			"id":      id,
			"montant": montant,
			"statut":  statut,
			"date":    date,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(paiements)
}
