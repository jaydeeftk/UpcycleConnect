package handlers

import (
	"encoding/json"
	"net/http"
	"os"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetParametres(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"nom_site":    "UpcycleConnect",
		"email":       os.Getenv("CONTACT_EMAIL"),
		"description": "Plateforme de mise en relation pour l'upcycling",
		"langue":      "Français",
		"fuseau":      "Europe/Paris",
	})
}

func AdminUpdateParametres(w http.ResponseWriter, r *http.Request) {
	var body map[string]string

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	for cle, valeur := range body {
		_, err := database.DB.Exec("UPDATE Parametres SET Valeur = ? WHERE Cle = ?", valeur, cle)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, "Erreur lors de la mise à jour de "+cle)
			return
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Paramètres mis à jour"})
}
