package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
)

func AdminGetFactures(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerFactures()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

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
