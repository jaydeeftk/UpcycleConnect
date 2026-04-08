package handlers

import (
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetScore(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	var idParticulier int
	if err := database.DB.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur,
	).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
			"score":      0,
			"historique": []interface{}{},
		})
		return
	}

	var nbAnnonces int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Annonces WHERE Id_Particuliers = ? AND Statut = 'validee'",
		idParticulier,
	).Scan(&nbAnnonces)

	var nbEvenements int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Participer_evenements WHERE Id_Particuliers = ?",
		idParticulier,
	).Scan(&nbEvenements)

	var nbSujets int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Sujets WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&nbSujets)

	score := (nbAnnonces * 30) + (nbEvenements * 20) + (nbSujets * 10)

	database.DB.Exec(
		"UPDATE Particuliers SET Score = ? WHERE Id_Particuliers = ?",
		score, idParticulier,
	)

	var historique []map[string]interface{}

	if nbAnnonces > 0 {
		historique = append(historique, map[string]interface{}{
			"action": "Annonces validées",
			"points": "+" + strconv.Itoa(nbAnnonces*30),
			"detail": strconv.Itoa(nbAnnonces) + " annonce(s) × 30 pts",
			"icon":   "fa-bullhorn",
			"color":  "text-green-500",
		})
	}

	if nbEvenements > 0 {
		historique = append(historique, map[string]interface{}{
			"action": "Participations à des événements",
			"points": "+" + strconv.Itoa(nbEvenements*20),
			"detail": strconv.Itoa(nbEvenements) + " événement(s) × 20 pts",
			"icon":   "fa-calendar-alt",
			"color":  "text-purple-500",
		})
	}

	if nbSujets > 0 {
		historique = append(historique, map[string]interface{}{
			"action": "Sujets créés dans le forum",
			"points": "+" + strconv.Itoa(nbSujets*10),
			"detail": strconv.Itoa(nbSujets) + " sujet(s) × 10 pts",
			"icon":   "fa-comments",
			"color":  "text-orange-500",
		})
	}

	if historique == nil {
		historique = []map[string]interface{}{}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"score":      score,
		"historique": historique,
	})
}