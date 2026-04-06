package handlers

import (
	"encoding/json"
	"net/http"
	"os"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"

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

	row := database.DB.QueryRow(
		`SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Statut, u.Mot_de_passe,
			CASE
				WHEN a.Id_Administrateurs IS NOT NULL THEN 'admin'
				WHEN s.Id_Salaries IS NOT NULL THEN 'salarie'
				WHEN p.Id_Professionnels IS NOT NULL THEN 'professionnel'
				ELSE 'particulier'
			END AS role
		FROM Utilisateurs u
		LEFT JOIN Administrateurs a ON a.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Professionnels_artisans p ON p.Id_Utilisateurs = u.Id_Utilisateurs
		WHERE u.Email = ?`,
		body.Email,
	)

	var id int
	var nom, prenom, email, statut, hash, role string

	if err := row.Scan(&id, &nom, &prenom, &email, &statut, &hash, &role); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
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
		"token":  signed,
		"id":     id,
		"nom":    nom,
		"prenom": prenom,
		"email":  email,
		"role":   role,
		"statut": statut,
	})
}

func Register(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Nom      string `json:"nom"`
		Prenom   string `json:"prenom"`
		Email    string `json:"email"`
		Password string `json:"mot_de_passe"`
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

	hashed, err := bcrypt.GenerateFromPassword([]byte(body.Password), bcrypt.DefaultCost)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur serveur")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue) VALUES (?, ?, ?, ?, 'actif', NOW(), 1)",
		body.Nom, body.Prenom, body.Email, string(hashed),
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()

	database.DB.Exec(
		"INSERT INTO Particuliers (Id_Utilisateurs, Score) VALUES (?, 0)",
		id,
	)

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":     id,
		"nom":    body.Nom,
		"prenom": body.Prenom,
		"email":  body.Email,
	})
}
