package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"

	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

var ticketSvc = services.NewTicketService()

func TicketMonTicketHandler(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	dto, err := ticketSvc.MonTicketOuvert(userID)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func TicketHistoriqueHandler(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	liste, err := ticketSvc.HistoriqueParticulier(userID)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func TicketMessagesEnvoyerHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	var body struct {
		Contenu string `json:"contenu"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	idTicket, err := ticketSvc.ParticulierEnvoyerMessage(userID, body.Contenu)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id_ticket": idTicket})
}

func TicketDispatch(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	estAdmin := middleware.GetRole(r) == "admin"
	segs := segmentsApres(r.URL.Path, "/api/tickets/")
	if len(segs) != 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idTicket, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch {
	case segs[1] == "messages" && r.Method == http.MethodGet:
		msgs, err := ticketSvc.Messages(userID, estAdmin, idTicket)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, msgs)

	case segs[1] == "messages" && r.Method == http.MethodPost:
		var body struct {
			Contenu string `json:"contenu"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := ticketSvc.EnvoyerDansTicket(userID, estAdmin, idTicket, body.Contenu); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Message envoyé"})

	case segs[1] == "fermer" && r.Method == http.MethodPost:
		if err := ticketSvc.Fermer(userID, estAdmin, idTicket); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Ticket fermé"})

	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}

func AdminTicketsHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	liste, err := ticketSvc.ListerPourAdmin()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminTicketAccepterHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	idAdmin := middleware.GetUserID(r)
	if idAdmin <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/admin/tickets/")
	if len(segs) != 2 || segs[1] != "accepter" {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idTicket, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if err := ticketSvc.Accepter(idAdmin, idTicket); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Ticket accepté"})
}
