package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
)

func GetEvenementsSalarie(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT e.Id_Evenements, e.Titre, e.Description, e.Lieu, e.Date_, e.Capacite, e.Statut,
		       u.Nom, u.Prenom
		FROM Evenements e
		JOIN Salaries s ON e.Id_Salaries = s.Id_Salaries
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		ORDER BY e.Id_Evenements DESC
	`)
	if err != nil {
		http.Error(w, `{"message": "Erreur base de donn├®es"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var evenements []map[string]interface{}
	for rows.Next() {
		var id, capacite int
		var titre, description, lieu, statut, nom, prenom string
		var date *string

		if err := rows.Scan(&id, &titre, &description, &lieu, &date, &capacite, &statut, &nom, &prenom); err != nil {
			continue
		}

		evenements = append(evenements, map[string]interface{}{
			"id_evenements": id,
			"titre":         titre,
			"description":   description,
			"lieu":          lieu,
			"date":          date,
			"capacite":      capacite,
			"statut":        statut,
			"auteur":        prenom + " " + nom,
		})
	}

	if evenements == nil {
		evenements = []map[string]interface{}{}
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": evenements,
	})
}

func CreateEvenement(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre          string `json:"titre"`
		Description    string `json:"description"`
		Lieu           string `json:"lieu"`
		Date           string `json:"date"`
		Capacite       int    `json:"capacite"`
		IdUtilisateurs int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Donn├®es invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Titre == "" || body.IdUtilisateurs == 0 {
		http.Error(w, `{"message": "Titre et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	var idSalaries int
	err := database.DB.QueryRow(`
		SELECT Id_Salaries FROM Salaries WHERE Id_Utilisateurs = ?
	`, body.IdUtilisateurs).Scan(&idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Salari├® introuvable"}`, http.StatusNotFound)
		return
	}

	result, err := database.DB.Exec(`
		INSERT INTO Evenements (Titre, Description, Lieu, Date_, Capacite, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, ?, 'en_attente', ?)
	`, body.Titre, body.Description, body.Lieu, body.Date, body.Capacite, idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la cr├®ation"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`
		INSERT INTO Animer (Id_Salaries, Id_Evenements) VALUES (?, ?)
	`, idSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":       "├ëv├®nement cr├®├® avec succ├¿s",
		"id_evenements": id,
	})
}

func GetEvenementSalarie(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var idEvenement, capacite int
	var titre, description, lieu, statut string
	var date *string

	err := database.DB.QueryRow(`
		SELECT Id_Evenements, Titre, Description, Lieu, Date_, Capacite, Statut
		FROM Evenements
		WHERE Id_Evenements = ?
	`, id).Scan(&idEvenement, &titre, &description, &lieu, &date, &capacite, &statut)

	if err != nil {
		http.Error(w, `{"message": "├ëv├®nement introuvable"}`, http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": map[string]interface{}{
			"id_evenements": idEvenement,
			"titre":         titre,
			"description":   description,
			"lieu":          lieu,
			"date":          date,
			"capacite":      capacite,
			"statut":        statut,
		},
	})
}

func UpdateEvenement(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var body struct {
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Lieu        string `json:"lieu"`
		Date        string `json:"date"`
		Capacite    int    `json:"capacite"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Donn├®es invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Titre == "" {
		http.Error(w, `{"message": "Titre requis"}`, http.StatusBadRequest)
		return
	}

	_, err := database.DB.Exec(`
		UPDATE Evenements SET Titre = ?, Description = ?, Lieu = ?, Date_ = ?, Capacite = ?
		WHERE Id_Evenements = ?
	`, body.Titre, body.Description, body.Lieu, body.Date, body.Capacite, id)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la mise ├á jour"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "├ëv├®nement mis ├á jour avec succ├¿s",
	})
}

func EvenementSalarieAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	last := parts[len(parts)-1]
	if r.Method == http.MethodPost && last == "create" {
		CreateEvenement(w, r)
		return
	}
	switch r.Method {
	case http.MethodGet:
		GetEvenementSalarie(w, r)
	case http.MethodPut:
		UpdateEvenement(w, r)
	case http.MethodDelete:
		DeleteEvenement(w, r)
	default:
		http.Error(w, `{"message": "Méthode non autorisée"}`, http.StatusMethodNotAllowed)
	}
}

func DeleteEvenement(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	database.DB.Exec(`DELETE FROM Animer WHERE Id_Evenements = ?`, id)
	database.DB.Exec(`DELETE FROM Planifier_evenements WHERE Id_Evenements = ?`, id)
	database.DB.Exec(`DELETE FROM Participer_evenements WHERE Id_Evenements = ?`, id)

	_, err := database.DB.Exec(`DELETE FROM Evenements WHERE Id_Evenements = ?`, id)
	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la suppression"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Événement supprimé avec succès",
	})
}
