package main

import (
	"encoding/base64"
	"encoding/json"
	"net/http"
	"strings"
)

// Intégration bastion Teleport.
//
// L'application n'est joignable qu'à travers le proxy du bastion. Pour
// chaque requête vers une application protégée, Teleport authentifie
// l'utilisateur puis signe et injecte un jeton dans l'en-tête
// « Teleport-Jwt-Assertion » ; le claim « username » porte l'identité.
// On l'extrait pour l'attribuer dans le journal d'accès (art. 5.2).
//
// Modèle de confiance : la garantie repose ici sur le cloisonnement
// réseau (seul le proxy atteint le service). En production, on vérifie
// EN PLUS la signature du jeton contre la JWKS du cluster (clé publique
// de la CA JWT Teleport) — durcissement documenté dans infra/teleport/.
const (
	headerTeleportJWT  = "Teleport-Jwt-Assertion"
	headerTeleportUser = "Teleport-Username"
)

// teleportIdentity déduit l'identité de l'appelant, par ordre de préférence :
//  1. le claim « username » du jeton signé injecté par le bastion ;
//  2. l'en-tête Teleport-Username (chemin d'administration interne) ;
//  3. « inconnu ».
func teleportIdentity(r *http.Request) string {
	if u := usernameFromJWT(r.Header.Get(headerTeleportJWT)); u != "" {
		return u
	}
	if u := strings.TrimSpace(r.Header.Get(headerTeleportUser)); u != "" {
		return u
	}
	return "inconnu"
}

// usernameFromJWT décode la charge utile d'un JWT Teleport (sans
// revérifier la signature — cf. modèle de confiance ci-dessus) et
// renvoie le nom d'utilisateur (claim « username », à défaut « sub »).
func usernameFromJWT(token string) string {
	parts := strings.Split(token, ".")
	if len(parts) != 3 {
		return ""
	}
	payload, err := base64.RawURLEncoding.DecodeString(parts[1])
	if err != nil {
		return ""
	}
	var claims struct {
		Username string `json:"username"`
		Subject  string `json:"sub"`
	}
	if err := json.Unmarshal(payload, &claims); err != nil {
		return ""
	}
	if claims.Username != "" {
		return claims.Username
	}
	return claims.Subject
}
