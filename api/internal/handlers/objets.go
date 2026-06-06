package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

var recuperationSvc = services.NewRecuperationService()

func ProfessionnelObjetsHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}

	if r.URL.Query().Get("filtre") == "mes-reservations" {
		liste, err := recuperationSvc.MesReservations(profID)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)
		return
	}

	idConteneur, _ := strconv.Atoi(r.URL.Query().Get("conteneur"))
	liste, err := recuperationSvc.ListerDisponibles(profID, idConteneur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func ProfessionnelObjetAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/professionnels/objets/")
	if len(segs) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Action manquante")
		return
	}
	idObjet, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	var msg string
	switch segs[1] {
	case "reserver":
		err, msg = recuperationSvc.Reserver(profID, idObjet), "Objet réservé"
	case "recuperer":
		err, msg = recuperationSvc.Recuperer(profID, idObjet), "Objet récupéré"
	case "annuler":
		err, msg = recuperationSvc.AnnulerReservation(profID, idObjet), "Réservation annulée"
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
		return
	}
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": msg})
}

func ProfessionnelRecupererParCode(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	var body struct {
		Code string `json:"code"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	if err := recuperationSvc.RecupererParCodeBarre(profID, body.Code); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Objet récupéré"})
}
