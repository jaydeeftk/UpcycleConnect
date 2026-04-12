package middleware

import (
	"context"
	"net/http"
	"os"
	"strings"

	"upcycleconnect/internal/httpx"

	"github.com/golang-jwt/jwt/v5"
)

type contextKey string

const ClaimsKey contextKey = "claims"

func JWTAuth(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		header := r.Header.Get("Authorization")
		if !strings.HasPrefix(header, "Bearer ") {
			httpx.JSONError(w, http.StatusUnauthorized, "Token manquant")
			return
		}

		tokenStr := strings.TrimPrefix(header, "Bearer ")
		secret := []byte(os.Getenv("JWT_SECRET"))

		token, err := jwt.Parse(tokenStr, func(t *jwt.Token) (interface{}, error) {
			return secret, nil
		})

		if err != nil || !token.Valid {
			httpx.JSONError(w, http.StatusUnauthorized, "Token invalide")
			return
		}

		claims, ok := token.Claims.(jwt.MapClaims)
		if !ok {
			httpx.JSONError(w, http.StatusUnauthorized, "Token malformé")
			return
		}

		ctx := context.WithValue(r.Context(), ClaimsKey, claims)
		next(w, r.WithContext(ctx))
	}
}

func AdminOnly(next http.HandlerFunc) http.HandlerFunc {
	return JWTAuth(func(w http.ResponseWriter, r *http.Request) {
		claims := r.Context().Value(ClaimsKey).(jwt.MapClaims)
		if claims["role"] != "admin" {
			httpx.JSONError(w, http.StatusForbidden, "Accès refusé")
			return
		}
		next(w, r)
	})
}

func SalarieOnly(next http.HandlerFunc) http.HandlerFunc {
	return JWTAuth(func(w http.ResponseWriter, r *http.Request) {
		claims := r.Context().Value(ClaimsKey).(jwt.MapClaims)
		role, _ := claims["role"].(string)
		if role != "salarie" && role != "admin" {
			httpx.JSONError(w, http.StatusForbidden, "Accès réservé aux salariés")
			return
		}
		next(w, r)
	})
}

func ProfessionnelOnly(next http.HandlerFunc) http.HandlerFunc {
	return JWTAuth(func(w http.ResponseWriter, r *http.Request) {
		claims := r.Context().Value(ClaimsKey).(jwt.MapClaims)
		role, _ := claims["role"].(string)
		if role != "professionnel" && role != "admin" {
			httpx.JSONError(w, http.StatusForbidden, "Accès réservé aux professionnels")
			return
		}
		next(w, r)
	})
}
