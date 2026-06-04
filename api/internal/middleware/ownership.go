package middleware

import (
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/httpx"

	"github.com/golang-jwt/jwt/v5"
)

// GetUserID retourne l'Id_Utilisateurs authentifié, lu dans le claim "sub" du JWT.
// Les nombres JSON arrivent en float64 dans MapClaims. Renvoie 0 si absent.
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
	claims, _ := r.Context().Value(ClaimsKey).(jwt.MapClaims)
	role, _ := claims["role"].(string)
	return role == "admin"
}

// OwnerFromPath exige un token valide et vérifie que l'identifiant en fin d'URL
// correspond à l'utilisateur authentifié. Les admins gardent un accès transverse.
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
