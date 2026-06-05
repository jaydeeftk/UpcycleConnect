package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

// conteneurSvc — cas d'usage du vertical Demande / Conteneur / Box. Sans état, partagé.
var conteneurSvc = services.NewConteneurService()

// GetConteneurs : liste publique des points de dépôt (conteneurs disponibles).
func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	liste, err := conteneurSvc.ListerConteneursDisponibles()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// CreateDemandeConteneur : dépôt d'une demande. Identité = JWT (sub) ; le champ
// user_id éventuel du corps est ignoré. Validation des invariants et insertion
// vivent dans le service.
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

// GetDemandesConteneurUser : file privée d'un utilisateur. La route est gardée par
// OwnerFromPath (l'id en fin d'URL doit être celui de l'appelant, sauf admin) ;
// l'identifiant sert donc à cibler la file, l'autorisation étant déjà tranchée.
func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	idUtilisateur, err := idDepuisChemin(r.URL.Path, "/api/conteneurs/user/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	liste, err := conteneurSvc.DemandesDeLUtilisateur(idUtilisateur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// AdminDemandeConteneurAction : modération depuis la page « Conteneurs & Box ».
// accept -> valider (réserve une box + code), refuse -> refuser. Toute autre action
// est rejetée (fini la suppression silencieuse sur action inconnue).
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
