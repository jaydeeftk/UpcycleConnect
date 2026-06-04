package httpx

import (
	"errors"
	"net/http"

	"upcycleconnect/internal/domain"
)

// WriteError traduit une erreur métier typée (domain.*) en réponse HTTP. La
// catégorie de l'erreur détermine le statut ; son message (sûr) est renvoyé tel
// quel au client. Toute erreur NON métier retombe sur un 500 générique journalisé
// (CWE-209 : aucun détail interne fuité). C'est l'unique pont service -> HTTP,
// ce qui garantit qu'aucune règle métier ne produit un 500.
func WriteError(w http.ResponseWriter, err error) {
	switch {
	case err == nil:
		return
	case errors.Is(err, domain.ErrIntrouvable):
		JSONError(w, http.StatusNotFound, err.Error())
	case errors.Is(err, domain.ErrForbidden):
		JSONError(w, http.StatusForbidden, err.Error())
	case errors.Is(err, domain.ErrInvalide):
		JSONError(w, http.StatusUnprocessableEntity, err.Error())
	case errors.Is(err, domain.ErrPaiementRequis):
		JSONError(w, http.StatusPaymentRequired, err.Error())
	case errors.Is(err, domain.ErrEtatInvalide),
		errors.Is(err, domain.ErrDeja),
		errors.Is(err, domain.ErrComplet),
		errors.Is(err, domain.ErrConflit):
		JSONError(w, http.StatusConflict, err.Error())
	default:
		JSONServerError(w, err)
	}
}
