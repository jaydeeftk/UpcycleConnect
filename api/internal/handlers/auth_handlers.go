package handlers

import (
	"encoding/json"
	"net/http"
	"upcycleconnect/internal/database"
)

func VerifyAccount(w http.ResponseWriter, r *http.Request) {
	token := r.URL.Query().Get("token")
	if token == "" {
		http.Error(w, "Token manquant", http.StatusBadRequest)
		return
	}

	query := "UPDATE Utilisateurs SET is_verified = 1, verification_token = NULL WHERE verification_token = ?"
	result, err := database.DB.Exec(query, token)
	if err != nil {
		http.Error(w, "Erreur serveur lors de la validation", http.StatusInternalServerError)
		return
	}

	rowsAffected, _ := result.RowsAffected()
	if rowsAffected == 0 {
		http.Error(w, "Lien de validation invalide ou expiré", http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"status":  "success",
		"message": "Compte activé ! Vous pouvez maintenant vous connecter.",
	})
}
