package handlers

import (
	"encoding/json"
	"log"
	"net/http"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"

	"golang.org/x/crypto/bcrypt"
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
		"SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Mot_de_passe, u.Statut, u.Tutoriel_vu, COALESCE(p.Id_Particuliers, 0), COALESCE(pro.Id_Professionnels, 0) FROM Utilisateurs u LEFT JOIN Particuliers p ON p.Id_Utilisateurs = u.Id_Utilisateurs LEFT JOIN Professionnels_artisans pro ON pro.Id_Utilisateurs = u.Id_Utilisateurs WHERE u.Email = ?",
		body.Email,
	)

	var id, idParticulier, idProfessionnel, tutorielVu int
	var nom, prenom, email, statut, hashedPassword string
	if err := row.Scan(&id, &nom, &prenom, &email, &hashedPassword, &statut, &tutorielVu, &idParticulier, &idProfessionnel); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
		return
	}

	if err := bcrypt.CompareHashAndPassword([]byte(hashedPassword), []byte(body.Password)); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
		return
	}

	role := "particulier"
	if idProfessionnel > 0 {
		role = "professionnel"
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id":               id,
		"id_particulier":   idParticulier,
		"id_professionnel": idProfessionnel,
		"nom":              nom,
		"prenom":           prenom,
		"email":            email,
		"statut":           statut,
		"role":             role,
		"tutoriel_vu":      tutorielVu,
	})
}

func Register(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Nom           string `json:"nom"`
		Prenom        string `json:"prenom"`
		Email         string `json:"email"`
		Password      string `json:"mot_de_passe"`
		Role          string `json:"role"`
		NomEntreprise string `json:"nom_entreprise"`
		Type          string `json:"type"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	log.Println("ROLE RECU:", body.Role)

	var exists int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs WHERE Email = ?", body.Email).Scan(&exists)
	if exists > 0 {
		httpx.JSONError(w, http.StatusConflict, "Email déjà utilisé")
		return
	}

	hashed, err := bcrypt.GenerateFromPassword([]byte(body.Password), 10)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur hashage")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu) VALUES (?, ?, ?, ?, 'actif', NOW(), 1, 0)",
		body.Nom, body.Prenom, body.Email, string(hashed),
	)
	if err != nil {
		log.Println("REGISTER ERROR:", err.Error())
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()
	log.Println("ID UTILISATEUR:", id)

	if body.Role == "professionnel" {
		_, err = database.DB.Exec(
			"INSERT INTO Professionnels_artisans (Nom_Entreprise, Type, Id_Utilisateurs) VALUES (?, ?, ?)",
			body.NomEntreprise, body.Type, id,
		)
		if err != nil {
			log.Println("PROFESSIONNEL ERROR:", err.Error())
		}
	} else {
		_, err = database.DB.Exec(
			"INSERT INTO Particuliers (Score, Id_Utilisateurs) VALUES (0, ?)",
			id,
		)
		if err != nil {
			log.Println("PARTICULIER ERROR:", err.Error())
		}
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":     id,
		"nom":    body.Nom,
		"prenom": body.Prenom,
		"email":  body.Email,
		"role":   body.Role,
	})
}

func UpdateTutoriel(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	var body struct {
		Id int `json:"id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	_, err := database.DB.Exec(
		"UPDATE Utilisateurs SET Tutoriel_vu = 1 WHERE Id_Utilisateurs = ?",
		body.Id,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"message": "Tutoriel marqué comme vu",
	})
}
