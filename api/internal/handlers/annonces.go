package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var annonceSvc = services.NewAnnonceService()

func GetAnnonces(w http.ResponseWriter, r *http.Request) {
	liste, err := annonceSvc.ListerPubliees()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func GetAnnonceDispatch(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/annonces/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if parts[0] == "create" {
		middleware.JWTAuth(CreateAnnonce)(w, r)
		return
	}
	if parts[0] == "user" {
		middleware.JWTAuth(GetAnnoncesUser)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "annuler" {
		middleware.JWTAuth(AnnulerAnnonce)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "reserver" {
		middleware.JWTAuth(ReserverDonAnnonce)(w, r)
		return
	}
	middleware.OptionalJWT(ficheAnnonce)(w, r)
}

func CreateAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Categorie   string  `json:"categorie"`
		Etat        string  `json:"etat"`
		TypeAnnonce string  `json:"type_annonce"`
		Prix        float64 `json:"prix"`
		Ville       string  `json:"ville"`
		CodePostal  string  `json:"code_postal"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := annonceSvc.CreerAnnonce(middleware.GetUserID(r), services.CreationAnnonceInput{
		Titre: body.Titre, Description: body.Description, Categorie: body.Categorie,
		Etat: body.Etat, Type: body.TypeAnnonce, Prix: body.Prix,
		Ville: body.Ville, CodePostal: body.CodePostal,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Annonce soumise avec succès, en attente de validation",
	})
}

func ficheAnnonce(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	dto, err := annonceSvc.FicheAnnonce(middleware.GetUserID(r), middleware.GetRole(r), id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func GetAnnoncesUser(w http.ResponseWriter, r *http.Request) {
	liste, err := annonceSvc.MesAnnonces(middleware.GetUserID(r))
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AnnulerAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID annonce manquant")
		return
	}
	if err := annonceSvc.RetirerAnnonce(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce retirée"})
}

func ReserverDonAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID annonce manquant")
		return
	}
	if err := annonceSvc.ReserverDon(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Don réservé"})
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	annonces, err := annonceSvc.ListerAdmin()
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminAnnonceAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Statut string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)

		var serr error
		switch body.Statut {
		case "validee":
			serr = annonceSvc.ValiderAnnonce(id)
		case "refusee", "rejetee":
			serr = annonceSvc.RefuserAnnonce(id)
		default:
			httpx.JSONError(w, http.StatusUnprocessableEntity, "Transition non supportée")
			return
		}
		if serr != nil {
			httpx.WriteError(w, serr)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Statut mis à jour"})

	case http.MethodDelete:
		if err := annonceSvc.SupprimerAnnonce(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
