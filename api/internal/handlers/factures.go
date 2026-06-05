package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
)

// AdminGetFactures : liste admin des factures (montants HT/TVA/TTC déjà cohérents
// en base — la validation d'invariant se fait à l'émission, pas à la lecture).
func AdminGetFactures(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerFactures()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// AdminGetFacture : détail d'une facture par identifiant (404 si absente).
func AdminGetFacture(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/factures/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	f, err := facturationSvc.ObtenirFacture(id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, f)
}
