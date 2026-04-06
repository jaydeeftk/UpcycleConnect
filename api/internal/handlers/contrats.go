package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetContrats(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT c.Id_Contrats, c.Date_signature, c.Date_debut, c.Date_fin, c.Type,
			u.Nom, u.Prenom, p.Nom_Entreprise
		FROM Contrats c
		JOIN Professionnels_artisans p ON p.Id_Professionnels = c.Id_Professionnels
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY c.Date_debut DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Contrat struct {
		ID            int    `json:"id"`
		DateSignature string `json:"date_signature"`
		DateDebut     string `json:"date_debut"`
		DateFin       string `json:"date_fin"`
		Type          string `json:"type"`
		Nom           string `json:"nom"`
		Prenom        string `json:"prenom"`
		Entreprise    string `json:"nom_entreprise"`
	}

	contrats := []Contrat{}
	for rows.Next() {
		var c Contrat
		rows.Scan(&c.ID, &c.DateSignature, &c.DateDebut, &c.DateFin, &c.Type, &c.Nom, &c.Prenom, &c.Entreprise)
		contrats = append(contrats, c)
	}
	httpx.JSONOK(w, http.StatusOK, contrats)
}

func AdminContratAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/contrats/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			DateFin string `json:"date_fin"`
			Type    string `json:"type"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Contrats SET Date_fin=?, Type=? WHERE Id_Contrats=?",
			body.DateFin, body.Type, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Contrat mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Contrats WHERE Id_Contrats=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Contrat supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminGetAbonnements(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT a.Id_Abonnement, a.Type, a.Prix, a.Date_Debut, a.Date_Fin, a.Statut,
			u.Nom, u.Prenom
		FROM Abonnement a
		JOIN Professionnels_artisans p ON p.Id_Abonnement = a.Id_Abonnement
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Abo struct {
		ID        string  `json:"id"`
		Type      string  `json:"type"`
		Prix      float64 `json:"prix"`
		DateDebut string  `json:"date_debut"`
		DateFin   string  `json:"date_fin"`
		Statut    string  `json:"statut"`
		Nom       string  `json:"nom"`
		Prenom    string  `json:"prenom"`
	}

	abos := []Abo{}
	for rows.Next() {
		var a Abo
		rows.Scan(&a.ID, &a.Type, &a.Prix, &a.DateDebut, &a.DateFin, &a.Statut, &a.Nom, &a.Prenom)
		abos = append(abos, a)
	}
	httpx.JSONOK(w, http.StatusOK, abos)
}

func AdminAbonnementAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/abonnements/")
	if r.Method != http.MethodPut {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Statut string `json:"statut"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	database.DB.Exec("UPDATE Abonnement SET Statut=? WHERE Id_Abonnement=?", body.Statut, id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Abonnement mis à jour"})
}
