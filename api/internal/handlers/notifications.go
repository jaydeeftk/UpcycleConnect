package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/notifier"
)

// MesNotifications retourne les notifications de l'utilisateur connecté
// (toutes catégories de rôles), de la plus récente à la plus ancienne.
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

// MarquerNotificationLue marque comme lue une notification appartenant à
// l'utilisateur connecté. Route : /api/notifications/{id}/lu.
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
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}

		if body.IdUtilisateurs == 0 {
			rows, err := database.DB.Query("SELECT Id_Utilisateurs FROM Utilisateurs")
			if err != nil {
				httpx.JSONServerError(w, err)
				return
			}
			defer rows.Close()
			var ids []int
			for rows.Next() {
				var uid int
				rows.Scan(&uid)
				ids = append(ids, uid)
			}
			for _, uid := range ids {
				database.DB.Exec(
					"INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs) VALUES (?, NOW(), 0, ?, ?)",
					body.Contenu, body.IdAdministrateurs, uid,
				)
			}
			go notifier.SendPush(ids, body.Contenu)
			httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Notification envoyée à tous", "count": len(ids)})
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs) VALUES (?, NOW(), 0, ?, ?)",
			body.Contenu, body.IdAdministrateurs, body.IdUtilisateurs,
		)
		if err != nil {
			httpx.JSONServerError(w, err)
			return
		}
		newID, _ := result.LastInsertId()
		go notifier.SendPush([]int{body.IdUtilisateurs}, body.Contenu)
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Notifications WHERE Id_Notifications=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Notification supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
