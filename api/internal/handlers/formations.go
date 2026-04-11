package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
)

func GetFormations(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT f.Id_Formations, f.Titre, f.Description, f.Prix, f.Duree, f.Date_, f.Lieu, f.Statut,
		       u.Nom, u.Prenom
		FROM Formations f
		JOIN Salaries s ON f.Id_Salaries = s.Id_Salaries
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		ORDER BY f.Id_Formations DESC
	`)
	if err != nil {
		http.Error(w, `{"message": "Erreur base de données"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var formations []map[string]interface{}
	for rows.Next() {
		var id, duree int
		var titre, description, statut, nom, prenom string
		var prix float64
		var date, lieu *string

		if err := rows.Scan(&id, &titre, &description, &prix, &duree, &date, &lieu, &statut, &nom, &prenom); err != nil {
			http.Error(w, `{"message": "Erreur scan: `+err.Error()+`"}`, http.StatusInternalServerError)
			return
		}

		formations = append(formations, map[string]interface{}{
			"id_formations": id,
			"titre":         titre,
			"description":   description,
			"prix":          prix,
			"duree":         duree,
			"date":          date,
			"lieu":          lieu,
			"statut":        statut,
			"auteur":        prenom + " " + nom,
		})
	}

	if formations == nil {
		formations = []map[string]interface{}{}
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": formations,
	})
}

func CreateFormation(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre          string  `json:"titre"`
		Description    string  `json:"description"`
		Prix           float64 `json:"prix"`
		Duree          int     `json:"duree"`
		Date           string  `json:"date"`
		Lieu           string  `json:"lieu"`
		IdUtilisateurs int     `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
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
		http.Error(w, `{"message": "Salarié introuvable"}`, http.StatusNotFound)
		return
	}

	result, err := database.DB.Exec(`
		INSERT INTO Formations (Titre, Description, Prix, Duree, Date_, Lieu, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?)
	`, body.Titre, body.Description, body.Prix, body.Duree, body.Date, body.Lieu, idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`
		INSERT INTO Animer_formation (Id_Salaries, Id_Formations) VALUES (?, ?)
	`, idSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":       "Formation créée avec succès",
		"id_formations": id,
	})
}

func FormationAction(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		GetFormation(w, r)
	case http.MethodPut:
		UpdateFormation(w, r)
	case http.MethodDelete:
		DeleteFormation(w, r)
	default:
		http.Error(w, `{"message": "Méthode non autorisée"}`, http.StatusMethodNotAllowed)
	}
}

func GetFormation(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var idFormation, duree int
	var titre, description, statut string
	var prix float64
	var date, lieu *string

	err := database.DB.QueryRow(`
		SELECT Id_Formations, Titre, Description, Prix, Duree, Date_, Lieu, Statut
		FROM Formations
		WHERE Id_Formations = ?
	`, id).Scan(&idFormation, &titre, &description, &prix, &duree, &date, &lieu, &statut)

	if err != nil {
		http.Error(w, `{"message": "Formation introuvable"}`, http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": map[string]interface{}{
			"id_formations": idFormation,
			"titre":         titre,
			"description":   description,
			"prix":          prix,
			"duree":         duree,
			"date":          date,
			"lieu":          lieu,
			"statut":        statut,
		},
	})
}

func UpdateFormation(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var body struct {
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Prix        float64 `json:"prix"`
		Duree       int     `json:"duree"`
		Date        string  `json:"date"`
		Lieu        string  `json:"lieu"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Titre == "" {
		http.Error(w, `{"message": "Titre requis"}`, http.StatusBadRequest)
		return
	}

	_, err := database.DB.Exec(`
		UPDATE Formations SET Titre = ?, Description = ?, Prix = ?, Duree = ?, Date_ = ?, Lieu = ?
		WHERE Id_Formations = ?
	`, body.Titre, body.Description, body.Prix, body.Duree, body.Date, body.Lieu, id)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la mise à jour"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Formation mise à jour avec succès",
	})
}

func DeleteFormation(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	database.DB.Exec(`DELETE FROM Animer_formation WHERE Id_Formations = ?`, id)
	database.DB.Exec(`DELETE FROM Planifier_formation WHERE Id_Formations = ?`, id)

	_, err := database.DB.Exec(`DELETE FROM Formations WHERE Id_Formations = ?`, id)
	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la suppression"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Formation supprimée avec succès",
	})
}
