package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Conteneurs, Localisation, Capacite, Statut FROM Conteneurs WHERE Statut = 'actif'")
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
	json.NewDecoder(r.Body).Decode(&body)
	database.DB.Exec(
		"INSERT INTO Demandes_conteneurs (Type_objet, Description, Etat_usure, Id_Conteneur, Date_depot, Destination, Prix_vente, Statut, Date_demande, Id_Particuliers) VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)",
		body.TypeObjet, body.Description, body.EtatUsure, body.IdConteneur, body.DateDepot, body.Destination, body.PrixVente, body.IdParticulier,
	)
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Demande envoyée"})
}

func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]
	rows, _ := database.DB.Query("SELECT Id_Demande, Type_objet, Statut FROM Demandes_conteneurs WHERE Id_Particuliers = ?", id)
	defer rows.Close()
	var list []map[string]interface{}
	for rows.Next() {
		var idD int
		var t, s string
		rows.Scan(&idD, &t, &s)
		list = append(list, map[string]interface{}{"id": idD, "type_objet": t, "statut": s})
	}
	httpx.JSONOK(w, http.StatusOK, list)
}

func AdminConteneurAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/conteneurs/")
	id = strings.Split(id, "/")[0]
	if r.Method == http.MethodDelete {
		database.DB.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Supprimé"})
	}
}
