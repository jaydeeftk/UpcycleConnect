package handlers

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"time"
	"upcycleconnect/internal/database"
)

func GetMessages(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	recipientID := r.URL.Query().Get("recipient_id")
	if recipientID == "" {
		json.NewEncoder(w).Encode([]interface{}{})
		return
	}

	rows, err := database.DB.Query(`
		SELECT id, sender_id, recipient_id, content, created_at 
		FROM messages 
		WHERE (sender_id = 1 AND recipient_id = ?) 
		   OR (sender_id = ? AND recipient_id = 1) 
		ORDER BY created_at ASC`, recipientID, recipientID)

	if err != nil {
		json.NewEncoder(w).Encode([]interface{}{})
		return
	}
	defer rows.Close()

	var msgs []map[string]interface{}
	for rows.Next() {
		var id, sID, rID int
		var content string
		var createdAt time.Time
		rows.Scan(&id, &sID, &rID, &content, &createdAt)
		msgs = append(msgs, map[string]interface{}{
			"id": id, "sender_id": sID, "recipient_id": rID,
			"content": content, "created_at": createdAt,
		})
	}
	json.NewEncoder(w).Encode(msgs)
}

func GetUserMessages(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	userID := r.URL.Query().Get("user_id")
	if userID == "" {
		json.NewEncoder(w).Encode([]interface{}{})
		return
	}

	rows, err := database.DB.Query(`
		SELECT id, sender_id, recipient_id, content, created_at 
		FROM messages 
		WHERE sender_id = ? OR recipient_id = ? 
		ORDER BY created_at ASC`, userID, userID)

	if err != nil {
		json.NewEncoder(w).Encode([]interface{}{})
		return
	}
	defer rows.Close()

	var msgs []map[string]interface{}
	for rows.Next() {
		var id, sID, rID int
		var content string
		var createdAt time.Time
		rows.Scan(&id, &sID, &rID, &content, &createdAt)
		msgs = append(msgs, map[string]interface{}{
			"id": id, "sender_id": sID, "recipient_id": rID,
			"content": content, "created_at": createdAt,
		})
	}
	json.NewEncoder(w).Encode(msgs)
}

func GetHistorique(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode([]interface{}{})
}

func SendMessage(w http.ResponseWriter, r *http.Request) {
	w.WriteHeader(http.StatusOK)
}

func UploadMessageAttachment(w http.ResponseWriter, r *http.Request) {
	err := r.ParseMultipartForm(5 << 20)
	if err != nil {
		http.Error(w, "Fichier trop volumineux", http.StatusBadRequest)
		return
	}

	file, header, err := r.FormFile("file")
	if err != nil {
		http.Error(w, "Erreur de lecture", http.StatusBadRequest)
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
		http.Error(w, "Erreur serveur", http.StatusInternalServerError)
		return
	}
	defer dst.Close()

	if _, err := io.Copy(dst, file); err != nil {
		http.Error(w, "Erreur ecriture", http.StatusInternalServerError)
		return
	}

	fileURL := "/api/uploads/messages/" + filename
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{"url": fileURL})
}
