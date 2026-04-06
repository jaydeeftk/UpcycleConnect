package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetNotifications(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT n.Id_Notifications, n.Contenu, n.Date_Envoi, n.Statut,
			u.Nom, u.Prenom, u.Email
		FROM Notifications n
		JOIN Utilisateurs u ON u.Id_Utilisateurs = n.Id_Utilisateurs
		ORDER BY n.Date_Envoi DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Notif struct {
		ID      int    `json:"id"`
		Contenu string `json:"contenu"`
		Date    string `json:"date_envoi"`
		Statut  int    `json:"statut"`
		Nom     string `json:"nom"`
		Prenom  string `json:"prenom"`
		Email   string `json:"email"`
	}

	notifs := []Notif{}
	for rows.Next() {
		var n Notif
		rows.Scan(&n.ID, &n.Contenu, &n.Date, &n.Statut, &n.Nom, &n.Prenom, &n.Email)
		notifs = append(notifs, n)
	}
	httpx.JSONOK(w, http.StatusOK, notifs)
}

func AdminNotificationAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/notifications/")

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Contenu           string `json:"contenu"`
			IdAdministrateurs int    `json:"id_administrateurs"`
			IdUtilisateurs    int    `json:"id_utilisateurs"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs) VALUES (?, NOW(), 0, ?, ?)",
			body.Contenu, body.IdAdministrateurs, body.IdUtilisateurs,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Notifications WHERE Id_Notifications=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Notification supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
