package handlers

import (
	"encoding/json"
	"log"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/notifier"
	"upcycleconnect/internal/services"
)

func MesNotifications(w http.ResponseWriter, r *http.Request) {
	uid := middleware.GetUserID(r)
	if uid == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}
	rows, err := database.DB.Query(
		`SELECT Id_Notifications, COALESCE(Contenu,''), COALESCE(Date_Envoi,''), COALESCE(Statut,0)
		FROM Notifications WHERE Id_Utilisateurs = ? ORDER BY Date_Envoi DESC LIMIT 50`, uid,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	type Notif struct {
		ID      int    `json:"id"`
		Contenu string `json:"contenu"`
		Date    string `json:"date_envoi"`
		Lu      bool   `json:"lu"`
	}
	notifs := []Notif{}
	nonLues := 0
	for rows.Next() {
		var n Notif
		var statut int
		rows.Scan(&n.ID, &n.Contenu, &n.Date, &statut)
		n.Lu = statut == 1
		if !n.Lu {
			nonLues++
		}
		notifs = append(notifs, n)
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"notifications": notifs,
		"non_lues":      nonLues,
	})
}

func MarquerNotificationLue(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost && r.Method != http.MethodPatch {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	uid := middleware.GetUserID(r)
	if uid == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}
	id := strings.TrimSuffix(strings.TrimPrefix(r.URL.Path, "/api/notifications/"), "/lu")
	id = strings.Trim(id, "/")
	res, err := database.DB.Exec(
		"UPDATE Notifications SET Statut = 1 WHERE Id_Notifications = ? AND Id_Utilisateurs = ?", id, uid,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	n, _ := res.RowsAffected()
	if n == 0 {
		httpx.JSONError(w, http.StatusNotFound, "Notification introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Notification marquée comme lue"})
}

func AdminGetNotifications(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT n.Id_Notifications, n.Contenu, n.Date_Envoi, n.Statut,
			u.Nom, u.Prenom, u.Email
		FROM Notifications n
		JOIN Utilisateurs u ON u.Id_Utilisateurs = n.Id_Utilisateurs
		ORDER BY n.Date_Envoi DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
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
			EnvoyerEmail      bool   `json:"envoyer_email"`
			EnvoyerPush       bool   `json:"envoyer_push"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}

		type cible struct {
			id    int
			email string
		}
		var cibles []cible
		if body.IdUtilisateurs == 0 {
			rows, err := database.DB.Query("SELECT Id_Utilisateurs, COALESCE(Email,'') FROM Utilisateurs")
			if err != nil {
				httpx.JSONServerError(w, err)
				return
			}
			for rows.Next() {
				var c cible
				rows.Scan(&c.id, &c.email)
				cibles = append(cibles, c)
			}
			rows.Close()
		} else {
			c := cible{id: body.IdUtilisateurs}
			database.DB.QueryRow("SELECT COALESCE(Email,'') FROM Utilisateurs WHERE Id_Utilisateurs = ?", c.id).Scan(&c.email)
			cibles = append(cibles, c)
		}

		ids := make([]int, 0, len(cibles))
		emails := make([]string, 0, len(cibles))
		for _, c := range cibles {
			database.DB.Exec(
				"INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs) VALUES (?, NOW(), 0, ?, ?)",
				body.Contenu, body.IdAdministrateurs, c.id,
			)
			ids = append(ids, c.id)
			if c.email != "" {
				emails = append(emails, c.email)
			}
		}

		if body.EnvoyerPush {
			go notifier.SendPush(ids, body.Contenu)
		}
		if body.EnvoyerEmail {
			go func(dests []string, msg string) {
				for _, em := range dests {
					if err := services.SendGenericEmail(em, "UpcycleConnect — Notification", msg); err != nil {
						log.Printf("[mail] echec notif email a %s : %v", em, err)
					}
				}
			}(emails, body.Contenu)
		}

		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Notification envoyée", "count": len(ids), "emails": len(emails)})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Notifications WHERE Id_Notifications=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Notification supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
