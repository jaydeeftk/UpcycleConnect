package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var conteneurSvc = services.NewConteneurService()

func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	liste, err := conteneurSvc.ListerConteneursDisponibles()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func CreateDemandeConteneur(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		TypeObjet   string  `json:"type_objet"`
		Description string  `json:"description"`
		EtatUsure   string  `json:"etat_usure"`
		IdConteneur int     `json:"conteneur_id"`
		DateDepot   string  `json:"date_depot"`
		Destination string  `json:"destination"`
		PrixVente   float64 `json:"prix_vente"`
		PhotoUrl    string  `json:"photo_url"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := conteneurSvc.CreerDemande(middleware.GetUserID(r), services.CreationDepotInput{
		TypeObjet: body.TypeObjet, Description: body.Description, EtatUsure: body.EtatUsure,
		IdConteneur: body.IdConteneur, DateDepot: body.DateDepot, Destination: body.Destination,
		PrixVente: body.PrixVente, PhotoUrl: body.PhotoUrl,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Demande envoyée, en attente de validation",
	})
}

func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	idUtilisateur := middleware.GetUserID(r)
	if middleware.GetRole(r) == "admin" {
		if pid, err := idDepuisChemin(r.URL.Path, "/api/conteneurs/user/"); err == nil {
			idUtilisateur = pid
		}
	}
	liste, err := conteneurSvc.DemandesDeLUtilisateur(idUtilisateur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminDemandeConteneurAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/conteneurs/demandes/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	action := parts[len(parts)-1]

	switch action {
	case "accept":
		code, err := conteneurSvc.ValiderDemande(id)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande acceptée", "code_acces": code})
	case "refuse":
		if err := conteneurSvc.RefuserDemande(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande refusée"})
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
	}
}
