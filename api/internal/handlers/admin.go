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

	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&totalUtilisateurs)
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces").Scan(&totalAnnonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&totalEvenements)
	database.DB.QueryRow("SELECT COUNT(*) FROM Messages").Scan(&totalMessages)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"total_utilisateurs": totalUtilisateurs,
		"total_annonces":     totalAnnonces,
		"total_evenements":   totalEvenements,
		"total_messages":     totalMessages,
	})
}

func AdminGetUtilisateurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Utilisateurs, Nom, Prenom, Email, Statut, Date_Inscription FROM Utilisateurs",
	)

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
	rows, err := database.DB.Query(
		"SELECT Id_Evenements, Titre, Description, Lieu, Date_Evenement FROM Evenements",
	)

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
		SELECT m.Id_Messages, m.Contenu, u.Nom, u.Prenom, u.Email
		FROM Messages m
		JOIN Particuliers p ON p.Id_Particuliers = m.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
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
	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Contenu, Date_publication, Statut FROM Annonces",
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}

	for rows.Next() {
		var id int
		var contenu, statut string
		var date *string

		rows.Scan(&id, &contenu, &date, &statut)

		annonces = append(annonces, map[string]interface{}{
			"id":      id,
			"contenu": contenu,
			"date":    date,
			"statut":  statut,
		})
	}

	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminDeleteUtilisateur(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.TrimSuffix(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	_, err := database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs = ?", id)
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
		IdSalaries  int    `json:"id_salaries"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO Evenements (Titre, Description, Lieu, Date_Evenement, Id_Salaries) VALUES (?, ?, ?, ?, ?)",
		body.Titre, body.Description, body.Lieu, body.Date, body.IdSalaries,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]string{"message": "Événement créé"})
}

func AdminUtilisateurAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.TrimSuffix(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	if r.Method == "DELETE" || (r.Method == "GET" && strings.Contains(r.URL.Path, "delete")) {
		_, err := database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs = ?", id)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Utilisateur supprimé"})
		return
	}

	row := database.DB.QueryRow(
		"SELECT Id_Utilisateurs, Nom, Prenom, Email, Statut FROM Utilisateurs WHERE Id_Utilisateurs = ?", id,
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
	httpx.JSONOK(w, http.StatusOK, []interface{}{})
}

func AdminCreateCategorie(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusCreated, map[string]string{"message": "Non implémenté"})
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Non implémenté"})
}
