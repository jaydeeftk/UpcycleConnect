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
		`SELECT c.Id_Contrats, COALESCE(c.Date_signature,''), COALESCE(c.Date_debut,''), COALESCE(c.Date_fin,''), COALESCE(c.Type,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(p.Nom_Entreprise,'')
		FROM Contrats c
		LEFT JOIN Professionnels_artisans p ON p.Id_Professionnels = c.Id_Professionnels
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY c.Date_debut DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
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

func AdminCreateContrat(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Type          string `json:"type"`
		DateSignature string `json:"date_signature"`
		DateDebut     string `json:"date_debut"`
		DateFin       string `json:"date_fin"`
		IdProfessionnel int  `json:"id_professionnels"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	result, err := database.DB.Exec(
		"INSERT INTO Contrats (Type, Date_signature, Date_debut, Date_fin, Id_Professionnels) VALUES (?,?,?,?,?)",
		body.Type, body.DateSignature, body.DateDebut, body.DateFin, body.IdProfessionnel,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Contrat créé"})
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
	httpx.JSONOK(w, http.StatusOK, []interface{}{})
}

func AdminAbonnementAction(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Non implémenté"})
}
