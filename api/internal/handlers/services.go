package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

var serviceCatalogueSvc = services.NewServiceCatalogueService()

func GetServices(w http.ResponseWriter, r *http.Request) {
	liste, err := serviceCatalogueSvc.ListerCatalogue()
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func GetService(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	row := database.DB.QueryRow(
		`SELECT s.Id_Services, COALESCE(s.Titre,''), COALESCE(s.Description,''), COALESCE(s.Prix,0),
			COALESCE(s.Duree,0), COALESCE(s.Categorie,''), COALESCE(s.Id_Professionnels,0),
			TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,''))),
			CASE WHEN s.Id_Professionnels IS NOT NULL THEN 'pro' ELSE 'salarie' END
		 FROM Services s
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = s.Id_Professionnels
		 LEFT JOIN Salaries sa ON sa.Id_Salaries = s.Id_Salaries
		 LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = COALESCE(pa.Id_Utilisateurs, sa.Id_Utilisateurs)
		 WHERE s.Id_Services = ?`, id,
	)

	var sid, duree, idPro int
	var titre, description, categorie, nomAuteur, typeAuteur string
	var prix float64

	if err := row.Scan(&sid, &titre, &description, &prix, &duree, &categorie, &idPro, &nomAuteur, &typeAuteur); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Service introuvable")
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": sid, "titre": titre, "description": description,
		"prix": prix, "duree": duree, "categorie": categorie,
		"id_professionnel": idPro, "nom_auteur": nomAuteur, "type_auteur": typeAuteur,
	})
}

func AdminGetServices(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Services, Titre, Description, Prix, Duree, Categorie FROM Services ORDER BY Id_Services DESC")
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	svcs := []map[string]interface{}{}
	for rows.Next() {
		var id, duree int
		var titre, description, categorie string
		var prix float64
		rows.Scan(&id, &titre, &description, &prix, &duree, &categorie)
		svcs = append(svcs, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"prix": prix, "duree": duree, "categorie": categorie,
		})
	}
	httpx.JSONOK(w, http.StatusOK, svcs)
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
		httpx.JSONServerError(w, err)
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
