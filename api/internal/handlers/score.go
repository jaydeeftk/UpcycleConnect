package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

// scoreSvc — cas d'usage de la gamification. Sans état, partagé.
var scoreSvc = services.NewScoreService()

// GetScore : vue de gamification (score écologique dérivé de l'activité +
// historique des points + badges dérivés du score) de l'utilisateur visé par
// l'URL.
//
// IDENTITÉ DEPUIS LE JWT, JAMAIS L'URL : un utilisateur non-admin ne peut consulter
// QUE son propre score (idUtilisateur = sub du JWT, l'URL est ignorée). Un admin
// peut cibler un autre utilisateur via l'URL. OwnerFromPath garde déjà la route,
// mais on ne se repose pas sur le segment d'URL pour choisir la cible — sinon une
// URL du type /api/score/{victime}/{monId} contournerait la garde (IDOR).
// LECTURE PURE : ce GET n'écrit rien (plus de rafraîchissement de cache).
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
