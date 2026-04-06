package handlers

import (
	"encoding/json"
	"net/http"
	"os"

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
	var body struct {
		NomSite     string `json:"nom_site"`
		Email       string `json:"email"`
		Description string `json:"description"`
		Langue      string `json:"langue"`
		Fuseau      string `json:"fuseau"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Paramètres mis à jour"})
}
