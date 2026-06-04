package main

import (
	"context"
	"encoding/base64"
	"encoding/json"
	"net/http/httptest"
	"testing"
)

// makeJWT fabrique un jeton de la forme « header.payload.signature ».
// La charge utile est l'encodage base64url (sans padding) du JSON des
// claims. La signature n'est pas vérifiée par le service (cf. modèle de
// confiance réseau dans teleport.go), une valeur factice suffit donc.
func makeJWT(t *testing.T, claims map[string]any) string {
	t.Helper()
	payload, err := json.Marshal(claims)
	if err != nil {
		t.Fatalf("encodage des claims: %v", err)
	}
	enc := base64.RawURLEncoding.EncodeToString(payload)
	return "header." + enc + ".signature"
}

func TestUsernameFromJWT(t *testing.T) {
	cases := []struct {
		name  string
		token string
		want  string
	}{
		{
			name:  "claim username present",
			token: makeJWT(t, map[string]any{"username": "alice.dpo", "sub": "alice"}),
			want:  "alice.dpo",
		},
		{
			name:  "repli sur sub si username absent",
			token: makeJWT(t, map[string]any{"sub": "bob.infra"}),
			want:  "bob.infra",
		},
		{
			name:  "username prioritaire sur sub",
			token: makeJWT(t, map[string]any{"username": "carol", "sub": "ignored"}),
			want:  "carol",
		},
		{
			name:  "claims vides",
			token: makeJWT(t, map[string]any{}),
			want:  "",
		},
		{
			name:  "jeton sans signature (2 segments)",
			token: "header.payload",
			want:  "",
		},
		{
			name:  "jeton à 4 segments",
			token: "a.b.c.d",
			want:  "",
		},
		{
			name:  "chaine vide",
			token: "",
			want:  "",
		},
		{
			name:  "payload base64 invalide",
			token: "header.!!!notbase64!!!.signature",
			want:  "",
		},
		{
			name:  "payload base64 valide mais JSON invalide",
			token: "header." + base64.RawURLEncoding.EncodeToString([]byte("pas du json")) + ".signature",
			want:  "",
		},
	}
	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			if got := usernameFromJWT(tc.token); got != tc.want {
				t.Errorf("usernameFromJWT() = %q, attendu %q", got, tc.want)
			}
		})
	}
}

func TestTeleportIdentity(t *testing.T) {
	validJWT := makeJWT(t, map[string]any{"username": "alice.dpo"})

	cases := []struct {
		name       string
		jwtHeader  string
		userHeader string
		want       string
	}{
		{
			name:      "identite issue du jeton signe",
			jwtHeader: validJWT,
			want:      "alice.dpo",
		},
		{
			name:       "repli sur l'en-tete Teleport-Username",
			userHeader: "infra.admin",
			want:       "infra.admin",
		},
		{
			name:       "le jeton signe prime sur l'en-tete brut",
			jwtHeader:  validJWT,
			userHeader: "autre",
			want:       "alice.dpo",
		},
		{
			name:       "jeton malforme -> repli sur l'en-tete",
			jwtHeader:  "jeton.invalide",
			userHeader: "audit",
			want:       "audit",
		},
		{
			name:       "en-tete avec espaces -> coupe",
			userHeader: "  dpo  ",
			want:       "dpo",
		},
		{
			name: "aucune identite -> inconnu",
			want: "inconnu",
		},
	}
	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			r := httptest.NewRequest("GET", "/api/adherents", nil)
			if tc.jwtHeader != "" {
				r.Header.Set(headerTeleportJWT, tc.jwtHeader)
			}
			if tc.userHeader != "" {
				r.Header.Set(headerTeleportUser, tc.userHeader)
			}
			if got := teleportIdentity(r); got != tc.want {
				t.Errorf("teleportIdentity() = %q, attendu %q", got, tc.want)
			}
		})
	}
}

func TestClientIP(t *testing.T) {
	cases := []struct {
		name       string
		xff        string
		remoteAddr string
		want       string
	}{
		{
			name:       "X-Forwarded-For: premiere adresse retenue",
			xff:        "203.0.113.7, 10.0.0.1",
			remoteAddr: "10.0.0.1:5050",
			want:       "203.0.113.7",
		},
		{
			name:       "sans XFF: hote extrait de RemoteAddr",
			remoteAddr: "192.0.2.10:443",
			want:       "192.0.2.10",
		},
		{
			name:       "RemoteAddr sans port: renvoye tel quel",
			remoteAddr: "192.0.2.55",
			want:       "192.0.2.55",
		},
	}
	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			r := httptest.NewRequest("GET", "/", nil)
			r.RemoteAddr = tc.remoteAddr
			if tc.xff != "" {
				r.Header.Set("X-Forwarded-For", tc.xff)
			}
			if got := clientIP(r); got != tc.want {
				t.Errorf("clientIP() = %q, attendu %q", got, tc.want)
			}
		})
	}
}

func TestIdentityOf(t *testing.T) {
	t.Run("identite presente dans le contexte", func(t *testing.T) {
		r := httptest.NewRequest("GET", "/", nil)
		ctx := context.WithValue(r.Context(), ctxIdentity, "alice.dpo")
		if got := identityOf(r.WithContext(ctx)); got != "alice.dpo" {
			t.Errorf("identityOf() = %q, attendu %q", got, "alice.dpo")
		}
	})
	t.Run("contexte vide -> inconnu", func(t *testing.T) {
		r := httptest.NewRequest("GET", "/", nil)
		if got := identityOf(r); got != "inconnu" {
			t.Errorf("identityOf() = %q, attendu %q", got, "inconnu")
		}
	})
}
