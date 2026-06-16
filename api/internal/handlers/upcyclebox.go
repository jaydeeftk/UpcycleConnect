package handlers

import (
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

// OuvrirUpcycleBox : POST /api/box/{idBox}/ouvrir avec un code (Code_acces
// d'une demande). CONTRÔLE STRICT (item 18) : le code n'ouvre QUE le tiroir
// auquel il est lié. Un code valide pour un autre tiroir est rejeté en 403.
// Ne mute pas l'etat du Box (l'ouverture physique reste hors-perimetre M1) ;
// retourne 200 sur succes pour preuve operationnelle.
func OuvrirUpcycleBox(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 4 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idBoxDemande, err := strconv.Atoi(parts[len(parts)-2])
	if err != nil || idBoxDemande <= 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant du tiroir invalide")
		return
	}
	var body struct {
		Code string `json:"code"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || strings.TrimSpace(body.Code) == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Code d'accès manquant")
		return
	}

	var idBoxLie int
	var statut string
	err = database.DB.QueryRow(
		`SELECT COALESCE(Id_Box, 0), COALESCE(Statut, '')
		 FROM Demandes_conteneurs WHERE Code_acces = ?`,
		strings.TrimSpace(body.Code),
	).Scan(&idBoxLie, &statut)
	if errors.Is(err, sql.ErrNoRows) {
		httpx.JSONError(w, http.StatusForbidden, "Code d'accès invalide")
		return
	}
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	if statut != "validee" {
		httpx.JSONError(w, http.StatusForbidden, "Ce code d'accès n'est pas actif")
		return
	}
	if idBoxLie == 0 {
		httpx.JSONError(w, http.StatusForbidden, "Aucun tiroir associé à ce code")
		return
	}
	if idBoxLie != idBoxDemande {
		httpx.JSONError(w, http.StatusForbidden, "Ce code n'ouvre pas cet UpcycleBox")
		return
	}

	var ref string
	database.DB.QueryRow("SELECT COALESCE(Reference, '') FROM Box WHERE Id_Box = ?", idBoxLie).Scan(&ref)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"message":   "UpcycleBox ouvert",
		"id_box":    idBoxLie,
		"reference": ref,
	})
}
