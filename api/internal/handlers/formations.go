package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
)

func GetFormations(w http.ResponseWriter, r *http.Request) {
	query := `SELECT Id_Formations, Titre, Description, Prix, Duree, Statut, 
			  COALESCE(DATE_FORMAT(Date_formation, '%Y-%m-%dT%H:%i:%s'),''), COALESCE(Places_total, 0),
			  COALESCE(Places_dispo, 0), COALESCE(Localisation, ''), 
			  COALESCE(Categorie, ''), COALESCE(DATE_FORMAT(Date_fin, '%Y-%m-%d'),'')
			  FROM Formations WHERE Statut_validation = 'valide'`

	rows, err := database.DB.Query(query)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree, pTotal, pDispo int
		var titre, desc, statut, date, loc, cat, dateFin string
		var prix float64
		rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &cat, &dateFin)

		formations = append(formations, map[string]interface{}{
			"id":           id,
			"titre":        titre,
			"description":  desc,
			"prix":         prix,
			"duree":        duree,
			"statut":       statut,
			"date":         date,
			"date_fin":     dateFin,
			"places_total": pTotal,
			"places_dispo": pDispo,
			"localisation": loc,
			"categorie":    cat,
		})
	}
	httpx.JSONOK(w, http.StatusOK, formations)
}

func GetFormation(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/formations/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if len(parts) >= 2 && parts[1] == "inscrire" {
		middleware.JWTAuth(InscrireFormation)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "desinscrire" {
		middleware.JWTAuth(DesinscrireFormation)(w, r)
		return
	}

	middleware.OptionalJWT(ficheFormation)(w, r)
}

func ficheFormation(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/formations/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	viewer := middleware.GetUserID(r)
	if viewer == 0 {
		if uid := r.URL.Query().Get("user_id"); uid != "" {
			viewer, _ = strconv.Atoi(uid)
		}
	}
	dto, err := inscriptionSvc.FicheFormation(viewer, id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func InscrireFormation(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/formations/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID formation manquant")
		return
	}
	if err := inscriptionSvc.InscrireFormation(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Inscription confirmée"})
}

func DesinscrireFormation(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/formations/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID formation manquant")
		return
	}
	if err := inscriptionSvc.DesinscrireFormation(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription effectuée"})
}

func AdminGetFormations(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT f.Id_Formations, COALESCE(f.Titre,''), COALESCE(f.Description,''),
			COALESCE(f.Prix,0), COALESCE(f.Duree,0), COALESCE(f.Statut,'en_attente'),
			COALESCE(DATE_FORMAT(f.Date_formation, '%Y-%m-%dT%H:%i:%s'),''), COALESCE(f.Places_total,0), COALESCE(f.Places_dispo,0),
			COALESCE(f.Localisation,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(DATE_FORMAT(f.Date_fin, '%Y-%m-%d'),'')
		FROM Formations f
		LEFT JOIN Salaries s ON s.Id_Salaries = f.Id_Salaries
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY f.Id_Formations DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree, pTotal, pDispo int
		var titre, desc, statut, date, loc, nom, prenom, dateFin string
		var prix float64
		rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &nom, &prenom, &dateFin)
		formations = append(formations, map[string]interface{}{
			"id": id, "titre": titre, "description": desc, "prix": prix,
			"duree": duree, "statut": statut, "date_formation": date, "date_fin": dateFin,
			"places_total": pTotal, "places_dispo": pDispo, "localisation": loc,
			"nom_salarie": nom, "prenom_salarie": prenom,
		})
	}
	httpx.JSONOK(w, http.StatusOK, formations)
}

func AdminFormationAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/formations/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	id := parts[0]

	if len(parts) >= 2 {
		action := parts[1]
		switch action {
		case "valider":
			database.DB.Exec("UPDATE Formations SET Statut='actif', Statut_validation='valide', Motif_refus=NULL WHERE Id_Formations=?", id)
			notifierSalarieFormation(id, "Votre formation a été validée et publiée.")
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation validée"})
			return
		case "rejeter":
			motif := r.URL.Query().Get("motif")
			database.DB.Exec("UPDATE Formations SET Statut='rejete', Statut_validation='refuse', Motif_refus=NULLIF(?, '') WHERE Id_Formations=?", motif, id)
			msg := "Votre formation a été refusée."
			if motif != "" {
				msg += " Motif : " + motif
			}
			notifierSalarieFormation(id, msg)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation rejetée"})
			return
		}
	}

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Titre         string  `json:"titre"`
			Description   string  `json:"description"`
			Prix          float64 `json:"prix"`
			Duree         int     `json:"duree"`
			Statut        string  `json:"statut"`
			DateFormation string  `json:"date_formation"`
			DateFin       string  `json:"date_fin"`
			PlacesTotal   int     `json:"places_total"`
			Localisation  string  `json:"localisation"`
			IdSalaries    int     `json:"id_salaries"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		if body.PlacesTotal == 0 {
			body.PlacesTotal = 20
		}
		if body.Statut == "" {
			body.Statut = "en_attente"
		}
		result, err := database.DB.Exec(
			`INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Date_formation, Date_fin, Places_total, Places_dispo, Localisation, Id_Salaries)
			VALUES (?,?,?,?,?,?,NULLIF(?, ''),?,?,?,?)`,
			body.Titre, body.Description, body.Prix, body.Duree, body.Statut,
			body.DateFormation, body.DateFin, body.PlacesTotal, body.PlacesTotal, body.Localisation, body.IdSalaries,
		)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodPut:
		var body struct {
			Titre         string  `json:"titre"`
			Description   string  `json:"description"`
			Prix          float64 `json:"prix"`
			Duree         int     `json:"duree"`
			Statut        string  `json:"statut"`
			DateFormation string  `json:"date_formation"`
			DateFin       string  `json:"date_fin"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Formations SET Titre=?, Description=?, Prix=?, Duree=?, Statut=?, Date_formation=IFNULL(NULLIF(?, ''), Date_formation), Date_fin=NULLIF(?, '') WHERE Id_Formations=?",
			body.Titre, body.Description, body.Prix, body.Duree, body.Statut, body.DateFormation, body.DateFin, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation mise à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Formation_Etapes WHERE Id_Formations=?", id)
		database.DB.Exec("DELETE FROM Formations WHERE Id_Formations=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
