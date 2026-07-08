// Application RGPD — service isolé de gestion des données personnelles
// des adhérents transités par la Suisse. Joignable uniquement à travers
// le proxy applicatif du bastion Teleport (RGPD remote access).
package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"log"
	"net"
	"net/http"
	"os"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

var db *sql.DB

type ctxKey string

const ctxIdentity ctxKey = "identity"

func main() {
	dsn := getenv("RGPD_DB_DSN", "rgpd:rgpd@tcp(rgpd-db:3306)/rgpd?charset=utf8mb4")
	addr := getenv("RGPD_LISTEN_ADDR", ":8090")

	var err error
	db, err = sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("ouverture base: %v", err)
	}
	db.SetConnMaxLifetime(2 * time.Minute)
	db.SetMaxOpenConns(10)
	db.SetMaxIdleConns(5)

	for i := 0; i < 30; i++ {
		if err = db.Ping(); err == nil {
			break
		}
		log.Printf("base indisponible, nouvelle tentative (%d/30)...", i+1)
		time.Sleep(2 * time.Second)
	}
	if err != nil {
		log.Fatalf("base injoignable: %v", err)
	}

	mux := http.NewServeMux()
	mux.HandleFunc("GET /healthz", handleHealthz)
	mux.Handle("GET /{$}", auth(http.HandlerFunc(handleDashboard)))
	mux.Handle("GET /api/adherents", auth(http.HandlerFunc(handleListAdherents)))
	mux.Handle("POST /api/adherents", auth(http.HandlerFunc(handleCreateAdherent)))
	mux.Handle("GET /api/adherents/{id}", auth(http.HandlerFunc(handleGetAdherent)))
	mux.Handle("PUT /api/adherents/{id}", auth(http.HandlerFunc(handleUpdateAdherent)))
	mux.Handle("DELETE /api/adherents/{id}", auth(http.HandlerFunc(handleEraseAdherent)))
	mux.Handle("GET /api/adherents/{id}/export", auth(http.HandlerFunc(handleExportAdherent)))
	mux.Handle("POST /api/adherents/{id}/consentements", auth(http.HandlerFunc(handleAddConsent)))
	mux.Handle("POST /api/adherents/{id}/demandes", auth(http.HandlerFunc(handleCreateDemande)))
	mux.Handle("GET /api/registre", auth(http.HandlerFunc(handleRegistre)))
	mux.Handle("GET /api/demandes", auth(http.HandlerFunc(handleListDemandes)))
	mux.Handle("GET /api/journal", auth(http.HandlerFunc(handleJournal)))

	srv := &http.Server{
		Addr:              addr,
		Handler:           mux,
		ReadHeaderTimeout: 10 * time.Second,
	}
	log.Printf("Application RGPD à l'écoute sur %s", addr)
	log.Fatal(srv.ListenAndServe())
}

func getenv(k, def string) string {
	if v := os.Getenv(k); v != "" {
		return v
	}
	return def
}

func handleHealthz(w http.ResponseWriter, r *http.Request) {
	if err := db.Ping(); err != nil {
		http.Error(w, "db down", http.StatusServiceUnavailable)
		return
	}
	w.WriteHeader(http.StatusOK)
	_, _ = w.Write([]byte("ok"))
}

// auth exige un jeton porteur (le service n'est exposé qu'au travers du
// proxy du bastion) et récupère l'identité propagée par Teleport afin de
// l'inscrire dans le journal d'accès.
func auth(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		token := os.Getenv("RGPD_API_TOKEN")
		if token == "" {
			jsonError(w, http.StatusInternalServerError, "service mal configuré")
			return
		}
		if r.Header.Get("Authorization") != "Bearer "+token {
			jsonError(w, http.StatusUnauthorized, "accès refusé")
			return
		}
		ctx := context.WithValue(r.Context(), ctxIdentity, teleportIdentity(r))
		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

func identityOf(r *http.Request) string {
	if v, ok := r.Context().Value(ctxIdentity).(string); ok {
		return v
	}
	return "inconnu"
}

func clientIP(r *http.Request) string {
	if xff := r.Header.Get("X-Forwarded-For"); xff != "" {
		return strings.TrimSpace(strings.Split(xff, ",")[0])
	}
	if host, _, err := net.SplitHostPort(r.RemoteAddr); err == nil {
		return host
	}
	return r.RemoteAddr
}

// audit inscrit une entrée dans le journal d'accès (art. 5.2 & 32 RGPD).
func audit(r *http.Request, action, cibleType, cibleID, details string) {
	id := identityOf(r)
	_, _ = db.Exec(
		`INSERT INTO Journal_Acces (Acteur, Identite_Teleport, Action, Cible_Type, Cible_Id, Details, Adresse_IP)
		 VALUES (?, ?, ?, ?, ?, ?, ?)`,
		id, id, action, cibleType, cibleID, details, clientIP(r),
	)
}

func jsonOK(w http.ResponseWriter, status int, data interface{}) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(data)
}

func jsonError(w http.ResponseWriter, status int, msg string) {
	jsonOK(w, status, map[string]string{"error": msg})
}
