// Package repository isole l'accès SQL. Les méthodes mappent des lignes vers des
// types domaine (et inversement) ; elles ne portent AUCUNE règle métier — pas de
// décision « autorisé/refusé » ici, seulement de la lecture/écriture.
package repository

import "database/sql"

// Querier est le sous-ensemble commun à *sql.DB et *sql.Tx. En l'acceptant, un
// repo fonctionne indifféremment hors transaction (lecture simple) ou dans une
// transaction (écriture sous verrou FOR UPDATE) sans duplication de code. C'est
// la couche service qui décide d'ouvrir — ou non — une transaction.
type Querier interface {
	Query(query string, args ...any) (*sql.Rows, error)
	QueryRow(query string, args ...any) *sql.Row
	Exec(query string, args ...any) (sql.Result, error)
}
