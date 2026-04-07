package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"time"

	"upcycleconnect/internal/database"
)

// GET /api/salarie/planning
func GetPlanning(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT 'evenement' AS type,
		       e.Id_Evenements AS id,
		       e.Titre,
		       e.Description,
		       e.Lieu,
		       e.Date_ AS date_debut,
		       NULL AS date_fin,
		       e.Statut,
		       CONCAT(u.Prenom, ' ', u.Nom) AS animateur
		FROM Evenements e
		LEFT JOIN Animer a ON e.Id_Evenements = a.Id_Evenements
		LEFT JOIN Salaries s ON a.Id_Salaries = s.Id_Salaries
		LEFT JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs

		UNION ALL

		SELECT 'formation' AS type,
		       f.Id_Formations AS id,
		       f.Titre,
		       f.Description,
		       NULL AS Lieu,
		       NULL AS date_debut,
		       NULL AS date_fin,
		       f.Statut,
		       CONCAT(u.Prenom, ' ', u.Nom) AS animateur
		FROM Formations f
		LEFT JOIN Animer_formation af ON f.Id_Formations = af.Id_Formations
		LEFT JOIN Salaries s ON af.Id_Salaries = s.Id_Salaries
		LEFT JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs

		UNION ALL

		SELECT 'atelier' AS type,
		       at.Id_Atelier AS id,
		       at.Theme AS Titre,
		       NULL AS Description,
		       at.Lieu,
		       at.Date_atelier AS date_debut,
		       NULL AS date_fin,
		       at.Statut,
		       CONCAT(u.Prenom, ' ', u.Nom) AS animateur
		FROM Atelier at
		LEFT JOIN Animer_atelier aa ON at.Id_Atelier = aa.Id_Atelier
		LEFT JOIN Salaries s ON aa.Id_Salaries = s.Id_Salaries
		LEFT JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs

		ORDER BY date_debut ASC
	`)

	if err != nil {
		http.Error(w, `{"message": "Erreur base de données"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var items []map[string]interface{}
	for rows.Next() {
		var typeItem, titre, statut string
		var id int
		var description, lieu, dateDebut, dateFin, animateur *string

		if err := rows.Scan(&typeItem, &id, &titre, &description, &lieu, &dateDebut, &dateFin, &statut, &animateur); err != nil {
			continue
		}

		items = append(items, map[string]interface{}{
			"type":        typeItem,
			"id":          id,
			"titre":       titre,
			"description": description,
			"lieu":        lieu,
			"date_debut":  dateDebut,
			"date_fin":    dateFin,
			"statut":      statut,
			"animateur":   animateur,
		})
	}

	if items == nil {
		items = []map[string]interface{}{}
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"data": items,
	})
}

// POST /api/salarie/planning/evenement/create
func CreateEvenementPlanning(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Lieu        string `json:"lieu"`
		Date        string `json:"date"`
		Capacite    int    `json:"capacite"`
		IdSalaries  int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Titre == "" || body.IdSalaries == 0 {
		http.Error(w, `{"message": "Titre et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	result, err := database.DB.Exec(`
		INSERT INTO Evenements (Titre, Description, Lieu, Date_, Capacite, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, ?, 'en_attente', ?)
	`, body.Titre, body.Description, body.Lieu, body.Date, body.Capacite, body.IdSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	// Lier dans Animer
	database.DB.Exec(`INSERT INTO Animer (Id_Salaries, Id_Evenements) VALUES (?, ?)`, body.IdSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Événement créé avec succès",
		"id":      id,
	})
}

// POST /api/salarie/planning/formation/create
func CreateFormationPlanning(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Prix        float64 `json:"prix"`
		Duree       int     `json:"duree"`
		IdSalaries  int     `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Titre == "" || body.IdSalaries == 0 {
		http.Error(w, `{"message": "Titre et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	result, err := database.DB.Exec(`
		INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, 'en_attente', ?)
	`, body.Titre, body.Description, body.Prix, body.Duree, body.IdSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`INSERT INTO Animer_formation (Id_Salaries, Id_Formations) VALUES (?, ?)`, body.IdSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Formation créée avec succès",
		"id":      id,
	})
}

// POST /api/salarie/planning/atelier/create
func CreateAtelierPlanning(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Theme      string `json:"theme"`
		Lieu       string `json:"lieu"`
		Date       string `json:"date"`
		IdSalaries int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if body.Theme == "" || body.IdSalaries == 0 {
		http.Error(w, `{"message": "Thème et id_salaries requis"}`, http.StatusBadRequest)
		return
	}

	dateCreation := time.Now().Format("2006-01-02 15:04:05")

	result, err := database.DB.Exec(`
		INSERT INTO Atelier (Theme, Lieu, Date_creation, Date_atelier, Statut, Id_Salaries)
		VALUES (?, ?, ?, ?, 'en_attente', ?)
	`, body.Theme, body.Lieu, dateCreation, body.Date, body.IdSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`INSERT INTO Animer_atelier (Id_Salaries, Id_Atelier) VALUES (?, ?)`, body.IdSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Atelier créé avec succès",
		"id":      id,
	})
}

// DELETE /api/salarie/planning/evenement/{id}
func DeleteEvenementPlanning(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	database.DB.Exec(`DELETE FROM Animer WHERE Id_Evenements = ?`, id)
	database.DB.Exec(`DELETE FROM Planifier_evenements WHERE Id_Evenements = ?`, id)

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

// DELETE /api/salarie/planning/formation/{id}
func DeleteFormationPlanning(w http.ResponseWriter, r *http.Request) {
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

// DELETE /api/salarie/planning/atelier/{id}
func DeleteAtelierPlanning(w http.ResponseWriter, r *http.Request) {
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
