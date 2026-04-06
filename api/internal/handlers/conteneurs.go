package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Conteneur struct {
		ID           int    `json:"id"`
		Localisation string `json:"localisation"`
		Capacite     string `json:"capacite"`
		Statut       string `json:"statut"`
	}

	conteneurs := []Conteneur{}
	for rows.Next() {
		var c Conteneur
		rows.Scan(&c.ID, &c.Localisation, &c.Capacite, &c.Statut)
		conteneurs = append(conteneurs, c)
	}
	httpx.JSONOK(w, http.StatusOK, conteneurs)
}

func AdminConteneurAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/conteneurs/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodGet:
		row := database.DB.QueryRow(
			"SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs WHERE Id_Conteneurs=?", id,
		)
		var c struct {
			ID           int    `json:"id"`
			Localisation string `json:"localisation"`
			Capacite     string `json:"capacite"`
			Statut       string `json:"statut"`
		}
		if err := row.Scan(&c.ID, &c.Localisation, &c.Capacite, &c.Statut); err != nil {
			httpx.JSONError(w, http.StatusNotFound, "Conteneur introuvable")
			return
		}
		httpx.JSONOK(w, http.StatusOK, c)

	case http.MethodPost:
		var body struct {
			Localisation      string `json:"localisation"`
			Capacite          string `json:"capacite"`
			Statut            string `json:"statut"`
			IdAdministrateurs int    `json:"id_administrateurs"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Conteneurs (Localisation, Capacite, Statut, Id_Administrateurs) VALUES (?,?,?,?)",
			body.Localisation, body.Capacite, body.Statut, body.IdAdministrateurs,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodPut:
		var body struct {
			Localisation string `json:"localisation"`
			Capacite     string `json:"capacite"`
			Statut       string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Conteneurs SET Localisation=?, Capacite=?, Statut=? WHERE Id_Conteneurs=?",
			body.Localisation, body.Capacite, body.Statut, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
