package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var conteneurs []map[string]interface{}
	for rows.Next() {
		var id int
		var localisation, capacite, statut string
		rows.Scan(&id, &localisation, &capacite, &statut)
		conteneurs = append(conteneurs, map[string]interface{}{
			"id": id, "localisation": localisation, "capacite": capacite, "statut": statut,
		})
	}
	if conteneurs == nil {
		conteneurs = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, conteneurs)
}

func AdminGetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var conteneurs []map[string]interface{}
	for rows.Next() {
		var id int
		var loc, cap, stat string
		rows.Scan(&id, &loc, &cap, &stat)
		conteneurs = append(conteneurs, map[string]interface{}{"id": id, "localisation": loc, "capacite": cap, "statut": stat})
	}
	if conteneurs == nil {
		conteneurs = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, conteneurs)
}

func CreateDemandeConteneur(w http.ResponseWriter, r *http.Request) {
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

	var idParticulier int
	err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdParticulier).Scan(&idParticulier)
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non trouvé comme particulier")
		return
	}

	_, err = database.DB.Exec(
		"INSERT INTO Demandes_conteneurs (Type_objet, Description, Etat_usure, Id_Conteneur, Date_depot, Destination, Prix_vente, Statut, Date_demande, Id_Particuliers) VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)",
		body.TypeObjet, body.Description, body.EtatUsure, body.IdConteneur, body.DateDepot, body.Destination, body.PrixVente, idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur BDD : "+err.Error())
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Demande envoyée"})
}

func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}

	rows, err := database.DB.Query("SELECT Id_Demande, Type_objet, Statut FROM Demandes_conteneurs WHERE Id_Particuliers = ?", idParticulier)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var list []map[string]interface{}
	for rows.Next() {
		var idD int
		var t, s string
		rows.Scan(&idD, &t, &s)
		list = append(list, map[string]interface{}{"id": idD, "type_objet": t, "statut": s})
	}
	if list == nil {
		list = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, list)
}

func AdminConteneurAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/conteneurs/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodGet:
		row := database.DB.QueryRow("SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs WHERE Id_Conteneurs=?", id)
		var cid int
		var loc, cap, stat string
		if err := row.Scan(&cid, &loc, &cap, &stat); err != nil {
			httpx.JSONError(w, http.StatusNotFound, "Conteneur introuvable")
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"id": cid, "localisation": loc, "capacite": cap, "statut": stat})
	case http.MethodPost:
		var body struct {
			Localisation      string `json:"localisation"`
			Capacite          string `json:"capacite"`
			Statut            string `json:"statut"`
			IdAdministrateurs int    `json:"id_administrateurs"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		result, err := database.DB.Exec(
			"INSERT INTO Conteneurs (Localisation, Capacite, Statut, Id_Administrateurs) VALUES (?,?,?,?)",
			body.Localisation, body.Capacite, body.Statut, body.IdAdministrateurs,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID})
	case http.MethodPut:
		var body struct {
			Localisation string `json:"localisation"`
			Capacite     string `json:"capacite"`
			Statut       string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec("UPDATE Conteneurs SET Localisation=?, Capacite=?, Statut=? WHERE Id_Conteneurs=?",
			body.Localisation, body.Capacite, body.Statut, id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Mis à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
