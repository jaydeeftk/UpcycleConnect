package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"time"

	"upcycleconnect/internal/database"
)

func GetConseils(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT c.Id_Conseils, c.Contenu, c.Date_d_ajout, c.Id_Salaries,
		       u.Nom, u.Prenom
		FROM Conseils c
		JOIN Salaries s ON c.Id_Salaries = s.Id_Salaries
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		ORDER BY c.Date_d_ajout DESC
	`)
	if err != nil {
		http.Error(w, `{"message": "Erreur base de données"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var conseils []map[string]interface{}
	for rows.Next() {
		var id, idSalaries int
		var contenu, dateAjout, nom, prenom string

		if err := rows.Scan(&id, &contenu, &dateAjout, &idSalaries, &nom, &prenom); err != nil {
			continue
		}

		conseils = append(conseils, map[string]interface{}{
			"id_conseils":  id,
			"contenu":      contenu,
			"date_d_ajout": dateAjout,
			"id_salaries":  idSalaries,
			"auteur":       prenom + " " + nom,
		})
	}

	if conseils == nil {
		conseils = []map[string]interface{}{}
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": conseils,
	})
}

func CreateConseil(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Contenu    string `json:"contenu"`
		IdSalaries int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Contenu == "" || body.IdSalaries == 0 {
		http.Error(w, `{"message": "Contenu et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	dateAjout := time.Now().Format("2006-01-02 15:04:05")

	result, err := database.DB.Exec(`
		INSERT INTO Conseils (Contenu, Date_d_ajout, Id_Salaries)
		VALUES (?, ?, ?)
	`, body.Contenu, dateAjout, body.IdSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":    "Conseil créé avec succès",
		"id_conseil": id,
	})
}

func ConseilAction(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		GetConseil(w, r)
	case http.MethodPut:
		UpdateConseil(w, r)
	case http.MethodDelete:
		DeleteConseil(w, r)
	default:
		http.Error(w, `{"message": "Méthode non autorisée"}`, http.StatusMethodNotAllowed)
	}
}

func GetConseil(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var idConseil int
	var contenu, dateAjout string
	var idSalaries int

	err := database.DB.QueryRow(`
		SELECT Id_Conseils, Contenu, Date_d_ajout, Id_Salaries
		FROM Conseils
		WHERE Id_Conseils = ?
	`, id).Scan(&idConseil, &contenu, &dateAjout, &idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Conseil introuvable"}`, http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": map[string]interface{}{
			"id_conseils":  idConseil,
			"contenu":      contenu,
			"date_d_ajout": dateAjout,
			"id_salaries":  idSalaries,
		},
	})
}

func UpdateConseil(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	var body struct {
		Contenu string `json:"contenu"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Contenu == "" {
		http.Error(w, `{"message": "Contenu requis"}`, http.StatusBadRequest)
		return
	}

	_, err := database.DB.Exec(`
		UPDATE Conseils SET Contenu = ? WHERE Id_Conseils = ?
	`, body.Contenu, id)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la mise à jour"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Conseil mis à jour avec succès",
	})
}

func DeleteConseil(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	_, err := database.DB.Exec(`DELETE FROM Conseils WHERE Id_Conseils = ?`, id)
	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la suppression"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Conseil supprimé avec succès",
	})
}
