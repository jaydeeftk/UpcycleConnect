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
		"SELECT Id_Evenements, Titre, Description, Date_, Lieu, Capacite, Statut, COALESCE(Prix,0) FROM Evenements ORDER BY Date_ DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Evenement struct {
		ID          int     `json:"id"`
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Date        string  `json:"date"`
		Lieu        string  `json:"lieu"`
		Capacite    int     `json:"capacite"`
		Statut      string  `json:"statut"`
		Prix        float64 `json:"prix"`
	}

	evts := []Evenement{}
	for rows.Next() {
		var e Evenement
		rows.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut, &e.Prix)
		evts = append(evts, e)
	}
	httpx.JSONOK(w, http.StatusOK, evts)
}

func GetEvenement(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/evenements/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if len(parts) >= 2 && parts[1] == "participer" {
		ParticiperEvenement(w, r)
		return
	}

	if len(parts) >= 2 && parts[1] == "desinscrire" {
		DesinscrireEvenement(w, r)
		return
	}

	id := parts[0]
	userId := r.URL.Query().Get("user_id")

	row := database.DB.QueryRow(
		"SELECT Id_Evenements, Titre, Description, Date_, Lieu, Capacite, Statut, COALESCE(Prix,0) FROM Evenements WHERE Id_Evenements=?", id,
	)
	var e struct {
		ID          int     `json:"id"`
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Date        string  `json:"date"`
		Lieu        string  `json:"lieu"`
		Capacite    int     `json:"capacite"`
		Statut      string  `json:"statut"`
		Prix        float64 `json:"prix"`
	}
	if err := row.Scan(&e.ID, &e.Titre, &e.Description, &e.Date, &e.Lieu, &e.Capacite, &e.Statut, &e.Prix); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Événement introuvable")
		return
	}

	estInscrit := false
	if userId != "" {
		var idParticulier int
		if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", userId).Scan(&idParticulier); err == nil {
			var count int
			database.DB.QueryRow("SELECT COUNT(*) FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?", idParticulier, id).Scan(&count)
			estInscrit = count > 0
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": e.ID, "titre": e.Titre, "description": e.Description,
		"date": e.Date, "lieu": e.Lieu, "capacite": e.Capacite,
		"statut": e.Statut, "prix": e.Prix, "est_inscrit": estInscrit,
	})
}

func DesinscrireEvenement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	path := strings.TrimPrefix(r.URL.Path, "/api/evenements/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	idEvenement := parts[0]

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

	database.DB.Exec("DELETE FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?", idParticulier, idEvenement)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription effectuée"})
}

func ParticiperEvenement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	parts := strings.Split(r.URL.Path, "/")
	idEvenement := ""
	for i, p := range parts {
		if p == "evenements" && i+1 < len(parts) {
			idEvenement = parts[i+1]
			break
		}
	}

	if idEvenement == "" {
		httpx.JSONError(w, http.StatusBadRequest, "ID événement manquant")
		return
	}

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
		"SELECT COUNT(*) FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?",
		idParticulier, idEvenement,
	).Scan(&count)
	if count > 0 {
		httpx.JSONError(w, http.StatusConflict, "Vous participez déjà à cet événement")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO Participer_evenements (Id_Particuliers, Id_Evenements) VALUES (?, ?)",
		idParticulier, idEvenement,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"message": "Inscription confirmée",
	})
}

func AdminGetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, e.Description, e.Date_, e.Lieu, e.Capacite, e.Statut,
			COALESCE(u.Nom, ''), COALESCE(u.Prenom, '')
		FROM Evenements e
		LEFT JOIN Salaries s ON s.Id_Salaries = e.Id_Salaries
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
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

func AdminDeleteEvenement(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Evenements WHERE Id_Evenements = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement supprimé"})
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
			IdSalaries  *int    `json:"id_salaries"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Evenements (Titre, Description, Date_, Lieu, Capacite, Statut, Prix, Id_Salaries) VALUES (?,?,?,?,?,?,?,?)",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, body.Prix, body.IdSalaries,
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
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		_, err := database.DB.Exec(
			"UPDATE Evenements SET Titre=?, Description=?, Date_=?, Lieu=?, Capacite=?, Statut=? WHERE Id_Evenements=?",
			body.Titre, body.Description, body.Date, body.Lieu, body.Capacite, body.Statut, id,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement mis à jour"})

	case http.MethodDelete:
		_, err := database.DB.Exec("DELETE FROM Evenements WHERE Id_Evenements=?", id)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
