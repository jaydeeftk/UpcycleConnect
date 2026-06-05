package services

import (
	"context"
	"database/sql"

	"upcycleconnect/internal/database"
)

// withTx exécute fn dans une transaction : commit si fn réussit, rollback sinon.
// C'est le garant de l'invariant « pas d'effet de bord partiel » — une opération
// multi-étapes est tout-ou-rien. Le rollback sur panique évite de laisser une
// transaction ouverte si un appelant panique au milieu.
func withTx(fn func(*sql.Tx) error) error {
	return withTxIso(0, fn)
}

// withTxIso est identique à withTx mais permet d'imposer un niveau d'isolation.
//
// READ COMMITTED est requis pour les décisions de CAPACITÉ dérivée (occupation =
// COUNT d'objets en_stock par box) : sous le REPEATABLE READ par défaut de MySQL,
// la vue de lecture est figée à la première lecture de la transaction, si bien
// qu'un SELECT ... FOR UPDATE qui se débloque après le COMMIT d'une transaction
// concurrente verrait quand même un COUNT périmé (snapshot) et pourrait
// sur-remplir la box. En READ COMMITTED chaque énoncé prend un instantané frais :
// après l'attente du verrou FOR UPDATE, le COUNT reflète l'insertion committée de
// la transaction concurrente -> deux validations simultanées ne peuvent pas
// dépasser la capacité. iso == 0 conserve le niveau par défaut.
func withTxIso(iso sql.IsolationLevel, fn func(*sql.Tx) error) (err error) {
	tx, err := database.DB.BeginTx(context.Background(), &sql.TxOptions{Isolation: iso})
	if err != nil {
		return err
	}
	defer func() {
		if p := recover(); p != nil {
			_ = tx.Rollback()
			panic(p)
		}
	}()
	if err = fn(tx); err != nil {
		_ = tx.Rollback()
		return err
	}
	return tx.Commit()
}
