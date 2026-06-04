package services

import (
	"database/sql"

	"upcycleconnect/internal/database"
)

// withTx exécute fn dans une transaction : commit si fn réussit, rollback sinon.
// C'est le garant de l'invariant « pas d'effet de bord partiel » — une opération
// multi-étapes est tout-ou-rien. Le rollback sur panique évite de laisser une
// transaction ouverte si un appelant panique au milieu.
func withTx(fn func(*sql.Tx) error) (err error) {
	tx, err := database.DB.Begin()
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
