package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminDashboard(w http.ResponseWriter, r *http.Request) {

	var totalUtilisateurs, totalAnnonces, totalEvenements, totalMessages int

	database.DB.QueryRow("SELECT COUNT(*) FROM utilisateurs").Scan(&totalUtilisateurs)
	database.DB.QueryRow("SELECT COUNT(*) FROM annonces").Scan(&totalAnnonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM evenements").Scan(&totalEvenements)
	database.DB.QueryRow("SELECT COUNT(*) FROM messages").Scan(&totalMessages)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"total_utilisateurs": totalUtilisateurs,
		"total_annonces":     totalAnnonces,
		"total_evenements":   totalEvenements,
		"total_messages":     totalMessages,
	})
}

func AdminGetUtilisateurs(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query("SELECT id_utilisateur, nom, prenom, email, statut, date_inscription FROM utilisateurs")

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var utilisateurs []map[string]interface{}

	for rows.Next() {

		var id int
		var nom, prenom, email, statut string
		var date *string

		rows.Scan(&id, &nom, &prenom, &email, &statut, &date)

		utilisateurs = append(utilisateurs, map[string]interface{}{
			"id":         id,
			"nom":        nom,
			"prenom":     prenom,
			"email":      email,
			"statut":     statut,
			"created_at": date,
		})
	}

	httpx.JSONOK(w, http.StatusOK, utilisateurs)
}

func AdminGetEvenements(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query("SELECT id_evenement, titre, description, lieu, date_evenement FROM evenements")

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var evenements []map[string]interface{}

	for rows.Next() {

		var id int
		var titre, description, lieu string
		var date *string

		rows.Scan(&id, &titre, &description, &lieu, &date)

		evenements = append(evenements, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"description": description,
			"lieu":        lieu,
			"date":        date,
		})
	}

	httpx.JSONOK(w, http.StatusOK, evenements)
}

func AdminGetMessages(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query(`
		SELECT m.id_message, m.contenu, u.nom, u.prenom, u.email
		FROM messages m
		JOIN utilisateurs u ON u.id_utilisateur = m.id_particulier
	`)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var messages []map[string]interface{}

	for rows.Next() {

		var id int
		var contenu, nom, prenom, email string

		rows.Scan(&id, &contenu, &nom, &prenom, &email)

		messages = append(messages, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"nom":     nom,
			"prenom":  prenom,
			"email":   email,
		})
	}

	httpx.JSONOK(w, http.StatusOK, messages)
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query(`
		SELECT a.id_annonce, a.contenu, a.date_publication
		FROM annonces a
	`)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}

	for rows.Next() {

		var id int
		var contenu, date string

		rows.Scan(&id, &contenu, &date)

		annonces = append(annonces, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"date":    date,
		})
	}

	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminDeleteUtilisateur(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.TrimSuffix(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	_, err := database.DB.Exec("DELETE FROM utilisateurs WHERE id_utilisateur = ?", id)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Utilisateur supprimé"})
}

func AdminCreateEvenement(w http.ResponseWriter, r *http.Request) {

	var body struct {
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Lieu        string `json:"lieu"`
		Date        string `json:"date_evenement"`
		IdSalarie   int    `json:"id_salarie"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO evenements (titre, description, lieu, date_evenement, id_salarie) VALUES (?, ?, ?, ?, ?)",
		body.Titre, body.Description, body.Lieu, body.Date, body.IdSalarie,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]string{
		"message": "Événement créé",
	})

}

func AdminUtilisateurAction(w http.ResponseWriter, r *http.Request) {

	parts := strings.Split(strings.TrimSuffix(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	if r.Method == "DELETE" || (r.Method == "GET" && strings.Contains(r.URL.Path, "delete")) {
		_, err := database.DB.Exec("DELETE FROM utilisateurs WHERE id_utilisateur = ?", id)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Utilisateur supprimé"})
		return
	}

	row := database.DB.QueryRow(
		"SELECT id_utilisateur, nom, prenom, email, statut FROM utilisateurs WHERE id_utilisateur = ?", id,
	)

	var idU int
	var nom, prenom, email, statut string

	if err := row.Scan(&idU, &nom, &prenom, &email, &statut); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Utilisateur non trouvé")
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id":     idU,
		"nom":    nom,
		"prenom": prenom,
		"email":  email,
		"statut": statut,
	})
}

func AdminGetCategories(w http.ResponseWriter, r *http.Request) {

	rows, err := database.DB.Query("SELECT id_categorie, nom, description, icone, statut FROM categories")

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var categories []map[string]interface{}

	for rows.Next() {

		var id int
		var nom, statut string
		var description, icone *string

		rows.Scan(&id, &nom, &description, &icone, &statut)

		categories = append(categories, map[string]interface{}{
			"id":          id,
			"nom":         nom,
			"description": description,
			"icone":       icone,
			"statut":      statut,
		})
	}

	httpx.JSONOK(w, http.StatusOK, categories)
}

func AdminCreateCategorie(w http.ResponseWriter, r *http.Request) {

	var body struct {
		Nom         string `json:"nom"`
		Description string `json:"description"`
		Icone       string `json:"icone"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO categories (nom, description, icone, statut) VALUES (?, ?, ?, 'active')",
		body.Nom, body.Description, body.Icone,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]string{"message": "Catégorie créée"})
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {

	parts := strings.Split(strings.TrimSuffix(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	_, err := database.DB.Exec("DELETE FROM categories WHERE id_categorie = ?", id)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Catégorie supprimée"})
}
