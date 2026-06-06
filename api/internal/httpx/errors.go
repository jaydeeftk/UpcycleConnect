package httpx

import (
	"errors"
	"net/http"

	"upcycleconnect/internal/domain"
)

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
