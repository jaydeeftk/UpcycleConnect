package handlers

import (
	"io"
	"os"
	"path/filepath"
	"fmt"
	"time"
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"

	"github.com/golang-jwt/jwt/v5"
)

func SendMessage(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Contenu string `json:"contenu"`
		Sujet   string `json:"sujet"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || body.Contenu == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Contenu manquant")
		return
	}
	claims, ok := r.Context().Value(middleware.ClaimsKey).(jwt.MapClaims)
	if !ok {
		httpx.JSONError(w, http.StatusUnauthorized, "Non authentifié")
		return
	}
	sub, _ := claims["sub"].(float64)
	userID := int(sub)

	var particulierID int
	errPart := database.DB.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs=?", userID,
	).Scan(&particulierID)

	if errPart != nil {
		_, err := database.DB.Exec(
			"INSERT INTO Messages (Contenu, Date_envoi, Id_Utilisateurs) VALUES (?, NOW(), ?)",
			body.Contenu, userID,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
	} else {
		_, err := database.DB.Exec(
			"INSERT INTO Messages (Contenu, Date_envoi, Id_Particuliers, Id_Utilisateurs) VALUES (?, NOW(), ?, ?)",
			body.Contenu, particulierID, userID,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Message envoyé"})
}

func GetUserMessages(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	idUtilisateur := parts[len(parts)-1]
	rows, err := database.DB.Query(
		`SELECT m.Id_Messages, COALESCE(m.Contenu,''), COALESCE(CAST(m.Date_envoi AS CHAR),''),
			CASE WHEN m.Id_Utilisateurs IS NOT NULL AND m.Id_Particuliers IS NULL THEN 1 ELSE 0 END AS is_admin
		FROM Messages m
		LEFT JOIN Particuliers p ON p.Id_Particuliers = m.Id_Particuliers
		WHERE m.Id_Utilisateurs = ? OR p.Id_Utilisateurs = ?
		ORDER BY m.Id_Messages ASC`,
		idUtilisateur, idUtilisateur,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	msgs := []map[string]interface{}{}
	for rows.Next() {
		var id, isAdmin int
		var contenu, date string
		rows.Scan(&id, &contenu, &date, &isAdmin)
		msgs = append(msgs, map[string]interface{}{
			"id": id, "contenu": contenu, "date": date, "is_admin": isAdmin == 1,
		})
	}
	httpx.JSONOK(w, http.StatusOK, msgs)
}

func GetHistorique(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	idUtilisateur := parts[len(parts)-1]
	var idParticulier int
	if err := database.DB.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs=?", idUtilisateur,
	).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	rows, err := database.DB.Query(
		`SELECT Id_Historique, COALESCE(Date_Depot,''), COALESCE(Statut_depot,''), COALESCE(Observations,'')
		FROM Historique WHERE Id_Particuliers=? ORDER BY Date_Depot DESC`, idParticulier,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	items := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var date, statut, obs string
		rows.Scan(&id, &date, &statut, &obs)
		items = append(items, map[string]interface{}{
			"id": id, "date": date, "statut": statut, "observations": obs,
		})
	}
	httpx.JSONOK(w, http.StatusOK, items)
}

func UploadMessageAttachment(w http.ResponseWriter, r *http.Request) {
	err := r.ParseMultipartForm(5 << 20)
	if err != nil {
		httpx.Error(w, http.StatusBadRequest, "Fichier trop volumineux")
		return
	}

	file, header, err := r.FormFile("file")
	if err != nil {
		httpx.Error(w, http.StatusBadRequest, "Erreur de lecture")
		return
	}
	defer file.Close()

	uploadDir := "uploads/messages"
	os.MkdirAll(uploadDir, os.ModePerm)

	ext := filepath.Ext(header.Filename)
	filename := fmt.Sprintf("%d%s", time.Now().UnixNano(), ext)
	filepathFull := filepath.Join(uploadDir, filename)

	dst, err := os.Create(filepathFull)
	if err != nil {
		httpx.Error(w, http.StatusInternalServerError, "Erreur serveur")
		return
	}
	defer dst.Close()

	if _, err := io.Copy(dst, file); err != nil {
		httpx.Error(w, http.StatusInternalServerError, "Erreur ecriture")
		return
	}

	fileURL := "/api/uploads/messages/" + filename
	httpx.JSON(w, http.StatusOK, map[string]string{"url": fileURL})
}
