package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetFormations(w http.ResponseWriter, r *http.Request) {
	query := `SELECT Id_Formations, Titre, Description, Prix, Duree, Statut, 
			  COALESCE(Date_formation, ''), COALESCE(Places_total, 0), 
			  COALESCE(Places_dispo, 0), COALESCE(Localisation, ''), 
			  COALESCE(Categorie, '') 
			  FROM Formations WHERE Statut = 'actif'`

	rows, err := database.DB.Query(query)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree, pTotal, pDispo int
		var titre, desc, statut, date, loc, cat string
		var prix float64
		rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &cat)

		formations = append(formations, map[string]interface{}{
			"id":           id,
			"titre":        titre,
			"description":  desc,
			"prix":         prix,
			"duree":        duree,
			"statut":       statut,
			"date":         date,
			"places_total": pTotal,
			"places_dispo": pDispo,
			"localisation": loc,
			"categorie":    cat,
		})
	}
	httpx.JSONOK(w, http.StatusOK, formations)
}

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

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree int
		var titre, desc, statut, nom, prenom string
		var prix float64
		rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &nom, &prenom)
		formations = append(formations, map[string]interface{}{
			"id": id, "titre": titre, "description": desc, "prix": prix,
			"duree": duree, "statut": statut, "nom_salarie": nom, "prenom_salarie": prenom,
		})
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
		json.NewDecoder(r.Body).Decode(&body)
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
