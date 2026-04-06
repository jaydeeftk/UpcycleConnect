package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"

	"golang.org/x/crypto/bcrypt"
)

func AdminDashboard(w http.ResponseWriter, r *http.Request) {
	var users, annonces, evenements, messages int

	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&users)
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces").Scan(&annonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&evenements)
	database.DB.QueryRow("SELECT COUNT(*) FROM Messages").Scan(&messages)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"utilisateurs": users,
		"annonces":     annonces,
		"evenements":   evenements,
		"messages":     messages,
	})
}

func AdminGetUtilisateurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Statut, u.Date_Inscription,
			CASE
				WHEN a.Id_Administrateurs IS NOT NULL THEN 'admin'
				WHEN s.Id_Salaries IS NOT NULL THEN 'salarie'
				WHEN p.Id_Professionnels IS NOT NULL THEN 'professionnel'
				ELSE 'particulier'
			END AS role
		FROM Utilisateurs u
		LEFT JOIN Administrateurs a ON a.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Professionnels_artisans p ON p.Id_Utilisateurs = u.Id_Utilisateurs`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type User struct {
		ID              int    `json:"id"`
		Nom             string `json:"nom"`
		Prenom          string `json:"prenom"`
		Email           string `json:"email"`
		Statut          string `json:"statut"`
		DateInscription string `json:"date_inscription"`
		Role            string `json:"role"`
	}

	users := []User{}
	for rows.Next() {
		var u User
		rows.Scan(&u.ID, &u.Nom, &u.Prenom, &u.Email, &u.Statut, &u.DateInscription, &u.Role)
		users = append(users, u)
	}

	httpx.JSONOK(w, http.StatusOK, users)
}

func AdminUtilisateurAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/utilisateurs/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodGet:
		row := database.DB.QueryRow(
			`SELECT u.Id_Utilisateurs, u.Nom, u.Prenom, u.Email, u.Statut, u.Telephone, u.Adresse,
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
			WHERE u.Id_Utilisateurs = ?`, id,
		)
		var uid int
		var nom, prenom, email, statut, tel, adresse, role string
		if err := row.Scan(&uid, &nom, &prenom, &email, &statut, &tel, &adresse, &role); err != nil {
			httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
			"id": uid, "nom": nom, "prenom": prenom, "email": email,
			"statut": statut, "telephone": tel, "adresse": adresse, "role": role,
		})

	case http.MethodPut:
		var body struct {
			Nom       string `json:"nom"`
			Prenom    string `json:"prenom"`
			Email     string `json:"email"`
			Telephone string `json:"telephone"`
			Adresse   string `json:"adresse"`
			Statut    string `json:"statut"`
			Password  string `json:"mot_de_passe"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}

		if body.Password != "" {
			hashed, _ := bcrypt.GenerateFromPassword([]byte(body.Password), bcrypt.DefaultCost)
			database.DB.Exec(
				"UPDATE Utilisateurs SET Nom=?, Prenom=?, Email=?, Telephone=?, Adresse=?, Statut=?, Mot_de_passe=? WHERE Id_Utilisateurs=?",
				body.Nom, body.Prenom, body.Email, body.Telephone, body.Adresse, body.Statut, string(hashed), id,
			)
		} else {
			database.DB.Exec(
				"UPDATE Utilisateurs SET Nom=?, Prenom=?, Email=?, Telephone=?, Adresse=?, Statut=? WHERE Id_Utilisateurs=?",
				body.Nom, body.Prenom, body.Email, body.Telephone, body.Adresse, body.Statut, id,
			)
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Administrateurs WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Salaries WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Professionnels_artisans WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Particuliers WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminDeleteUtilisateur(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/utilisateurs/delete/")
	database.DB.Exec("DELETE FROM Administrateurs WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Salaries WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Professionnels_artisans WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Particuliers WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs=?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur supprimé"})
}

func AdminGetCategories(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Catalogue, Description, Illustration FROM Catalogue")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Cat struct {
		ID           int    `json:"id"`
		Description  string `json:"description"`
		Illustration string `json:"illustration"`
	}

	cats := []Cat{}
	for rows.Next() {
		var c Cat
		rows.Scan(&c.ID, &c.Description, &c.Illustration)
		cats = append(cats, c)
	}
	httpx.JSONOK(w, http.StatusOK, cats)
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/categories/")
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	database.DB.Exec("DELETE FROM Catalogue WHERE Id_Catalogue=?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Catégorie supprimée"})
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT a.Id_Annonces, a.Contenu, a.Statut, a.Date_publication,
			u.Nom, u.Prenom, u.Email
		FROM Annonces a
		JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY a.Date_publication DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Annonce struct {
		ID         int    `json:"id"`
		Contenu    string `json:"contenu"`
		Statut     string `json:"statut"`
		Date       string `json:"date_publication"`
		NomUser    string `json:"nom"`
		PrenomUser string `json:"prenom"`
		EmailUser  string `json:"email"`
	}

	annonces := []Annonce{}
	for rows.Next() {
		var a Annonce
		rows.Scan(&a.ID, &a.Contenu, &a.Statut, &a.Date, &a.NomUser, &a.PrenomUser, &a.EmailUser)
		annonces = append(annonces, a)
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminAnnonceAction(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/annonces/")
	id = strings.Split(id, "/")[0]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Statut string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec("UPDATE Annonces SET Statut=? WHERE Id_Annonces=?", body.Statut, id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Annonce mise à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Annonces WHERE Id_Annonces=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Annonce supprimée"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminGetMessages(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT m.Id_Messages, m.Contenu, m.Date_envoi,
			up.Nom AS nom_particulier, up.Prenom AS prenom_particulier,
			upr.Nom AS nom_pro, upr.Prenom AS prenom_pro
		FROM Messages m
		JOIN Particuliers pa ON pa.Id_Particuliers = m.Id_Particuliers
		JOIN Utilisateurs up ON up.Id_Utilisateurs = pa.Id_Utilisateurs
		JOIN Professionnels_artisans pr ON pr.Id_Professionnels = m.Id_Professionnels
		JOIN Utilisateurs upr ON upr.Id_Utilisateurs = pr.Id_Utilisateurs
		ORDER BY m.Date_envoi DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	type Msg struct {
		ID             int    `json:"id"`
		Contenu        string `json:"contenu"`
		Date           string `json:"date_envoi"`
		NomParticulier string `json:"nom_particulier"`
		PrenomPart     string `json:"prenom_particulier"`
		NomPro         string `json:"nom_pro"`
		PrenomPro      string `json:"prenom_pro"`
	}

	msgs := []Msg{}
	for rows.Next() {
		var m Msg
		rows.Scan(&m.ID, &m.Contenu, &m.Date, &m.NomParticulier, &m.PrenomPart, &m.NomPro, &m.PrenomPro)
		msgs = append(msgs, m)
	}
	httpx.JSONOK(w, http.StatusOK, msgs)
}
