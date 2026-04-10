package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs WHERE Statut = 'actif'",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var conteneurs []map[string]interface{}
	for rows.Next() {
		var id int
		var localisation, capacite, statut string
		if err := rows.Scan(&id, &localisation, &capacite, &statut); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		conteneurs = append(conteneurs, map[string]interface{}{
			"id":           id,
			"localisation": localisation,
			"capacite":     capacite,
			"statut":       statut,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(conteneurs)
}

func CreateDemandeConteneur(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	var body struct {
		TypeObjet     string  `json:"type_objet"`
		Description   string  `json:"description"`
		EtatUsure     string  `json:"etat_usure"`
		IdConteneur   int     `json:"conteneur_id"`
		DateDepot     string  `json:"date_depot"`
		Destination   string  `json:"destination"`
		PrixVente     float64 `json:"prix_vente"`
		IdParticulier int     `json:"user_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Demandes_conteneurs (Type_objet, Description, Etat_usure, Id_Conteneur, Date_depot, Destination, Prix_vente, Statut, Date_demande, Id_Particuliers) VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)",
		body.TypeObjet, body.Description, body.EtatUsure, body.IdConteneur, body.DateDepot, body.Destination, body.PrixVente, body.IdParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Demande de dépôt soumise avec succès, en attente de validation",
	})
}

func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idParticulier := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT Id_Demande, Type_objet, Description, Etat_usure, Date_depot, Destination, COALESCE(Prix_vente, 0), Statut, Date_demande FROM Demandes_conteneurs WHERE Id_Particuliers = ? ORDER BY Date_demande DESC",
		idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var demandes []map[string]interface{}
	for rows.Next() {
		var id int
		var typeObjet, description, etatUsure, dateDepot, destination, statut, dateDemande string
		var prixVente float64
		if err := rows.Scan(&id, &typeObjet, &description, &etatUsure, &dateDepot, &destination, &prixVente, &statut, &dateDemande); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		demandes = append(demandes, map[string]interface{}{
			"id":          id,
			"type_objet":  typeObjet,
			"description": description,
			"etat_usure":  etatUsure,
			"date_depot":  dateDepot,
			"destination": destination,
			"prix_vente":  prixVente,
			"statut":      statut,
			"date":        dateDemande,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(demandes)
}

func AdminGetDemandesConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Demandes_conteneurs, Type_objet, Description, Etat_usure, Destination, Statut, Date_depot FROM Demandes_conteneurs ORDER BY Date_depot DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	demandes := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var typeObjet, description, etatUsure, destination, statut, date string
		rows.Scan(&id, &typeObjet, &description, &etatUsure, &destination, &statut, &date)
		demandes = append(demandes, map[string]interface{}{
			"id": id, "type_objet": typeObjet, "description": description,
			"etat_usure": etatUsure, "destination": destination, "statut": statut, "date_depot": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, demandes)
}

func AdminDemandeConteneurAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 5 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}
	id := parts[3]
	action := parts[4]

	switch action {
	case "accept":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut = 'accepte' WHERE Id_Demandes_conteneurs = ?", id)
	case "refuse":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut = 'refuse' WHERE Id_Demandes_conteneurs = ?", id)
	default:
		database.DB.Exec("DELETE FROM Demandes_conteneurs WHERE Id_Demandes_conteneurs = ?", id)
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Action effectuée"})
}
