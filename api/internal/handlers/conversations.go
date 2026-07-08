package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var conversationSvc = services.NewConversationService()

func ConversationsHandler(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}

	switch r.Method {
	case http.MethodGet:
		liste, err := conversationSvc.Lister(userID)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)

	case http.MethodPost:
		var body struct {
			IdAnnonce int `json:"id_annonce"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		id, err := conversationSvc.DemarrerConversation(userID, body.IdAnnonce)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"id": id})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ConversationMessagesHandler(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/conversations/")
	if len(segs) == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idConv, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	if len(segs) == 1 {
		if r.Method != http.MethodGet {
			httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
			return
		}
		info, err := conversationSvc.InfoConversation(userID, idConv)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, info)
		return
	}

	if segs[1] == "masquer" {
		if r.Method != http.MethodPost {
			httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
			return
		}
		if err := conversationSvc.Masquer(userID, idConv); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conversation supprimée"})
		return
	}

	if segs[1] != "messages" {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}

	switch r.Method {
	case http.MethodGet:
		msgs, err := conversationSvc.Messages(userID, idConv)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, msgs)

	case http.MethodPost:
		var body struct {
			Contenu string `json:"contenu"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := conversationSvc.Envoyer(userID, idConv, body.Contenu); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Message envoyé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
