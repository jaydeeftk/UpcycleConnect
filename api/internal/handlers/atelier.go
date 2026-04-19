package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"time"

	"upcycleconnect/internal/database"
)

func GetAteliers(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT a.Id_Atelier, a.Theme, a.Date_creation, a.Createur, a.Date_atelier, a.Lieu, a.Statut,
		       u.Nom, u.Prenom
		FROM Atelier a
		JOIN Salaries s ON a.Id_Salaries = s.Id_Salaries
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		ORDER BY a.Id_Atelier DESC
	`)
	if err != nil {
		http.Error(w, `{"message": "Erreur base de données"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var ateliers []map[string]interface{}
	for rows.Next() {
		var id int
		var theme, createur, lieu, statut, nom, prenom string
		var dateCreation, dateAtelier *string

		if err := rows.Scan(&id, &theme, &dateCreation, &createur, &dateAtelier, &lieu, &statut, &nom, &prenom); err != nil {
			continue
		}

		ateliers = append(ateliers, map[string]interface{}{
			"id_atelier":    id,
			"theme":         theme,
			"date_creation": dateCreation,
			"createur":      createur,
			"date_atelier":  dateAtelier,
			"lieu":          lieu,
			"statut":        statut,
			"auteur":        prenom + " " + nom,
		})
	}

	if ateliers == nil {
		ateliers = []map[string]interface{}{}
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": ateliers,
	})
}

func CreateAtelier(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Theme          string `json:"theme"`
		DateAtelier    string `json:"date_atelier"`
		Lieu           string `json:"lieu"`
		IdUtilisateurs int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Theme == "" || body.IdUtilisateurs == 0 {
		http.Error(w, `{"message": "Thème et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	var idSalaries int
	var nom, prenom string
	err := database.DB.QueryRow(`
		SELECT s.Id_Salaries, u.Nom, u.Prenom
		FROM Salaries s
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		WHERE s.Id_Utilisateurs = ?
	`, body.IdUtilisateurs).Scan(&idSalaries, &nom, &prenom)

	if err != nil {
		http.Error(w, `{"message": "Salarié introuvable"}`, http.StatusNotFound)
		return
	}

	dateCreation := time.Now().Format("2006-01-02 15:04:05")
	createur := prenom + " " + nom

	result, err := database.DB.Exec(`
		INSERT INTO Atelier (Theme, Date_creation, Createur, Date_atelier, Lieu, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, ?, 'en_attente', ?)
	`, body.Theme, dateCreation, createur, body.DateAtelier, body.Lieu, idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`
		INSERT INTO Animer_atelier (Id_Salaries, Id_Atelier) VALUES (?, ?)
	`, idSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":    "Atelier créé avec succès",
		"id_atelier": id,
	})
}

func AtelierAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	last := parts[len(parts)-1]
	if r.Method == http.MethodPost && last == "create" {
		CreateAtelier(w, r)
		return
	}
	switch r.Method {
	case http.MethodGet:
		GetAtelier(w, r)
	case http.MethodPut:
		UpdateAtelier(w, r)
	case http.MethodDelete:
		DeleteAtelier(w, r)
	default:
		http.Error(w, `{"message": "Méthode non autorisée"}`, http.StatusMethodNotAllowed)
	}
}

func GetAtelier(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var idAtelier int
	var theme, createur, lieu, statut string
	var dateCreation, dateAtelier *string

	err := database.DB.QueryRow(`
		SELECT Id_Atelier, Theme, Date_creation, Createur, Date_atelier, Lieu, Statut
		FROM Atelier
		WHERE Id_Atelier = ?
	`, id).Scan(&idAtelier, &theme, &dateCreation, &createur, &dateAtelier, &lieu, &statut)

	if err != nil {
		http.Error(w, `{"message": "Atelier introuvable"}`, http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": map[string]interface{}{
			"id_atelier":    idAtelier,
			"theme":         theme,
			"date_creation": dateCreation,
			"createur":      createur,
			"date_atelier":  dateAtelier,
			"lieu":          lieu,
			"statut":        statut,
		},
	})
}

func UpdateAtelier(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var body struct {
		Theme       string `json:"theme"`
		DateAtelier string `json:"date_atelier"`
		Lieu        string `json:"lieu"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Theme == "" {
		http.Error(w, `{"message": "Thème requis"}`, http.StatusBadRequest)
		return
	}

	_, err := database.DB.Exec(`
		UPDATE Atelier SET Theme = ?, Date_atelier = ?, Lieu = ?
		WHERE Id_Atelier = ?
	`, body.Theme, body.DateAtelier, body.Lieu, id)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la mise à jour"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Atelier mis à jour avec succès",
	})
}

func DeleteAtelier(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	database.DB.Exec(`DELETE FROM Animer_atelier WHERE Id_Atelier = ?`, id)

	_, err := database.DB.Exec(`DELETE FROM Atelier WHERE Id_Atelier = ?`, id)
	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la suppression"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Atelier supprimé avec succès",
	})
}
