// Package domain porte la logique métier PURE d'UpcycleConnect : machines à
// états, invariants et erreurs typées. Il ne dépend d'aucune I/O (ni SQL, ni
// HTTP) afin de rester testable unitairement et réutilisable par toutes les
// couches au-dessus (repository, service, handlers).
package domain

import "errors"

// Erreurs métier « catégories ». Une transition refusée renvoie l'une d'elles,
// que la couche httpx traduit en code HTTP. Une règle métier ne produit JAMAIS
// un 500 : elle produit un 4xx explicite.
//
//	ErrIntrouvable    -> 404  la ressource n'existe pas
//	ErrForbidden      -> 403  rôle/propriété insuffisants pour l'action
//	ErrInvalide       -> 422  entrée syntaxiquement correcte mais métier-invalide
//	ErrEtatInvalide   -> 409  transition interdite depuis l'état courant
//	ErrDeja           -> 409  action déjà effectuée (idempotence violée)
//	ErrComplet        -> 409  capacité atteinte
//	ErrConflit        -> 409  conflit générique (concurrence, précondition)
//	ErrPaiementRequis -> 402  un paiement est nécessaire avant l'action
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

// businessError associe une catégorie (qui détermine le code HTTP) à un message
// sûr à présenter à l'utilisateur final. errors.Is(err, ErrXxx) fonctionne via
// Unwrap, ce qui permet à httpx de router sur la catégorie sans connaître le
// message précis.
type businessError struct {
	kind    error
	message string
}

func (e *businessError) Error() string { return e.message }
func (e *businessError) Unwrap() error { return e.kind }

func wrap(kind error, message string) error {
	return &businessError{kind: kind, message: message}
}

// Constructeurs : ils portent un message métier précis tout en restant
// classables par errors.Is sur la catégorie.
func Introuvable(msg string) error    { return wrap(ErrIntrouvable, msg) }
func Forbidden(msg string) error      { return wrap(ErrForbidden, msg) }
func Invalide(msg string) error       { return wrap(ErrInvalide, msg) }
func EtatInvalide(msg string) error   { return wrap(ErrEtatInvalide, msg) }
func Deja(msg string) error           { return wrap(ErrDeja, msg) }
func Complet(msg string) error        { return wrap(ErrComplet, msg) }
func Conflit(msg string) error        { return wrap(ErrConflit, msg) }
func PaiementRequis(msg string) error { return wrap(ErrPaiementRequis, msg) }
