package handlers

import (
	"net/http"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func PublicStats(w http.ResponseWriter, r *http.Request) {
	var utilisateurs, annonces, evenements int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&utilisateurs)
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces").Scan(&annonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&evenements)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"total_utilisateurs": utilisateurs,
		"total_annonces":     annonces,
		"total_evenements":   evenements,
	})
}
