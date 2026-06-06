package middleware

import (
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/httpx"

	"github.com/golang-jwt/jwt/v5"
)

func GetUserID(r *http.Request) int {
	claims, _ := r.Context().Value(ClaimsKey).(jwt.MapClaims)
	if claims == nil {
		return 0
	}
	switch v := claims["sub"].(type) {
	case float64:
		return int(v)
	case string:
		id, _ := strconv.Atoi(v)
		return id
	}
	return 0
}

func isAdmin(r *http.Request) bool {
	return GetRole(r) == "admin"
}

func GetRole(r *http.Request) string {
	claims, _ := r.Context().Value(ClaimsKey).(jwt.MapClaims)
	role, _ := claims["role"].(string)
	return role
}

func OwnerFromPath(next http.HandlerFunc) http.HandlerFunc {
	return JWTAuth(func(w http.ResponseWriter, r *http.Request) {
		parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
		pathID, err := strconv.Atoi(parts[len(parts)-1])
		if err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
			return
		}
		if !isAdmin(r) && pathID != GetUserID(r) {
			httpx.JSONError(w, http.StatusForbidden, "Accès refusé")
			return
		}
		next(w, r)
	})
}
