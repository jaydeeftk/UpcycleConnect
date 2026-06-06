package database

import (
	"database/sql"
	"log"
	"os"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

var DB *sql.DB

func Connect() {
	dsn := os.Getenv("DB_DSN")
	if dsn == "" {
		log.Fatal("DB_DSN n'est pas défini")
	}

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}

	const maxAttempts = 30
	for attempt := 1; ; attempt++ {
		if err = db.Ping(); err == nil {
			break
		}
		if attempt >= maxAttempts {
			log.Fatalf("MySQL injoignable après %d tentatives : %v", maxAttempts, err)
		}
		log.Printf("MySQL pas encore prêt (tentative %d/%d), nouvel essai dans 2s…", attempt, maxAttempts)
		time.Sleep(2 * time.Second)
	}

	DB = db
}
