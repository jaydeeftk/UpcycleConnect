package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"time"

	"upcycleconnect/internal/httpx"
)

func luhnValide(num string) bool {
	sum := 0
	alt := false
	for i := len(num) - 1; i >= 0; i-- {
		c := num[i]
		if c < '0' || c > '9' {
			return false
		}
		d := int(c - '0')
		if alt {
			d *= 2
			if d > 9 {
				d -= 9
			}
		}
		sum += d
		alt = !alt
	}
	return sum%10 == 0
}

func chiffresSeulement(s string) string {
	var b strings.Builder
	for _, c := range s {
		if c >= '0' && c <= '9' {
			b.WriteRune(c)
		}
	}
	return b.String()
}

func ValiderSiret(siret string) (nomEntreprise string, err error) {
	siret = chiffresSeulement(siret)
	if len(siret) != 14 {
		return "", errInvalide("Le SIRET doit comporter 14 chiffres")
	}
	if !luhnValide(siret) {
		return "", errInvalide("SIRET invalide (clé de contrôle incorrecte)")
	}

	client := &http.Client{Timeout: 8 * time.Second}
	req, _ := http.NewRequest(http.MethodGet,
		"https://recherche-entreprises.api.gouv.fr/search?q="+siret+"&page=1&per_page=1", nil)
	resp, e := client.Do(req)
	if e != nil {
		return "", errInvalide("Impossible de vérifier le SIRET pour le moment")
	}
	defer resp.Body.Close()

	var data struct {
		TotalResults int `json:"total_results"`
		Results      []struct {
			NomComplet      string `json:"nom_complet"`
			NomRaisonSocial string `json:"nom_raison_sociale"`
		} `json:"results"`
	}
	if json.NewDecoder(resp.Body).Decode(&data) != nil || data.TotalResults == 0 || len(data.Results) == 0 {
		return "", errInvalide("Aucune entreprise trouvée pour ce SIRET")
	}
	nom := data.Results[0].NomComplet
	if nom == "" {
		nom = data.Results[0].NomRaisonSocial
	}
	return nom, nil
}

type erreurValidation struct{ msg string }

func (e erreurValidation) Error() string { return e.msg }
func errInvalide(m string) error         { return erreurValidation{m} }

func SiretVerify(w http.ResponseWriter, r *http.Request) {
	siret := strings.TrimPrefix(r.URL.Path, "/api/siret/")
	nom, err := ValiderSiret(siret)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
			"valid": false, "message": err.Error(),
		})
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"valid": true, "nom_entreprise": nom,
	})
}
