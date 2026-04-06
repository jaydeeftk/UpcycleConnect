package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Evenements, Titre, Description, Date_, Lieu, Capacite, Statut FROM Evenements ORDER BY Date_ DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Evenement struct {
		ID          int    `json:"id"`
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Date        string `json:"date"`
		Lieu        string `json:"lieu"`
		Capacite    int    `json:"capacite"`
		Statut      string `json:"statut"`
	}

	evts := []Evenement{}
	for rows.Next() {
		var e Evenement
		rows.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut)
		evts = append(evts, e)
	}
	httpx.JSONOK(w, http.StatusOK, evts)
}

func GetEvenement(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/evenements/")
	row := database.DB.QueryRow(
		"SELECT Id_Evenements, Titre, Description, Date_, Lieu, Capacite, Statut FROM Evenements WHERE Id_Evenements=?", id,
	)
	var e struct {
		ID          int    `json:"id"`
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Date        string `json:"date"`
		Lieu        string `json:"lieu"`
		Capacite    int    `json:"capacite"`
		Statut      string `json:"statut"`
	}
	if err := row.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Événement introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, e)
}

func AdminGetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, e.Description, e.Date_, e.Lieu, e.Capacite, e.Statut,
			u.Nom, u.Prenom
		FROM Evenements e
		JOIN Salaries s ON s.Id_Salaries = e.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY e.Date_ DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
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
			Titre       string `json:"titre"`
			Description string `json:"description"`
			Date        string `json:"date"`
			Lieu        string `json:"lieu"`
			Capacite    int    `json:"capacite"`
			Statut      string `json:"statut"`
			IdSalaries  int    `json:"id_salaries"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Evenements (Titre, Description, Date_, Lieu, Capacite, Statut, Id_Salaries) VALUES (?,?,?,?,?,?,?)",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, body.IdSalaries,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodPut:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			Date        string `json:"date"`
			Lieu        string `json:"lieu"`
			Capacite    int    `json:"capacite"`
			Statut      string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Evenements SET Titre=?, Description=?, Date_=?, Lieu=?, Capacite=?, Statut=? WHERE Id_Evenements=?",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Evenements WHERE Id_Evenements=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
