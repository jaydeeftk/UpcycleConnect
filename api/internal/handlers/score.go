package handlers

import (
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetScore(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"score": 0, "historique": []interface{}{}})
		return
	}

	var score int
	database.DB.QueryRow("SELECT COALESCE(Score, 0) FROM Particuliers WHERE Id_Particuliers = ?", idParticulier).Scan(&score)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"score":      score,
		"historique": []interface{}{},
	})
}
