package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetServices(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Services, Titre, Description, Prix, Duree, Categorie FROM Services")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var services []map[string]interface{}
	for rows.Next() {
		var id, duree int
		var titre, description, categorie string
		var prix float64
		if err := rows.Scan(&id, &titre, &description, &prix, &duree, &categorie); err != nil {
			continue
		}
		services = append(services, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"prix": prix, "duree": duree, "categorie": categorie,
		})
	}
	if services == nil {
		services = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, services)
}

func GetService(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow(
		"SELECT Id_Services, Titre, Description, Prix, Duree, Categorie FROM Services WHERE Id_Services = ?", id,
	)

	var sid, duree int
	var titre, description, categorie string
	var prix float64

	if err := row.Scan(&sid, &titre, &description, &prix, &duree, &categorie); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Service introuvable")
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": sid, "titre": titre, "description": description,
		"prix": prix, "duree": duree, "categorie": categorie,
	})
}

func AdminGetServices(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Services, Titre, Description, Prix, Duree, Categorie FROM Services ORDER BY Id_Services DESC")
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	services := []map[string]interface{}{}
	for rows.Next() {
		var id, duree int
		var titre, description, categorie string
		var prix float64
		rows.Scan(&id, &titre, &description, &prix, &duree, &categorie)
		services = append(services, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"prix": prix, "duree": duree, "categorie": categorie,
		})
	}
	httpx.JSONOK(w, http.StatusOK, services)
}

func AdminCreateService(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Prix        float64 `json:"prix"`
		Duree       int     `json:"duree"`
		Categorie   string  `json:"categorie"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	result, err := database.DB.Exec(
		"INSERT INTO Services (Titre, Description, Prix, Duree, Categorie) VALUES (?,?,?,?,?)",
		body.Titre, body.Description, body.Prix, body.Duree, body.Categorie,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Service créé"})
}

func AdminServiceAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Titre       string  `json:"titre"`
			Description string  `json:"description"`
			Prix        float64 `json:"prix"`
			Duree       int     `json:"duree"`
			Categorie   string  `json:"categorie"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Services SET Titre=?, Description=?, Prix=?, Duree=?, Categorie=? WHERE Id_Services=?",
			body.Titre, body.Description, body.Prix, body.Duree, body.Categorie, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Service mis à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Services WHERE Id_Services=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Service supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}