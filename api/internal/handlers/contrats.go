package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

// facturationSvc — cas d'usage du vertical Contrat / Abonnement / Facture /
// Commission / Paiement. Sans état, partagé entre handlers.
var facturationSvc = services.NewFacturationService()

// AdminGetContrats : liste admin des contrats, chacun enrichi de allowed_actions
// (dérivées du statut CÔTÉ SERVEUR) — l'UI n'affiche que les transitions licites.
func AdminGetContrats(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerContrats()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// AdminCreateContrat : création d'un contrat rattaché à un professionnel. La
// validation (type, dates, état initial, pro existant) vit dans le service.
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

// AdminContratAction route les opérations sur un contrat :
//   - PUT /{id}/{action} : transition de la machine à états (activer, suspendre,
//     reactiver, resilier, expirer) appliquée sous verrou par le domaine ;
//   - PUT /{id}           : mise à jour non destructive (échéance, type) ;
//   - DELETE /{id}        : suppression.
//
// Une action de transition inconnue ou illicite est refusée par le domaine
// (409/422), jamais silencieusement ignorée.
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

// AdminGetAbonnements : catalogue des abonnements + allowed_actions par statut.
func AdminGetAbonnements(w http.ResponseWriter, r *http.Request) {
	liste, err := facturationSvc.ListerAbonnements()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// AdminCreateAbonnement : création d'un type d'abonnement. L'identifiant est
// généré côté serveur ; la validation (type, prix) vit dans le service.
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

// AdminAbonnementAction : PUT /{id}/{action} applique une transition sous verrou ;
// DELETE /{id} supprime. L'identifiant d'abonnement est une chaîne (Id_Abonnement).
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
