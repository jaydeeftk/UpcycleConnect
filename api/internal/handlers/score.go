package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var scoreSvc = services.NewScoreService()

func GetScore(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	idUtilisateur := middleware.GetUserID(r)
	if middleware.GetRole(r) == "admin" {
		if pathID, err := idDepuisChemin(r.URL.Path, "/api/score/"); err == nil {
			idUtilisateur = pathID
		}
	}
	dto, err := scoreSvc.ScoreDuParticulier(idUtilisateur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func GetClassementScore(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	idUtilisateur := middleware.GetUserID(r)
	if idUtilisateur <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	dto, err := scoreSvc.Classement(idUtilisateur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}
