package handlers

import (
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetScore(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 3 {
		httpx.JSONError(w, http.StatusBadRequest, "ID manquant")
		return
	}
	userId := parts[2]

	var score int
	err := database.DB.QueryRow(
		"SELECT Score FROM Particuliers WHERE Id_Utilisateurs = ?", userId,
	).Scan(&score)
	if err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Score introuvable")
		return
	}

	rows, err := database.DB.Query(
		"SELECT a.Titre, a.Date_publication FROM Annonces a JOIN Particuliers p ON a.Id_Particuliers = p.Id_Particuliers WHERE p.Id_Utilisateurs = ? ORDER BY a.Date_publication DESC LIMIT 10",
		userId,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"score": score, "historique": []interface{}{}})
		return
	}
	defer rows.Close()

	historique := []map[string]interface{}{}
	for rows.Next() {
		var titre, date string
		rows.Scan(&titre, &date)
		historique = append(historique, map[string]interface{}{"titre": titre, "date": date})
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"score": score, "historique": historique})
}
