package handlers

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"time"
)

// GetMessages : Récupère l'historique (Route: /api/messages)
func GetMessages(w http.ResponseWriter, r *http.Request) {
	// Ici on pourrait ajouter la logique DB, pour l'instant on renvoie un tableau vide 
    // pour que l'API compile et que le front ne crash pas.
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode([]interface{}{})
}

// UploadMessageAttachment : Gère l'upload d'images (Route: /api/messages/upload)
func UploadMessageAttachment(w http.ResponseWriter, r *http.Request) {
	err := r.ParseMultipartForm(5 << 20) // 5MB max
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