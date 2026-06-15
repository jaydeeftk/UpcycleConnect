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

	evRows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), e.Lieu, e.Statut, COALESCE(e.Duree, 0)
		FROM Evenements e
		JOIN Participer_evenements pe ON pe.Id_Evenements = e.Id_Evenements
		JOIN Particuliers p ON p.Id_Particuliers = pe.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY e.Date_ ASC`, idUtilisateur,
	)

	var evenements []map[string]interface{}
	if err == nil {
		defer evRows.Close()
		for evRows.Next() {
			var id, duree int
			var titre, lieu, statut string
			var date *string
			evRows.Scan(&id, &titre, &date, &lieu, &statut, &duree)
			evenements = append(evenements, map[string]interface{}{
				"id": id, "titre": titre, "date": date,
				"lieu": lieu, "statut": statut, "duree": duree, "type": "evenement",
			})
		}
	}
	if evenements == nil {
		evenements = []map[string]interface{}{}
	}

	fRows, err := database.DB.Query(
		`SELECT f.Id_Formations, f.Titre, COALESCE(DATE_FORMAT(f.Date_formation, '%Y-%m-%dT%H:%i:%s'),''),
			COALESCE(f.Localisation, ''), f.Statut, COALESCE(f.Duree, 0)
		FROM Formations f
		JOIN Reserver_formation rf ON rf.Id_Formations = f.Id_Formations
		JOIN Particuliers p ON p.Id_Particuliers = rf.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY f.Date_formation ASC`, idUtilisateur,
	)

	var formations []map[string]interface{}
	if err == nil {
		defer fRows.Close()
		for fRows.Next() {
			var id, duree int
			var titre, lieu, statut string
			var date *string
			fRows.Scan(&id, &titre, &date, &lieu, &statut, &duree)
			formations = append(formations, map[string]interface{}{
				"id": id, "titre": titre, "date": date,
				"lieu": lieu, "statut": statut, "duree": duree, "type": "formation",
			})
		}
	}
	if formations == nil {
		formations = []map[string]interface{}{}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"evenements": evenements,
		"formations": formations,
		"stats": map[string]int{
			"evenements": len(evenements),
			"formations": len(formations),
		},
	})
}
