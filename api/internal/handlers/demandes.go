package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetDemandes(w http.ResponseWriter, r *http.Request) {

	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT id_annonce, contenu, date_publication FROM annonces WHERE id_particulier = ?",
		idUtilisateur,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var demandes []map[string]interface{}

	for rows.Next() {

		var id int
		var contenu, date string

		if err := rows.Scan(&id, &contenu, &date); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}

		demandes = append(demandes, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"date":    date,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(demandes)
}

func CreateDemande(w http.ResponseWriter, r *http.Request) {

	var body struct {
		Contenu       string `json:"contenu"`
		IdParticulier int    `json:"id_particulier"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO annonces (contenu, date_publication, id_particulier) VALUES (?, NOW(), ?)",
		body.Contenu, body.IdParticulier,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]string{
		"message": "Demande créée avec succès",
	})
}
