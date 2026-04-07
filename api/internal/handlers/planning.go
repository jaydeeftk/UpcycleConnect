package handlers

import (
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetPlanning(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	rows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, e.Date_, e.Lieu, e.Statut
		FROM Evenements e
		JOIN Participer_evenements pe ON pe.Id_Evenements = e.Id_Evenements
		JOIN Particuliers p ON p.Id_Particuliers = pe.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY e.Date_ ASC`, idUtilisateur,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"evenements": []interface{}{}, "stats": map[string]int{}})
		return
	}
	defer rows.Close()

	var evenements []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, lieu, statut string
		var date *string
		rows.Scan(&id, &titre, &date, &lieu, &statut)
		evenements = append(evenements, map[string]interface{}{
			"id": id, "titre": titre, "date": date, "lieu": lieu, "statut": statut,
		})
	}
	if evenements == nil {
		evenements = []map[string]interface{}{}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"evenements": evenements,
		"stats":      map[string]int{"total": len(evenements)},
	})
}
