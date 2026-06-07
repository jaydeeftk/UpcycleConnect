package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
)

// SalarieGetForumSujets liste tous les sujets du forum pour la modération salarié.
// Réutilise la logique de listing admin (opération de lecture identique).
func SalarieGetForumSujets(w http.ResponseWriter, r *http.Request) {
	liste, err := forumSvc.AdminListerSujets()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// SalarieForumSujetAction supprime un sujet signalé/inapproprié (modération salarié).
func SalarieForumSujetAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/salaries/forum/sujets/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if err := forumSvc.SupprimerSujet(id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Sujet supprimé"})
}

// SalarieForumReponseAction supprime une réponse inappropriée (modération salarié).
func SalarieForumReponseAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/salaries/forum/reponses/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if err := forumSvc.SupprimerReponse(id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Réponse supprimée"})
}
