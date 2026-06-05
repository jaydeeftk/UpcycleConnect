package handlers

import (
	"strconv"
	"strings"
)

// idDepuisChemin extrait le premier segment d'URL après prefix et le convertit
// en entier. Ex. : ("/api/evenements/12/participer", "/api/evenements/") -> 12.
// Centralise le parsing d'identifiant pour garder les handlers fins et cohérents.
func idDepuisChemin(path, prefix string) (int, error) {
	rest := strings.Trim(strings.TrimPrefix(path, prefix), "/")
	seg := strings.SplitN(rest, "/", 2)[0]
	return strconv.Atoi(seg)
}

// segmentsApres renvoie les segments non vides d'URL après prefix. Sert aux
// chemins « /{id}/{action} » : segs[0] = identifiant (brut, éventuellement
// non numérique comme un Id_Abonnement), segs[1] = action de transition.
func segmentsApres(path, prefix string) []string {
	rest := strings.Trim(strings.TrimPrefix(path, prefix), "/")
	if rest == "" {
		return nil
	}
	return strings.Split(rest, "/")
}
