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
	idParticulier := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Contenu, Date_publication, Statut FROM Annonces WHERE Id_Particuliers = ?",
		idParticulier,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var demandes []map[string]interface{}

	for rows.Next() {
		var id int
		var contenu, statut string
		var date *string

		if err := rows.Scan(&id, &contenu, &date, &statut); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}

		demandes = append(demandes, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"date":    date,
			"statut":  statut,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(demandes)
}

func CreateDemande(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Contenu        string `json:"contenu"`
		IdParticuliers int    `json:"id_particuliers"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO Annonces (Contenu, Date_publication, Statut, Id_Particuliers) VALUES (?, NOW(), 'active', ?)",
		body.Contenu, body.IdParticuliers,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]string{
		"message": "Demande créée avec succès",
	})
}
