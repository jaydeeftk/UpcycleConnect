package handlers

import (
	"net/http"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

// scoreSvc — cas d'usage de la gamification. Sans état, partagé.
var scoreSvc = services.NewScoreService()

// GetScore : vue de gamification (score écologique dérivé de l'activité +
// historique des points + badges dérivés du score) de l'utilisateur visé par
// l'URL.
//
// Autorisation portée par le middleware OwnerFromPath : seul le propriétaire (ou
// un admin) atteint ce handler, l'identifiant du chemin ayant été comparé au JWT.
// LECTURE PURE : contrairement à l'ancienne version, ce GET n'écrit rien (il ne
// rafraîchit plus un cache Particuliers.Score qui n'était jamais relu).
func GetScore(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	idUtilisateur, err := idDepuisChemin(r.URL.Path, "/api/score/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	dto, err := scoreSvc.ScoreDuParticulier(idUtilisateur)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}
