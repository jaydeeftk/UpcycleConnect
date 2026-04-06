package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetFormations(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT f.Id_Formations, f.Titre, f.Description, f.Prix, f.Duree, f.Statut,
			u.Nom, u.Prenom
		FROM Formations f
		JOIN Salaries s ON s.Id_Salaries = f.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Formation struct {
		ID          int     `json:"id"`
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Prix        float64 `json:"prix"`
		Duree       int     `json:"duree"`
		Statut      string  `json:"statut"`
		NomSalarie  string  `json:"nom_salarie"`
		PrenomSal   string  `json:"prenom_salarie"`
	}

	formations := []Formation{}
	for rows.Next() {
		var f Formation
		rows.Scan(&f.ID, &f.Titre, &f.Description, &f.Prix, &f.Duree, &f.Statut, &f.NomSalarie, &f.PrenomSal)
		formations = append(formations, f)
	}
	httpx.JSONOK(w, http.StatusOK, formations)
}

func AdminFormationAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/formations/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Titre       string  `json:"titre"`
			Description string  `json:"description"`
			Prix        float64 `json:"prix"`
			Duree       int     `json:"duree"`
			Statut      string  `json:"statut"`
			IdSalaries  int     `json:"id_salaries"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Id_Salaries) VALUES (?,?,?,?,?,?)",
			body.Titre, body.Description, body.Prix, body.Duree, body.Statut, body.IdSalaries,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodPut:
		var body struct {
			Titre       string  `json:"titre"`
			Description string  `json:"description"`
			Prix        float64 `json:"prix"`
			Duree       int     `json:"duree"`
			Statut      string  `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Formations SET Titre=?, Description=?, Prix=?, Duree=?, Statut=? WHERE Id_Formations=?",
			body.Titre, body.Description, body.Prix, body.Duree, body.Statut, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation mise à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Formations WHERE Id_Formations=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
