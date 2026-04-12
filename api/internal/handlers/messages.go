package handlers

import (
	"encoding/json"
	"net/http"
	"os"
	"path/filepath"
	"fmt"
	"io"
	"time"
)

// GetMessages (utilisé par l'admin pour voir les convs)
func GetMessages(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode([]interface{}{})
}

// GetUserMessages (utilisé par l'utilisateur pour son historique)
func GetUserMessages(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode([]interface{}{})
}

// GetHistorique (alias parfois utilisé dans le router)
func GetHistorique(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode([]interface{}{})
}

// SendMessage (envoi classique hors WebSocket si besoin)
func SendMessage(w http.ResponseWriter, r *http.Request) {
	w.WriteHeader(http.StatusOK)
}

// UploadMessageAttachment (notre nouvelle fonction pour le trombone)
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