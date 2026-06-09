package handlers

import (
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"log"
	"net"
	"net/http"
	"os"
	"regexp"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"

	"github.com/golang-jwt/jwt/v5"
	"golang.org/x/crypto/bcrypt"
)

var emailRegex = regexp.MustCompile(`^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$`)

// motDePasseRobuste : 8 caractères minimum, au moins une lettre et un chiffre.
func motDePasseRobuste(p string) bool {
	if len(p) < 8 {
		return false
	}
	hasLetter, hasDigit := false, false
	for _, c := range p {
		switch {
		case (c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z'):
			hasLetter = true
		case c >= '0' && c <= '9':
			hasDigit = true
		}
	}
	return hasLetter && hasDigit
}

// domaineEmailAccepteCourrier vérifie que le domaine de l'email existe et peut
// recevoir du courrier (enregistrements MX, ou à défaut A/AAAA).
func domaineEmailAccepteCourrier(email string) bool {
	at := strings.LastIndex(email, "@")
	if at < 0 {
		return false
	}
	domaine := email[at+1:]
	if mx, err := net.LookupMX(domaine); err == nil && len(mx) > 0 {
		return true
	}
	ips, err := net.LookupHost(domaine)
	return err == nil && len(ips) > 0
}

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

	if statut == "en_attente" {
		httpx.JSONError(w, http.StatusForbidden, "Compte non confirmé : cliquez sur le lien reçu par email pour l'activer.")
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
		Siret         string `json:"siret"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	// --- Validation stricte côté serveur (le formulaire est contournable) ---
	body.Nom = strings.TrimSpace(body.Nom)
	body.Prenom = strings.TrimSpace(body.Prenom)
	body.Email = strings.ToLower(strings.TrimSpace(body.Email))
	body.NomEntreprise = strings.TrimSpace(body.NomEntreprise)

	if len([]rune(body.Prenom)) < 2 || len([]rune(body.Nom)) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Indiquez un prénom et un nom valides (2 caractères minimum chacun).")
		return
	}
	if !emailRegex.MatchString(body.Email) {
		httpx.JSONError(w, http.StatusBadRequest, "Adresse email invalide.")
		return
	}
	if !domaineEmailAccepteCourrier(body.Email) {
		httpx.JSONError(w, http.StatusBadRequest, "Le domaine de cette adresse email n'existe pas ou ne reçoit pas de courrier.")
		return
	}
	if !motDePasseRobuste(body.Password) {
		httpx.JSONError(w, http.StatusBadRequest, "Mot de passe trop faible : 8 caractères minimum, avec au moins une lettre et un chiffre.")
		return
	}
	if body.Role != "particulier" && body.Role != "professionnel" {
		httpx.JSONError(w, http.StatusBadRequest, "Type de compte invalide.")
		return
	}

	var siret string
	if body.Role == "professionnel" {
		nomVerifie, e := ValiderSiret(body.Siret)
		if e != nil {
			httpx.JSONError(w, http.StatusBadRequest, e.Error())
			return
		}
		siret = chiffresSeulement(body.Siret)
		if nomVerifie != "" {
			body.NomEntreprise = nomVerifie // nom officiel issu du registre SIRENE
		}
		if body.NomEntreprise == "" {
			httpx.JSONError(w, http.StatusBadRequest, "Le nom de l'entreprise est requis.")
			return
		}
	}

	var exists int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs WHERE Email = ?", body.Email).Scan(&exists)
	if exists > 0 {
		httpx.JSONError(w, http.StatusConflict, "Email déjà utilisé")
		return
	}

	hashed, _ := bcrypt.GenerateFromPassword([]byte(body.Password), bcrypt.DefaultCost)

	// Confirmation par email : activée seulement si le SMTP est configuré. Sinon le
	// compte est créé actif (secours qui n'empêche jamais l'inscription).
	confirmationRequise := os.Getenv("SMTP_HOST") != ""
	statutInitial := "actif"
	tokenConfirmation := ""
	if confirmationRequise {
		statutInitial = "en_attente"
		tokenConfirmation = genererTokenConfirmation()
	}

	result, err := database.DB.Exec(
		"INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Token_confirmation, Date_Inscription, Id_Langue, Tutoriel_vu) VALUES (?, ?, ?, ?, ?, NULLIF(?,''), NOW(), 1, 0)",
		body.Nom, body.Prenom, body.Email, string(hashed), statutInitial, tokenConfirmation,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur lors de la création du compte")
		return
	}

	id, _ := result.LastInsertId()

	if body.Role == "professionnel" {
		database.DB.Exec(
			"INSERT INTO Professionnels_artisans (Nom_Entreprise, Type, Siret, Id_Utilisateurs) VALUES (?, ?, ?, ?)",
			body.NomEntreprise, body.Type, siret, id,
		)
	} else {
		database.DB.Exec(
			"INSERT INTO Particuliers (Score, Id_Utilisateurs) VALUES (0, ?)",
			id,
		)
	}

	if confirmationRequise && tokenConfirmation != "" {
		go func(email, tok string) {
			if err := services.SendVerificationEmail(email, tok); err != nil {
				log.Printf("[mail] echec envoi email de verification a %s : %v", email, err)
			} else {
				log.Printf("[mail] email de verification envoye a %s", email)
			}
		}(body.Email, tokenConfirmation)
	}

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id": id, "nom": body.Nom, "prenom": body.Prenom, "email": body.Email,
		"role": body.Role, "confirmation_required": confirmationRequise,
	})
}

// genererTokenConfirmation produit un jeton aléatoire (hex) pour l'activation du compte.
func genererTokenConfirmation() string {
	b := make([]byte, 32)
	if _, err := rand.Read(b); err != nil {
		return ""
	}
	return hex.EncodeToString(b)
}

// ConfirmerCompte active un compte via son jeton (lien reçu par email).
// GET /api/auth/confirmer?token=...
func ConfirmerCompte(w http.ResponseWriter, r *http.Request) {
	token := strings.TrimSpace(r.URL.Query().Get("token"))
	if token == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Jeton manquant")
		return
	}
	res, err := database.DB.Exec(
		"UPDATE Utilisateurs SET Statut='actif', Token_confirmation=NULL WHERE Token_confirmation=? AND Statut='en_attente'",
		token,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	if n, _ := res.RowsAffected(); n == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Lien invalide ou compte déjà activé")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Compte activé"})
}

func UpdateTutoriel(w http.ResponseWriter, r *http.Request) {
	id := middleware.GetUserID(r)
	database.DB.Exec("UPDATE Utilisateurs SET Tutoriel_vu = 1 WHERE Id_Utilisateurs = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Tutoriel à jour"})
}

func parseJWT(tokenStr string) (jwt.MapClaims, error) {
	token, err := jwt.Parse(tokenStr, func(t *jwt.Token) (interface{}, error) {
		return []byte(os.Getenv("JWT_SECRET")), nil
	}, jwt.WithValidMethods([]string{"HS256"}))
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
