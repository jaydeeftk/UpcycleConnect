package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var inscriptionSvc = services.NewInscriptionService()

func GetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, e.Description, COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), e.Lieu, e.Capacite, e.Statut,
		        COALESCE(e.Prix,0), COALESCE(e.Categorie,''), COALESCE(e.Duree,0),
		        (SELECT COUNT(*) FROM Participer_evenements pe WHERE pe.Id_Evenements = e.Id_Evenements)
		 FROM Evenements e ORDER BY e.Date_ DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	type Evenement struct {
		ID           int     `json:"id"`
		Titre        string  `json:"titre"`
		Description  string  `json:"description"`
		Date         string  `json:"date"`
		Lieu         string  `json:"lieu"`
		Capacite     int     `json:"capacite"`
		Statut       string  `json:"statut"`
		Prix         float64 `json:"prix"`
		Categorie    string  `json:"categorie"`
		Duree        int     `json:"duree"`
		Participants int     `json:"participants"`
	}

	evts := []Evenement{}
	for rows.Next() {
		var e Evenement
		rows.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut, &e.Prix, &e.Categorie, &e.Duree, &e.Participants)
		evts = append(evts, e)
	}
	httpx.JSONOK(w, http.StatusOK, evts)
}

func GetEvenement(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/evenements/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if len(parts) >= 2 && parts[1] == "participer" {
		middleware.JWTAuth(ParticiperEvenement)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "desinscrire" {
		middleware.JWTAuth(DesinscrireEvenement)(w, r)
		return
	}

	middleware.OptionalJWT(ficheEvenement)(w, r)
}

func ficheEvenement(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/evenements/")
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
	dto, err := inscriptionSvc.FicheEvenement(viewer, id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func ParticiperEvenement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/evenements/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID événement manquant")
		return
	}
	if err := inscriptionSvc.ParticiperEvenement(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Inscription confirmée"})
}

func DesinscrireEvenement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/evenements/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID événement manquant")
		return
	}
	if err := inscriptionSvc.DesinscrireEvenement(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription effectuée"})
}

func AdminGetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, e.Description, COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), e.Lieu, e.Capacite, e.Statut,
			COALESCE(u.Nom, ''), COALESCE(u.Prenom, '')
		FROM Evenements e
		LEFT JOIN Salaries s ON s.Id_Salaries = e.Id_Salaries
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY e.Date_ DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	type Evt struct {
		ID          int    `json:"id"`
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Date        string `json:"date"`
		Lieu        string `json:"lieu"`
		Capacite    int    `json:"capacite"`
		Statut      string `json:"statut"`
		NomSalarie  string `json:"nom_salarie"`
		PrenomSal   string `json:"prenom_salarie"`
	}

	evts := []Evt{}
	for rows.Next() {
		var e Evt
		rows.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut, &e.NomSalarie, &e.PrenomSal)
		evts = append(evts, e)
	}
	httpx.JSONOK(w, http.StatusOK, evts)
}

func AdminCreateEvenement(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/evenements/")

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Titre       string  `json:"titre"`
			Description string  `json:"description"`
			Date        string  `json:"date"`
			Lieu        string  `json:"lieu"`
			Capacite    int     `json:"capacite"`
			Statut      string  `json:"statut"`
			Prix        float64 `json:"prix"`
			Categorie   string  `json:"categorie"`
			Duree       int     `json:"duree"`
			IdSalaries  *int    `json:"id_salaries"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if body.Categorie == "" {
			body.Categorie = "atelier"
		}
		if body.Duree <= 0 {
			body.Duree = 2
		}
		result, err := database.DB.Exec(
			"INSERT INTO Evenements (Titre, Description, Date_, Lieu, Capacite, Statut, Prix, Categorie, Duree, Id_Salaries) VALUES (?,?,?,?,?,?,?,?,?,?)",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, body.Prix, body.Categorie, body.Duree, body.IdSalaries,
		)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodPut:
		var body struct {
			Titre       string  `json:"titre"`
			Description string  `json:"description"`
			Date        string  `json:"date"`
			Lieu        string  `json:"lieu"`
			Capacite    int     `json:"capacite"`
			Statut      string  `json:"statut"`
			Prix        float64 `json:"prix"`
			Categorie   string  `json:"categorie"`
			Duree       int     `json:"duree"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if body.Categorie == "" {
			body.Categorie = "atelier"
		}
		if body.Duree <= 0 {
			body.Duree = 2
		}
		_, err := database.DB.Exec(
			"UPDATE Evenements SET Titre=?, Description=?, Date_=?, Lieu=?, Capacite=?, Statut=?, Prix=?, Categorie=?, Duree=? WHERE Id_Evenements=?",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, body.Prix, body.Categorie, body.Duree, id,
		)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement mis à jour"})

	case http.MethodDelete:
		_, err := database.DB.Exec("DELETE FROM Evenements WHERE Id_Evenements=?", id)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminDeleteEvenement(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Animer WHERE Id_Evenements = ?", id)
	database.DB.Exec("DELETE FROM Participer_evenements WHERE Id_Evenements = ?", id)
	database.DB.Exec("DELETE FROM Planifier_evenements WHERE Id_Evenements = ?", id)
	database.DB.Exec("DELETE FROM Evenements WHERE Id_Evenements = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement supprimé"})
}
