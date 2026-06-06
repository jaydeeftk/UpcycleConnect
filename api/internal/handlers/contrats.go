package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

var facturationSvc = services.NewFacturationService()

func AdminGetContrats(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerContrats()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminCreateContrat(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Type            string `json:"type"`
		DateSignature   string `json:"date_signature"`
		DateDebut       string `json:"date_debut"`
		DateFin         string `json:"date_fin"`
		Statut          string `json:"statut"`
		IdProfessionnel int    `json:"id_professionnels"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := facturationSvc.CreerContrat(services.ContratInput{
		Type: body.Type, DateSignature: body.DateSignature, DateDebut: body.DateDebut,
		DateFin: body.DateFin, Statut: body.Statut, IdProfessionnel: body.IdProfessionnel,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Contrat créé"})
}

func AdminContratAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/contrats/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/admin/contrats/")
	action := ""
	if len(segs) > 1 {
		action = segs[1]
	}

	switch r.Method {
	case http.MethodPut:
		if action != "" {
			if err := facturationSvc.TransitionContrat(id, action); err != nil {
				httpx.WriteError(w, err)
				return
			}
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Contrat mis à jour"})
			return
		}
		var body struct {
			DateFin string `json:"date_fin"`
			Type    string `json:"type"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := facturationSvc.ModifierContrat(id, services.ContratUpdateInput{
			DateFin: body.DateFin, Type: body.Type,
		}); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Contrat mis à jour"})

	case http.MethodDelete:
		if err := facturationSvc.SupprimerContrat(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Contrat supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminGetAbonnements(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerAbonnements()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminCreateAbonnement(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Type      string  `json:"type"`
		Prix      float64 `json:"prix"`
		DateDebut string  `json:"date_debut"`
		DateFin   string  `json:"date_fin"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := facturationSvc.CreerAbonnement(services.AbonnementInput{
		Type: body.Type, Prix: body.Prix, DateDebut: body.DateDebut, DateFin: body.DateFin,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Abonnement créé"})
}

func AdminAbonnementAction(w http.ResponseWriter, r *http.Request) {
	segs := segmentsApres(r.URL.Path, "/api/admin/abonnements/")
	if len(segs) == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	id := segs[0]
	action := ""
	if len(segs) > 1 {
		action = segs[1]
	}

	switch r.Method {
	case http.MethodPut:
		if action == "" {
			httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
			return
		}
		if err := facturationSvc.TransitionAbonnement(id, action); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Abonnement mis à jour"})

	case http.MethodDelete:
		if err := facturationSvc.SupprimerAbonnement(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Abonnement supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
