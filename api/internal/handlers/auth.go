package handlers

import (
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
	"upcycleconnect/internal/utils"

	"github.com/golang-jwt/jwt/v5"
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

	var id, idParticulier, idProfessionnel, tutorielVu, isVerified int
	var nom, prenom, email, statut, hash, role string

	row := database.DB.QueryRow(
		`SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Statut, u.Mot_de_passe, u.Tutoriel_vu, u.is_verified,
            COALESCE(p.Id_Particuliers, 0), COALESCE(pro.Id_Professionnels, 0),
            CASE
                WHEN a.Id_Administrateurs IS NOT NULL THEN 'admin'
                WHEN s.Id_Salaries IS NOT NULL THEN 'salarie'
                WHEN pro.Id_Professionnels IS NOT NULL THEN 'professionnel'
                ELSE 'particulier'
            END AS role
        FROM Utilisateurs u
        LEFT JOIN Administrateurs a ON a.Id_Utilisateurs = u.Id_Utilisateurs
        LEFT JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
        LEFT JOIN Professionnels_artisans pro ON pro.Id_Utilisateurs = u.Id_Utilisateurs
        LEFT JOIN Particuliers p ON p.Id_Utilisateurs = u.Id_Utilisateurs
        WHERE u.Email = ?`,
		body.Email,
	)

	if err := row.Scan(&id, &nom, &prenom, &email, &statut, &hash, &tutorielVu, &isVerified, &idParticulier, &idProfessionnel, &role); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Identifiants incorrects")
		return
	}

	if isVerified == 0 {
		httpx.JSONError(w, http.StatusForbidden, "Veuillez valider votre compte par email avant de vous connecter.")
		return
	}

	if err := bcrypt.CompareHashAndPassword([]byte(hash), []byte(body.Password)); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
		return
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, jwt.MapClaims{
		"sub":  id,
		"role": role,
		"exp":  time.Now().Add(24 * time.Hour).Unix(),
	})

	signed, err := token.SignedString([]byte(os.Getenv("JWT_SECRET")))
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur génération token")
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"token":            signed,
		"id":               id,
		"id_particulier":   idParticulier,
		"id_professionnel": idProfessionnel,
		"nom":              nom,
		"prenom":           prenom,
		"email":            email,
		"role":             role,
		"statut":           statut,
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

	var exists int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs WHERE Email = ?", body.Email).Scan(&exists)
	if exists > 0 {
		httpx.JSONError(w, http.StatusConflict, "Email déjà utilisé")
		return
	}

	hashed, _ := bcrypt.GenerateFromPassword([]byte(body.Password), bcrypt.DefaultCost)

	vToken := utils.GenerateToken()

	result, err := database.DB.Exec(
		`INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu, is_verified, verification_token) 
         VALUES (?, ?, ?, ?, 'actif', NOW(), 1, 0, 0, ?)`,
		body.Nom, body.Prenom, body.Email, string(hashed), vToken,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()

	if body.Role == "professionnel" {
		database.DB.Exec(
			"INSERT INTO Professionnels_artisans (Nom_Entreprise, Type, Id_Utilisateurs) VALUES (?, ?, ?)",
			body.NomEntreprise, body.Type, id,
		)
	} else {
		database.DB.Exec(
			"INSERT INTO Particuliers (Score, Id_Utilisateurs) VALUES (0, ?)",
			id,
		)
	}

	go func(targetEmail, token string) {
		err := services.SendVerificationEmail(targetEmail, token)
		if err != nil {
			fmt.Printf("Erreur mail: %v\n", err)
		}
	}(body.Email, vToken)

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"message": "Inscription réussie. Vérifiez vos emails.",
		"id":      id,
	})
}

func UpdateTutoriel(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Id int `json:"id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		return
	}
	database.DB.Exec("UPDATE Utilisateurs SET Tutoriel_vu = 1 WHERE Id_Utilisateurs = ?", body.Id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Tutoriel à jour"})
}
