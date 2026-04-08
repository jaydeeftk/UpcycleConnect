package handlers

import (
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