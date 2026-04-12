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

	var nbDepots int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Demandes_conteneurs WHERE Id_Particuliers = ? AND Statut = 'validee'",
		idParticulier,
	).Scan(&nbDepots)

	var nbFormations int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Reserver_formation WHERE Id_Particuliers = ?",
		idParticulier,
	).Scan(&nbFormations)

	score := (nbAnnonces * 30) + (nbEvenements * 20) + (nbSujets * 10) + (nbDepots * 50) + (nbFormations * 15)

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

	if nbDepots > 0 {
		historique = append(historique, map[string]interface{}{
			"action": "Dépôts en conteneur validés",
			"points": "+" + strconv.Itoa(nbDepots*50),
			"detail": strconv.Itoa(nbDepots) + " dépôt(s) × 50 pts",
			"icon":   "fa-recycle",
			"color":  "text-teal-500",
		})
	}

	if nbFormations > 0 {
		historique = append(historique, map[string]interface{}{
			"action": "Formations réservées",
			"points": "+" + strconv.Itoa(nbFormations*15),
			"detail": strconv.Itoa(nbFormations) + " formation(s) × 15 pts",
			"icon":   "fa-graduation-cap",
			"color":  "text-blue-500",
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
