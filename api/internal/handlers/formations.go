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

func GetFormation(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/formations/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if len(parts) >= 2 && parts[1] == "inscrire" {
		InscrireFormation(w, r)
		return
	}

	if len(parts) >= 2 && parts[1] == "desinscrire" {
		DesinscrireFormation(w, r)
		return
	}

	id := parts[0]
	userId := r.URL.Query().Get("user_id")

	row := database.DB.QueryRow(
		`SELECT Id_Formations, Titre, Description, Prix, Duree, Statut,
			COALESCE(Date_formation, ''), COALESCE(Places_total, 0),
			COALESCE(Places_dispo, 0), COALESCE(Localisation, ''), COALESCE(Categorie, '')
		FROM Formations WHERE Id_Formations = ?`, id,
	)
	var fid, duree, pTotal, pDispo int
	var titre, desc, statut, date, loc, cat string
	var prix float64
	if err := row.Scan(&fid, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &cat); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Formation introuvable")
		return
	}

	estInscrit := false
	if userId != "" {
		var idParticulier int
		if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", userId).Scan(&idParticulier); err == nil {
			var count int
			database.DB.QueryRow("SELECT COUNT(*) FROM Reserver_formation WHERE Id_Particuliers = ? AND Id_Formations = ?", idParticulier, id).Scan(&count)
			estInscrit = count > 0
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": fid, "titre": titre, "description": desc, "prix": prix,
		"duree": duree, "statut": statut, "date": date,
		"places_total": pTotal, "places_dispo": pDispo,
		"localisation": loc, "categorie": cat,
		"est_inscrit": estInscrit,
	})
}

func DesinscrireFormation(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	path := strings.TrimPrefix(r.URL.Path, "/api/formations/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	idFormation := parts[0]

	var body struct {
		IdUtilisateur int `json:"id_utilisateur"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non particulier")
		return
	}

	database.DB.Exec("DELETE FROM Reserver_formation WHERE Id_Particuliers = ? AND Id_Formations = ?", idParticulier, idFormation)
	database.DB.Exec("UPDATE Formations SET Places_dispo = Places_dispo + 1 WHERE Id_Formations = ?", idFormation)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription effectuée"})
}

func InscrireFormation(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	path := strings.TrimPrefix(r.URL.Path, "/api/formations/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	idFormation := parts[0]

	var body struct {
		IdUtilisateur int `json:"id_utilisateur"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	if body.IdUtilisateur == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur requis")
		return
	}

	var idParticulier int
	if err := database.DB.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdUtilisateur,
	).Scan(&idParticulier); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non particulier")
		return
	}

	var count int
	database.DB.QueryRow(
		"SELECT COUNT(*) FROM Reserver_formation WHERE Id_Particuliers = ? AND Id_Formations = ?",
		idParticulier, idFormation,
	).Scan(&count)
	if count > 0 {
		httpx.JSONError(w, http.StatusConflict, "Vous êtes déjà inscrit à cette formation")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO Reserver_formation (Id_Particuliers, Id_Formations, Date_reservation) VALUES (?, ?, NOW())",
		idParticulier, idFormation,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	database.DB.Exec(
		"UPDATE Formations SET Places_dispo = Places_dispo - 1 WHERE Id_Formations = ? AND Places_dispo > 0",
		idFormation,
	)

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"message": "Inscription confirmée",
	})
}

func AdminGetFormations(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT f.Id_Formations, COALESCE(f.Titre,''), COALESCE(f.Description,''),
			COALESCE(f.Prix,0), COALESCE(f.Duree,0), COALESCE(f.Statut,'en_attente'),
			COALESCE(f.Date_formation,''), COALESCE(f.Places_total,0), COALESCE(f.Places_dispo,0),
			COALESCE(f.Localisation,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Formations f
		LEFT JOIN Salaries s ON s.Id_Salaries = f.Id_Salaries
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY f.Id_Formations DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree, pTotal, pDispo int
		var titre, desc, statut, date, loc, nom, prenom string
		var prix float64
		rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &nom, &prenom)
		formations = append(formations, map[string]interface{}{
			"id": id, "titre": titre, "description": desc, "prix": prix,
			"duree": duree, "statut": statut, "date_formation": date,
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
			database.DB.Exec("UPDATE Formations SET Statut='actif' WHERE Id_Formations=?", id)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation validée"})
			return
		case "rejeter":
			database.DB.Exec("UPDATE Formations SET Statut='rejete' WHERE Id_Formations=?", id)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation rejetée"})
			return
		}
	}

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Titre          string  `json:"titre"`
			Description    string  `json:"description"`
			Prix           float64 `json:"prix"`
			Duree          int     `json:"duree"`
			Statut         string  `json:"statut"`
			DateFormation  string  `json:"date_formation"`
			PlacesTotal    int     `json:"places_total"`
			Localisation   string  `json:"localisation"`
			IdSalaries     int     `json:"id_salaries"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		if body.PlacesTotal == 0 {
			body.PlacesTotal = 20
		}
		if body.Statut == "" {
			body.Statut = "en_attente"
		}
		result, err := database.DB.Exec(
			`INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Date_formation, Places_total, Places_dispo, Localisation, Id_Salaries)
			VALUES (?,?,?,?,?,?,?,?,?,?)`,
			body.Titre, body.Description, body.Prix, body.Duree, body.Statut,
			body.DateFormation, body.PlacesTotal, body.PlacesTotal, body.Localisation, body.IdSalaries,
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