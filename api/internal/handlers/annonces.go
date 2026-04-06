package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func CreateAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	var body struct {
		Contenu       string `json:"contenu"`
		IdParticulier int    `json:"user_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Annonces (Contenu, Statut, Date_publication, Id_Particuliers) VALUES (?, 'en_attente', NOW(), ?)",
		body.Contenu, body.IdParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur BDD : "+err.Error())
		return
	}

	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Annonce soumise avec succès, en attente de validation",
	})
}

func GetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Contenu, Statut, Date_publication FROM Annonces WHERE Statut = 'validee' ORDER BY Date_publication DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var contenu, statut, date string
		rows.Scan(&id, &contenu, &statut, &date)
		annonces = append(annonces, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"statut":  statut,
			"date":    date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func GetAnnoncesUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idParticulier := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Contenu, Statut, Date_publication FROM Annonces WHERE Id_Particuliers = ? ORDER BY Date_publication DESC",
		idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var contenu, statut, date string
		rows.Scan(&id, &contenu, &statut, &date)
		annonces = append(annonces, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"statut":  statut,
			"date":    date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT a.Id_Annonces, a.Contenu, a.Statut, a.Date_publication,
			u.Nom, u.Prenom, u.Email
		FROM Annonces a
		JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY a.Date_publication DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var contenu, statut, date, nom, prenom, email string
		rows.Scan(&id, &contenu, &statut, &date, &nom, &prenom, &email)
		annonces = append(annonces, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"statut":  statut,
			"date":    date,
			"nom":     nom,
			"prenom":  prenom,
			"email":   email,
		})
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminAnnonceAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/annonces/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Statut string `json:"statut"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		database.DB.Exec("UPDATE Annonces SET Statut = ? WHERE Id_Annonces = ?", body.Statut, id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Statut mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Annonces WHERE Id_Annonces = ?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
