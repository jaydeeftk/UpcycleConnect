package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminDashboard(w http.ResponseWriter, r *http.Request) {
	var totalUsers, totalAnnonces, totalEvenements, totalMessages int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&totalUsers)
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces").Scan(&totalAnnonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&totalEvenements)
	database.DB.QueryRow("SELECT COUNT(*) FROM Messages").Scan(&totalMessages)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"total_utilisateurs": totalUsers,
		"total_annonces":     totalAnnonces,
		"total_evenements":   totalEvenements,
		"total_messages":     totalMessages,
	})
}

func AdminGetUtilisateurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Utilisateurs, Nom, Prenom, Email, Statut, Date_Inscription FROM Utilisateurs ORDER BY Date_Inscription DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	users := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, prenom, email, statut, date string
		rows.Scan(&id, &nom, &prenom, &email, &statut, &date)
		users = append(users, map[string]interface{}{
			"id": id, "nom": nom, "prenom": prenom,
			"email": email, "statut": statut, "date_inscription": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, users)
}

func AdminUtilisateurAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 4 {
		httpx.JSONError(w, http.StatusBadRequest, "ID manquant")
		return
	}
	id := parts[3]

	var id_u int
	var nom, prenom, email, statut, date string
	err := database.DB.QueryRow(
		"SELECT Id_Utilisateurs, Nom, Prenom, Email, Statut, Date_Inscription FROM Utilisateurs WHERE Id_Utilisateurs = ?", id,
	).Scan(&id_u, &nom, &prenom, &email, &statut, &date)
	if err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": id_u, "nom": nom, "prenom": prenom,
		"email": email, "statut": statut, "date_inscription": date,
	})
}

func AdminDeleteUtilisateur(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur supprimé"})
}

func AdminGetEvenements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Evenements, Titre, Description, Date_evenement, Lieu FROM Evenements ORDER BY Date_evenement DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	evenements := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var titre, description, date, lieu string
		rows.Scan(&id, &titre, &description, &date, &lieu)
		evenements = append(evenements, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"date_evenement": date, "lieu": lieu,
		})
	}
	httpx.JSONOK(w, http.StatusOK, evenements)
}

func AdminCreateEvenement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Titre         string `json:"titre"`
		Description   string `json:"description"`
		Lieu          string `json:"lieu"`
		DateEvenement string `json:"date_evenement"`
		IdSalarie     int    `json:"id_salarie"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	_, err := database.DB.Exec(
		"INSERT INTO Evenements (Titre, Description, Lieu, Date_evenement, Id_Salaries) VALUES (?, ?, ?, ?, ?)",
		body.Titre, body.Description, body.Lieu, body.DateEvenement, body.IdSalarie,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Événement créé"})
}

func AdminGetMessages(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Messages, Contenu, Date_envoi, Id_Utilisateurs FROM Messages ORDER BY Date_envoi DESC LIMIT 50",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	messages := []map[string]interface{}{}
	for rows.Next() {
		var id, userId int
		var contenu, date string
		rows.Scan(&id, &contenu, &date, &userId)
		messages = append(messages, map[string]interface{}{
			"id": id, "contenu": contenu, "date_envoi": date, "id_utilisateur": userId,
		})
	}
	httpx.JSONOK(w, http.StatusOK, messages)
}

func AdminGetCategories(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodPost {
		AdminCreateCategorie(w, r)
		return
	}
	rows, err := database.DB.Query("SELECT Id_Categories, Nom, Description FROM Categories")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	categories := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, description string
		rows.Scan(&id, &nom, &description)
		categories = append(categories, map[string]interface{}{
			"id": id, "nom": nom, "description": description,
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
	json.NewDecoder(r.Body).Decode(&body)
	_, err := database.DB.Exec(
		"INSERT INTO Categories (Nom, Description) VALUES (?, ?)",
		body.Nom, body.Description,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Catégorie créée"})
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Categories WHERE Id_Categories = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Catégorie supprimée"})
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Titre, Description, Statut, Date_publication FROM Annonces ORDER BY Date_publication DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	annonces := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var titre, description, statut, date string
		rows.Scan(&id, &titre, &description, &statut, &date)
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"statut": statut, "date_publication": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminGetParametres(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"site_nom":         "UpcycleConnect",
		"site_description": "Plateforme de l'économie circulaire",
		"email_contact":    "contact@upcycleconnect.fr",
		"maintenance":      false,
	})
}

func AdminGetFormations(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Formations, Titre, Description, Prix, Duree, Statut, COALESCE(Date_formation,''), COALESCE(Places_total,0), COALESCE(Places_dispo,0) FROM Formations ORDER BY Date_formation DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()
	formations := []map[string]interface{}{}
	for rows.Next() {
		var id, duree, placesTotal, placesDispo int
		var titre, description, statut, date string
		var prix float64
		rows.Scan(&id, &titre, &description, &prix, &duree, &statut, &date, &placesTotal, &placesDispo)
		formations = append(formations, map[string]interface{}{
			"id": id, "titre": titre, "description": description, "prix": prix,
			"duree": duree, "statut": statut, "date": date,
			"places_total": placesTotal, "places_dispo": placesDispo,
		})
	}
	httpx.JSONOK(w, http.StatusOK, formations)
}

func AdminDeleteFormation(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Formations WHERE Id_Formations = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation supprimée"})
}

func AdminGetContrats(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, []interface{}{})
}

func AdminGetFactures(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Paiements, Montant, Statut, Date_Paiement FROM Paiements ORDER BY Date_Paiement DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()
	factures := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var montant float64
		var statut bool
		var date string
		rows.Scan(&id, &montant, &statut, &date)
		factures = append(factures, map[string]interface{}{
			"id": id, "montant": montant, "statut": statut, "date": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, factures)
}

func AdminGetNotifications(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Notifications, Contenu, Date_envoi, Id_Utilisateurs FROM Notifications ORDER BY Date_envoi DESC LIMIT 20",
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	notifs := []map[string]interface{}{}
	for rows.Next() {
		var id, userId int
		var contenu, date string
		rows.Scan(&id, &contenu, &date, &userId)
		notifs = append(notifs, map[string]interface{}{
			"id": id, "message": contenu, "date": date, "id_utilisateur": userId,
		})
	}
	httpx.JSONOK(w, http.StatusOK, notifs)
}

func AdminSendNotification(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre   string `json:"titre"`
		Message string `json:"message"`
		Cible   string `json:"cible"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	database.DB.Exec(
		"INSERT INTO Notifications (Contenu, Date_envoi, Id_Utilisateurs) VALUES (?, NOW(), 1)",
		body.Titre+": "+body.Message,
	)
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Notification envoyée"})
}
