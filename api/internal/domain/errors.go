package domain

import "errors"

var (
	ErrIntrouvable    = errors.New("ressource introuvable")
	ErrForbidden      = errors.New("action non autorisée")
	ErrInvalide       = errors.New("donnée invalide")
	ErrEtatInvalide   = errors.New("transition interdite depuis l'état courant")
	ErrDeja           = errors.New("action déjà effectuée")
	ErrComplet        = errors.New("capacité atteinte")
	ErrConflit        = errors.New("conflit d'état")
	ErrPaiementRequis = errors.New("paiement requis")
)

type businessError struct {
	kind    error
	message string
}

func (e *businessError) Error() string { return e.message }
func (e *businessError) Unwrap() error { return e.kind }

func wrap(kind error, message string) error {
	return &businessError{kind: kind, message: message}
}

func Introuvable(msg string) error    { return wrap(ErrIntrouvable, msg) }
func Forbidden(msg string) error      { return wrap(ErrForbidden, msg) }
func Invalide(msg string) error       { return wrap(ErrInvalide, msg) }
func EtatInvalide(msg string) error   { return wrap(ErrEtatInvalide, msg) }
func Deja(msg string) error           { return wrap(ErrDeja, msg) }
func Complet(msg string) error        { return wrap(ErrComplet, msg) }
func Conflit(msg string) error        { return wrap(ErrConflit, msg) }
func PaiementRequis(msg string) error { return wrap(ErrPaiementRequis, msg) }
