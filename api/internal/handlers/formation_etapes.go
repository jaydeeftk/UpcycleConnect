package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

// formationAppartientAuSalarie verifie que la formation appartient bien au
// salarie connecte avant de laisser gerer ses etapes.
func formationAppartientAuSalarie(idFormation, salarieID int) bool {
	var n int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Formations WHERE Id_Formations=? AND Id_Salaries=?",
		idFormation, salarieID,
	).Scan(&n)
	return n > 0
}

// SalarieFormationEtapesHandler gere la liste et la creation des etapes
// d'une formation. Route : /api/salaries/formations/{id}/etapes
func SalarieFormationEtapesHandler(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	parts := segmentsApres(r.URL.Path, "/api/salaries/formations/")
	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant de formation manquant")
		return
	}
	idFormation, err := idDepuisChemin(r.URL.Path, "/api/salaries/formations/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if !formationAppartientAuSalarie(idFormation, salarieID) {
		httpx.JSONError(w, http.StatusForbidden, "Formation introuvable")
		return
	}

	switch r.Method {
	case http.MethodGet:
		rows, err := database.DB.Query(
			`SELECT Id_Etapes, Titre, COALESCE(Description,''), Ordre
			FROM Formation_Etapes WHERE Id_Formations=? ORDER BY Ordre, Id_Etapes`, idFormation,
		)
		if err != nil {
			httpx.JSONOK(w, http.StatusOK, []interface{}{})
			return
		}
		defer rows.Close()
		etapes := []map[string]interface{}{}
		for rows.Next() {
			var id, ordre int
			var titre, description string
			rows.Scan(&id, &titre, &description, &ordre)
			etapes = append(etapes, map[string]interface{}{
				"id": id, "titre": titre, "description": description, "ordre": ordre,
			})
		}
		httpx.JSONOK(w, http.StatusOK, etapes)

	case http.MethodPost:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			Ordre       int    `json:"ordre"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil || strings.TrimSpace(body.Titre) == "" {
			httpx.JSONError(w, http.StatusBadRequest, "Le titre de l'étape est obligatoire")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Formation_Etapes (Id_Formations, Titre, Description, Ordre) VALUES (?,?,?,?)",
			idFormation, body.Titre, body.Description, body.Ordre,
		)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		id, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Étape ajoutée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

// SalarieFormationEtapeAction gere la modification et la suppression d'une
// etape. Route : /api/salaries/formations/{id}/etapes/{etapeId}
func SalarieFormationEtapeAction(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	idFormation, err := idDepuisChemin(r.URL.Path, "/api/salaries/formations/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if !formationAppartientAuSalarie(idFormation, salarieID) {
		httpx.JSONError(w, http.StatusForbidden, "Formation introuvable")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	idEtape := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			Ordre       int    `json:"ordre"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil || strings.TrimSpace(body.Titre) == "" {
			httpx.JSONError(w, http.StatusBadRequest, "Le titre de l'étape est obligatoire")
			return
		}
		database.DB.Exec(
			"UPDATE Formation_Etapes SET Titre=?, Description=?, Ordre=? WHERE Id_Etapes=? AND Id_Formations=?",
			body.Titre, body.Description, body.Ordre, idEtape, idFormation,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Étape mise à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Formation_Etapes WHERE Id_Etapes=? AND Id_Formations=?", idEtape, idFormation)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Étape supprimée"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
