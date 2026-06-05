package handlers

import (
	"net/http"
	"strconv"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

// recuperationSvc — cas d'usage de la récupération pro. Sans état, partagé.
var recuperationSvc = services.NewRecuperationService()

// ProfessionnelObjetsHandler : catalogue d'objets pour le professionnel.
//   - ?filtre=mes-reservations -> ses objets réservés / récupérés ;
//   - sinon (défaut)           -> objets disponibles (en_stock), ?conteneur=<id>
//     restreint à un conteneur.
//
// L'identité (Id_Professionnels) vient du JWT via getProfessionnelFromContext —
// jamais d'un paramètre. Un admin (sans profil pro) reçoit 403.
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

	idConteneur, _ := strconv.Atoi(r.URL.Query().Get("conteneur")) // 0 si absent => tous
	liste, err := recuperationSvc.ListerDisponibles(profID, idConteneur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// ProfessionnelObjetAction : transition de récupération sur un objet.
// POST /api/professionnels/objets/{id}/{reserver|recuperer|annuler}. Identité du
// JWT ; le service garde l'état + la propriété (403/404/409 selon le cas).
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
