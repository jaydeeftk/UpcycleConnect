package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

var projetSvc = services.NewProjetService()

func ProfessionnelProjetsHandler(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	switch r.Method {
	case http.MethodGet:
		liste, err := projetSvc.ListerProjets(profID)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)
	case http.MethodPost:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			DateDebut   string `json:"date_debut"`
			Statut      string `json:"statut"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		id, err := projetSvc.CreerProjet(profID, services.ProjetInput{
			Titre: body.Titre, Description: body.Description, DateDebut: body.DateDebut, Statut: body.Statut,
		})
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Projet créé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ProfessionnelProjetAction(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/professionnels/projets/")
	if len(segs) == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant manquant")
		return
	}
	idProjet, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	if len(segs) >= 2 {
		if r.Method != http.MethodPost {
			httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
			return
		}
		var msg string
		switch segs[1] {
		case "suspendre":
			err, msg = projetSvc.Suspendre(profID, idProjet), "Projet mis en pause"
		case "reprendre":
			err, msg = projetSvc.Reprendre(profID, idProjet), "Projet repris"
		case "terminer":
			err, msg = projetSvc.Terminer(profID, idProjet), "Projet terminé"
		case "rouvrir":
			err, msg = projetSvc.Rouvrir(profID, idProjet), "Projet rouvert"
		default:
			httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
			return
		}
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": msg})
		return
	}

	switch r.Method {
	case http.MethodGet:
		liste, err := projetSvc.ListerEtapes(profID, idProjet)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)
	case http.MethodPut:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := projetSvc.ModifierProjet(profID, idProjet, services.ProjetContenuInput{Titre: body.Titre, Description: body.Description}); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Projet mis à jour"})
	case http.MethodDelete:
		if err := projetSvc.SupprimerProjet(profID, idProjet); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Projet supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ProfessionnelEtapeAction(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/professionnels/projets/")

	switch r.Method {
	case http.MethodPost:
		if len(segs) != 2 {
			httpx.JSONError(w, http.StatusBadRequest, "Chemin d'étape invalide")
			return
		}
		idProjet, err := strconv.Atoi(segs[0])
		if err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
			return
		}
		var body struct {
			Nom         string `json:"nom"`
			Description string `json:"description"`
			Visuel      string `json:"visuel"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		id, err := projetSvc.AjouterEtape(profID, idProjet, services.EtapeInput{Nom: body.Nom, Description: body.Description, Visuel: body.Visuel})
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Étape ajoutée"})
	case http.MethodDelete:
		if len(segs) != 3 {
			httpx.JSONError(w, http.StatusBadRequest, "Identifiant d'étape manquant")
			return
		}
		idEtape, err := strconv.Atoi(segs[2])
		if err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
			return
		}
		if err := projetSvc.SupprimerEtape(profID, idEtape); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Étape supprimée"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
