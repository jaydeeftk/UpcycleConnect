package services

import (
	"context"
	"database/sql"

	"upcycleconnect/internal/database"
)

func withTx(fn func(*sql.Tx) error) error {
	return withTxIso(0, fn)
}

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
