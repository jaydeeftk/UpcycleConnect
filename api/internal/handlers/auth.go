package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func Login(w http.ResponseWriter, r *http.Request) {

	var body struct {
		Email    string `json:"email"`
		Password string `json:"mot_de_passe"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	row := database.DB.QueryRow(
		"SELECT id_utilisateur, nom, prenom, email, statut FROM utilisateurs WHERE email = ? AND mot_de_passe = ?",
		body.Email, body.Password,
	)

	var id int
	var nom, prenom, email, statut string

	if err := row.Scan(&id, &nom, &prenom, &email, &statut); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id":     id,
		"nom":    nom,
		"prenom": prenom,
		"email":  email,
		"statut": statut,
	})
}

func Register(w http.ResponseWriter, r *http.Request) {

	var body struct {
		Nom      string `json:"nom"`
		Prenom   string `json:"prenom"`
		Email    string `json:"email"`
		Password string `json:"mot_de_passe"`
		Role     string `json:"role"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	var exists int
	database.DB.QueryRow("SELECT COUNT(*) FROM utilisateurs WHERE email = ?", body.Email).Scan(&exists)

	if exists > 0 {
		httpx.JSONError(w, http.StatusConflict, "Email déjà utilisé")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, statut, id_langue) VALUES (?, ?, ?, ?, 'actif', 1)",
		body.Nom, body.Prenom, body.Email, body.Password,
	)

	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur lors de la création du compte")
		return
	}

	id, _ := result.LastInsertId()

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":     id,
		"nom":    body.Nom,
		"prenom": body.Prenom,
		"email":  body.Email,
	})
}
