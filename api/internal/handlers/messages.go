package handlers

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
)

func GetUserMessages(w http.ResponseWriter, r *http.Request) {
	userID := strings.TrimPrefix(r.URL.Path, "/api/messages/user/")
	userID = strings.TrimSuffix(userID, "/")
	if userID == "" {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}

	rows, err := database.DB.Query(`
		SELECT
			m.Id_Messages,
			COALESCE(m.Contenu, '') AS contenu,
			COALESCE(DATE_FORMAT(m.Date_envoi, '%Y-%m-%d %H:%i:%s'), '') AS date_envoi,
			CASE
				WHEN m.Id_Particuliers IS NULL AND m.Id_Utilisateurs IS NOT NULL THEN 1
				ELSE 0
			END AS is_admin
		FROM Messages m
		LEFT JOIN Particuliers p ON p.Id_Particuliers = m.Id_Particuliers
		WHERE m.Id_Utilisateurs = ? OR p.Id_Utilisateurs = ?
		ORDER BY m.Id_Messages ASC`,
		userID, userID,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()

	msgs := []map[string]interface{}{}
	for rows.Next() {
		var id, isAdmin int
		var contenu, dateEnvoi string
		rows.Scan(&id, &contenu, &dateEnvoi, &isAdmin)
		msgs = append(msgs, map[string]interface{}{
			"id":         id,
			"contenu":    contenu,
			"date_envoi": dateEnvoi,
			"is_admin":   isAdmin == 1,
		})
	}
	httpx.JSONOK(w, http.StatusOK, msgs)
}

func CreateUserMessage(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	claims := middleware.GetClaims(r)
	if claims == nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Non authentifié")
		return
	}
	userID := int(claims["sub"].(float64))

	var body struct {
		Contenu string `json:"contenu"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || strings.TrimSpace(body.Contenu) == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Contenu requis")
		return
	}

	var idPart int
	err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", userID).Scan(&idPart)
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Profil particulier introuvable")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Messages (Contenu, Date_envoi, Id_Particuliers, Id_Utilisateurs) VALUES (?, NOW(), ?, NULL)",
		body.Contenu, idPart,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur lors de l'envoi")
		return
	}
	newID, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID, "message": "Message envoyé"})
}

func GetHistorique(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, []interface{}{})
}

func UploadMessageAttachment(w http.ResponseWriter, r *http.Request) {
	if err := r.ParseMultipartForm(5 << 20); err != nil {
		http.Error(w, "Fichier trop volumineux", http.StatusBadRequest)
		return
	}

	file, header, err := r.FormFile("file")
	if err != nil {
		http.Error(w, "Erreur de lecture", http.StatusBadRequest)
		return
	}
	defer file.Close()

	allowed := map[string]bool{".jpg": true, ".jpeg": true, ".png": true, ".gif": true, ".webp": true}
	ext := strings.ToLower(filepath.Ext(header.Filename))
	if !allowed[ext] {
		http.Error(w, "Type de fichier non autorisé", http.StatusBadRequest)
		return
	}

	uploadDir := "uploads/messages"
	os.MkdirAll(uploadDir, os.ModePerm)

	filename := fmt.Sprintf("%d%s", time.Now().UnixNano(), ext)
	filepathFull := filepath.Join(uploadDir, filename)

	dst, err := os.Create(filepathFull)
	if err != nil {
		http.Error(w, "Erreur serveur", http.StatusInternalServerError)
		return
	}
	defer dst.Close()

	if _, err := io.Copy(dst, file); err != nil {
		http.Error(w, "Erreur écriture", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{"url": "/api/uploads/messages/" + filename})
}
