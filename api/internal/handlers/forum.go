package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var forumSvc = services.NewForumService()

func GetForumSujets(w http.ResponseWriter, r *http.Request) {
	liste, err := forumSvc.ListerSujets(r.URL.Query().Get("categorie"))
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func GetForumSujet(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/forum/sujets/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	estAdmin := middleware.GetRole(r) == "admin"
	dto, err := forumSvc.ConsulterSujet(middleware.GetUserID(r), estAdmin, id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func CreateForumSujet(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}
	var body struct {
		Titre     string `json:"titre"`
		Contenu   string `json:"contenu"`
		Categorie string `json:"categorie"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := forumSvc.CreerSujet(userID, services.SujetInput{
		Titre: body.Titre, Contenu: body.Contenu, Categorie: body.Categorie,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Sujet créé avec succès"})
}

func GetForumReponses(w http.ResponseWriter, r *http.Request) {
	segs := segmentsApres(r.URL.Path, "/api/forum/sujets/")
	if len(segs) < 1 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	idSujet, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	reponses, err := forumSvc.ListerReponses(idSujet)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, reponses)
}

func CreateForumReponse(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}
	idSujet, err := idDepuisChemin(r.URL.Path, "/api/forum/sujets/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	var body struct {
		Contenu string `json:"contenu"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := forumSvc.RepondreSujet(userID, idSujet, body.Contenu)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Réponse ajoutée avec succès"})
}

func MarquerSolution(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPatch {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}

	segs := segmentsApres(r.URL.Path, "/api/forum/sujets/")
	if len(segs) < 3 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idSujet, err1 := strconv.Atoi(segs[0])
	idReponse, err2 := strconv.Atoi(segs[2])
	if err1 != nil || err2 != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiants invalides")
		return
	}
	role := middleware.GetRole(r)
	estSalarie := role == "salarie" || role == "admin"
	if err := forumSvc.MarquerSolution(userID, estSalarie, idSujet, idReponse); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Réponse marquée comme solution"})
}

func DeleteForumReponse(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/forum/reponses/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if err := forumSvc.SupprimerReponseUtilisateur(userID, id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Réponse supprimée"})
}

func ForumSujetsHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		GetForumSujets(w, r)
	case http.MethodPost:
		CreateForumSujet(w, r)
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ForumSujetDispatch(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	switch {
	case len(parts) == 4:
		GetForumSujet(w, r)
	case len(parts) == 5 && parts[4] == "reponses" && r.Method == http.MethodGet:
		GetForumReponses(w, r)
	case len(parts) == 5 && parts[4] == "reponses":
		CreateForumReponse(w, r)
	case len(parts) == 7 && parts[4] == "reponses" && parts[6] == "solution":
		MarquerSolution(w, r)
	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}
