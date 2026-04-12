package handlers

import (
	"encoding/json"
	"net/http"
	"os"
	"strings"
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

	var id, idParticulier, idProfessionnel, tutorielVu int
	var nom, prenom, email, statut, hash, role string

	row := database.DB.QueryRow(
		`SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Statut, u.Mot_de_passe, u.Tutoriel_vu,
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

	if err := row.Scan(&id, &nom, &prenom, &email, &statut, &hash, &tutorielVu, &idParticulier, &idProfessionnel, &role); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Identifiants incorrects")
		return
	}

	if err := bcrypt.CompareHashAndPassword([]byte(hash), []byte(body.Password)); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Email ou mot de passe incorrect")
		return
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, jwt.MapClaims{
		"sub":  id,
		"role": role,
		"exp":  time.Now().Add(720 * time.Hour).Unix(),
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

	result, err := database.DB.Exec(
		"INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu) VALUES (?, ?, ?, ?, 'actif', NOW(), 1, 0)",
		body.Nom, body.Prenom, body.Email, string(hashed),
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

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id": id, "nom": body.Nom, "prenom": body.Prenom, "email": body.Email, "role": body.Role,
	})
}

func UpdateTutoriel(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Id int `json:"id"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	database.DB.Exec("UPDATE Utilisateurs SET Tutoriel_vu = 1 WHERE Id_Utilisateurs = ?", body.Id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Tutoriel à jour"})
}

func parseJWT(tokenStr string) (jwt.MapClaims, error) {
	token, err := jwt.Parse(tokenStr, func(t *jwt.Token) (interface{}, error) {
		return []byte(os.Getenv("JWT_SECRET")), nil
	})
	if err != nil || !token.Valid {
		return nil, err
	}
	claims, ok := token.Claims.(jwt.MapClaims)
	if !ok {
		return nil, jwt.ErrTokenInvalidClaims
	}
	return claims, nil
}

func VerifyPassword(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Password string `json:"password"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || body.Password == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Mot de passe requis")
		return
	}

	authHeader := r.Header.Get("Authorization")
	tokenStr := strings.TrimPrefix(authHeader, "Bearer ")
	if tokenStr == "" {
		httpx.JSONError(w, http.StatusUnauthorized, "Token requis")
		return
	}

	claims, err := parseJWT(tokenStr)
	if err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Token invalide")
		return
	}
	userID := int(claims["sub"].(float64))

	var hashedPwd string
	if err := database.DB.QueryRow("SELECT Mot_de_passe FROM Utilisateurs WHERE Id_Utilisateurs = ?", userID).Scan(&hashedPwd); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
		return
	}
	if err := bcrypt.CompareHashAndPassword([]byte(hashedPwd), []byte(body.Password)); err != nil {
		httpx.JSONError(w, http.StatusUnauthorized, "Mot de passe incorrect")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"verified": true})
}
