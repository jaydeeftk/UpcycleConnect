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
		json.NewDecoder(r.Body).Decode(&body)

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

	var msgs []map[string]interface{}
	for rows.Next() {
		var id int
		var cont, date, np, pp, npr, ppr string
		rows.Scan(&id, &cont, &date, &np, &pp, &npr, &ppr)
		msgs = append(msgs, map[string]interface{}{
			"id": id, "contenu": cont, "date": date, "nom_particulier": np, "prenom_particulier": pp, "nom_pro": npr, "prenom_pro": ppr,
		})
	}
	httpx.JSONOK(w, http.StatusOK, msgs)
}

func AdminGetCategories(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Catalogue, Description, Illustration FROM Catalogue")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var cats []map[string]interface{}
	for rows.Next() {
		var id int
		var desc, illu string
		rows.Scan(&id, &desc, &illu)
		cats = append(cats, map[string]interface{}{"id": id, "description": desc, "illustration": illu})
	}
	httpx.JSONOK(w, http.StatusOK, cats)
}

func AdminCreateCategorie(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Description  string `json:"description"`
		Illustration string `json:"illustration"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	database.DB.Exec("INSERT INTO Catalogue (Description, Illustration, Id_Administrateurs) VALUES (?, ?, 1)", body.Description, body.Illustration)
	httpx.JSONOK(w, http.StatusCreated, map[string]string{"message": "Catégorie créée"})
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/categories/")
	database.DB.Exec("DELETE FROM Catalogue WHERE Id_Catalogue = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Catégorie supprimée"})
}

func AdminGetDemandes(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT d.Id_Demande, d.Type_objet, d.Description, d.Etat_usure, d.Statut, d.Date_demande,
			COALESCE(d.Prix_vente, 0), c.Localisation,
			u.Nom, u.Prenom, u.Email
		FROM Demandes_conteneurs d
		LEFT JOIN Conteneurs c ON c.Id_Conteneurs = d.Id_Conteneur
		JOIN Particuliers p ON p.Id_Particuliers = d.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY d.Date_demande DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var demandes []map[string]interface{}
	for rows.Next() {
		var id int
		var typeObjet, description, etatUsure, statut, date, localisation, nom, prenom, email string
		var prix float64
		rows.Scan(&id, &typeObjet, &description, &etatUsure, &statut, &date, &prix, &localisation, &nom, &prenom, &email)
		demandes = append(demandes, map[string]interface{}{
			"id": id, "type_objet": typeObjet, "description": description,
			"etat_usure": etatUsure, "statut": statut, "date": date,
			"prix_vente": prix, "localisation": localisation,
			"nom": nom, "prenom": prenom, "email": email,
		})
	}
	if demandes == nil {
		demandes = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, demandes)
}

func AdminDemandeAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/demandes/")
	parts := strings.Split(path, "/")

	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}

	action := parts[0]
	id := parts[1]

	switch action {
	case "valider":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut = 'validee' WHERE Id_Demande = ?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Demande validée"})
	case "refuser":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut = 'refusee' WHERE Id_Demande = ?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Demande refusée"})
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
	}
}
