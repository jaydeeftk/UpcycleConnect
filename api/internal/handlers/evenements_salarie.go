package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/httpx"
)

func GetEvenementsSalarie(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT e.Id_Evenements, e.Titre, e.Description, e.Lieu, COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), e.Capacite, e.Statut,
		       u.Nom, u.Prenom
		FROM Evenements e
		JOIN Salaries s ON e.Id_Salaries = s.Id_Salaries
		JOIN Utilisateurs u ON s.Id_Utilisateurs = u.Id_Utilisateurs
		ORDER BY e.Id_Evenements DESC
	`)
	if err != nil {
		http.Error(w, `{"message": "Erreur base de données"}`, http.StatusInternalServerError)
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
			"type":          "evenement",
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
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if err := domain.ValiderCreationEvenement(body.Titre, body.Date, body.Lieu, body.Capacite, 0); err != nil {
		httpx.WriteError(w, err)
		return
	}
	if body.IdUtilisateurs == 0 {
		http.Error(w, `{"message": "id_salaries requis"}`, http.StatusBadRequest)
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

	// Statut = cycle de vie (a_venir = futur), Statut_validation = moderation.
	result, err := database.DB.Exec(`
		INSERT INTO Evenements (Titre, Description, Lieu, Date_, Capacite, Statut, Statut_validation, Id_Salaries)
		VALUES (?, ?, ?, ?, ?, 'a_venir', 'en_attente', ?)
	`, body.Titre, body.Description, body.Lieu, body.Date, body.Capacite, idSalaries)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la création"}`, http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(`
		INSERT INTO Animer (Id_Salaries, Id_Evenements) VALUES (?, ?)
	`, idSalaries, id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":       "Événement créé avec succès",
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
		SELECT Id_Evenements, Titre, Description, Lieu, COALESCE(DATE_FORMAT(Date_, '%Y-%m-%dT%H:%i:%s'),''), Capacite, Statut
		FROM Evenements
		WHERE Id_Evenements = ?
	`, id).Scan(&idEvenement, &titre, &description, &lieu, &date, &capacite, &statut)

	if err != nil {
		http.Error(w, `{"message": "Événement introuvable"}`, http.StatusNotFound)
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
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		http.Error(w, `{"message": "Profil salarié introuvable"}`, http.StatusForbidden)
		return
	}
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
		http.Error(w, `{"message": "Données invalides"}`, http.StatusBadRequest)
		return
	}

	if err := domain.ValiderCreationEvenement(body.Titre, body.Date, body.Lieu, body.Capacite, 0); err != nil {
		httpx.WriteError(w, err)
		return
	}

	_, err := database.DB.Exec(`
		UPDATE Evenements SET Titre = ?, Description = ?, Lieu = ?, Date_ = ?, Capacite = ?,
			Statut_validation = 'en_attente', Motif_refus = NULL
		WHERE Id_Evenements = ? AND Id_Salaries = ?
	`, body.Titre, body.Description, body.Lieu, body.Date, body.Capacite, id, salarieID)

	if err != nil {
		http.Error(w, `{"message": "Erreur lors de la mise à jour"}`, http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Événement mis à jour avec succès",
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
