package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
)

// CreerDemandeRemboursement : un particulier demande le remboursement de SON paiement.
func CreerDemandeRemboursement(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		IdPaiement int    `json:"id_paiement"`
		Motif      string `json:"motif"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || body.IdPaiement == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := facturationSvc.CreerDemandeRemboursement(middleware.GetUserID(r), body.IdPaiement, body.Motif)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Demande de remboursement enregistrée"})
}

// ListerDemandesRemboursement : salarié/admin — liste des demandes de remboursement.
func ListerDemandesRemboursement(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerDemandesRemboursement()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// RemboursementAction : salarié/admin — approuve/refuse une demande, ou rembourse
// directement (/direct). Approbation et refund direct passent par le même seam.
func RemboursementAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	path := strings.TrimPrefix(r.URL.Path, "/api/salaries/remboursements/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if parts[0] == "direct" {
		var body struct {
			IdPaiement int    `json:"id_paiement"`
			Motif      string `json:"motif"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil || body.IdPaiement == 0 {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := facturationSvc.RefundDirect(body.IdPaiement, body.Motif); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Remboursement effectué"})
		return
	}

	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Action manquante")
		return
	}
	id, err := strconv.Atoi(parts[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	switch parts[1] {
	case "approuver":
		if err := facturationSvc.ExecuterRemboursement(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Remboursement effectué"})
	case "refuser":
		if err := facturationSvc.RefuserDemandeRemboursement(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande refusée"})
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
	}
}
