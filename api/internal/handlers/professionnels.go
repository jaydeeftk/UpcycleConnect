package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"

	"github.com/golang-jwt/jwt/v5"
)

func getProfessionnelFromContext(r *http.Request) (userID int, profID int, ok bool) {
	claims, valid := r.Context().Value(middleware.ClaimsKey).(jwt.MapClaims)
	if !valid {
		return 0, 0, false
	}
	sub, _ := claims["sub"].(float64)
	userID = int(sub)
	if err := database.DB.QueryRow(
		"SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs=?", userID,
	).Scan(&profID); err != nil {
		return userID, 0, false
	}
	return userID, profID, true
}

func ProfessionnelGetProfile(w http.ResponseWriter, r *http.Request) {
	userID, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	row := database.DB.QueryRow(
		`SELECT u.Nom, u.Prenom, u.Email, COALESCE(u.Telephone,''), COALESCE(u.Adresse,''),
			COALESCE(pa.Siret,''), COALESCE(pa.Nom_Entreprise,''), COALESCE(pa.Type,'')
		FROM Utilisateurs u
		JOIN Professionnels_artisans pa ON pa.Id_Utilisateurs = u.Id_Utilisateurs
		WHERE u.Id_Utilisateurs=?`, userID,
	)
	var nom, prenom, email, tel, adresse, siret, entreprise, typePA string
	if err := row.Scan(&nom, &prenom, &email, &tel, &adresse, &siret, &entreprise, &typePA); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id_utilisateurs": userID, "id_professionnels": profID,
		"nom": nom, "prenom": prenom, "email": email,
		"telephone": tel, "adresse": adresse,
		"siret": siret, "nom_entreprise": entreprise, "type": typePA,
	})
}

func ProfessionnelProjetsHandler(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}

	switch r.Method {
	case http.MethodGet:
		rows, err := database.DB.Query(
			`SELECT p.Id_Projets, p.Titre, COALESCE(p.Description,''), COALESCE(p.Statut,'en_cours'),
				COALESCE(p.Date_Debut,''), COUNT(e.Id_Etapes) AS nb_etapes
			FROM Projets p
			LEFT JOIN Etapes e ON e.Id_Projets = p.Id_Projets
			WHERE p.Id_Professionnels=?
			GROUP BY p.Id_Projets ORDER BY p.Id_Projets DESC`, profID,
		)
		if err != nil {
			httpx.JSONOK(w, http.StatusOK, []interface{}{})
			return
		}
		defer rows.Close()
		projets := []map[string]interface{}{}
		for rows.Next() {
			var id, nbEtapes int
			var titre, desc, statut, dateDebut string
			rows.Scan(&id, &titre, &desc, &statut, &dateDebut, &nbEtapes)
			projets = append(projets, map[string]interface{}{
				"id": id, "titre": titre, "description": desc,
				"statut": statut, "date_debut": dateDebut, "nb_etapes": nbEtapes,
			})
		}
		httpx.JSONOK(w, http.StatusOK, projets)

	case http.MethodPost:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			DateDebut   string `json:"date_debut"`
			Statut      string `json:"statut"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if body.Statut == "" {
			body.Statut = "en_cours"
		}
		result, err := database.DB.Exec(
			"INSERT INTO Projets (Titre, Description, Date_Debut, Statut, Id_Professionnels) VALUES (?,?,?,?,?)",
			body.Titre, body.Description, body.DateDebut, body.Statut, profID,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		id, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Projet créé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ProfessionnelProjetAction(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	switch r.Method {
	case http.MethodGet:
		rows, err := database.DB.Query(
			"SELECT Id_Etapes, Nom, COALESCE(Description,''), COALESCE(Visuel,'') FROM Etapes WHERE Id_Projets=?", id,
		)
		if err != nil {
			httpx.JSONOK(w, http.StatusOK, []interface{}{})
			return
		}
		defer rows.Close()
		etapes := []map[string]interface{}{}
		for rows.Next() {
			var eid int
			var nom, desc, visuel string
			rows.Scan(&eid, &nom, &desc, &visuel)
			etapes = append(etapes, map[string]interface{}{
				"id": eid, "nom": nom, "description": desc, "visuel": visuel,
			})
		}
		httpx.JSONOK(w, http.StatusOK, etapes)

	case http.MethodPut:
		var body struct {
			Titre       string `json:"titre"`
			Description string `json:"description"`
			Statut      string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Projets SET Titre=?, Description=?, Statut=? WHERE Id_Projets=? AND Id_Professionnels=?",
			body.Titre, body.Description, body.Statut, id, profID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Projet mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Etapes WHERE Id_Projets=?", id)
		database.DB.Exec("DELETE FROM Projets WHERE Id_Projets=? AND Id_Professionnels=?", id, profID)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Projet supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ProfessionnelFavorisHandler(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}

	rows, err := database.DB.Query(
		`SELECT a.Id_Annonces, a.Titre, COALESCE(a.Description,''), COALESCE(a.Statut,''),
			COALESCE(a.Date_publication,'')
		FROM Annonces a
		JOIN Favoris f ON f.Id_Annonces = a.Id_Annonces
		WHERE f.Id_Professionnels=?
		ORDER BY a.Id_Annonces DESC`, profID,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	favoris := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var titre, desc, statut, date string
		rows.Scan(&id, &titre, &desc, &statut, &date)
		favoris = append(favoris, map[string]interface{}{
			"id": id, "titre": titre, "description": desc, "statut": statut, "date": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, favoris)
}

func ProfessionnelFavoriAction(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	annonceID := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPost:
		database.DB.Exec(
			"INSERT IGNORE INTO Favoris (Id_Professionnels, Id_Annonces) VALUES (?,?)", profID, annonceID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Ajouté aux favoris"})
	case http.MethodDelete:
		database.DB.Exec(
			"DELETE FROM Favoris WHERE Id_Professionnels=? AND Id_Annonces=?", profID, annonceID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Retiré des favoris"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ProfessionnelGetContrats(w http.ResponseWriter, r *http.Request) {
	userID, _, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}

	rows, err := database.DB.Query(
		`SELECT c.Id_Contrats, COALESCE(c.Type_contrat,''), COALESCE(c.Statut,''),
			COALESCE(c.Date_debut,''), COALESCE(c.Date_fin,''), COALESCE(c.Montant,0)
		FROM Contrats c
		WHERE c.Id_Utilisateurs=?
		ORDER BY c.Id_Contrats DESC`, userID,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	contrats := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var typeC, statut, dateDebut, dateFin string
		var montant float64
		rows.Scan(&id, &typeC, &statut, &dateDebut, &dateFin, &montant)
		contrats = append(contrats, map[string]interface{}{
			"id": id, "type": typeC, "statut": statut,
			"date_debut": dateDebut, "date_fin": dateFin, "montant": montant,
		})
	}
	httpx.JSONOK(w, http.StatusOK, contrats)
}

func ProfessionnelEtapeAction(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}

	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")

	switch r.Method {
	case http.MethodPost:
		projetID := parts[len(parts)-2]
		var body struct {
			Nom         string `json:"nom"`
			Description string `json:"description"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		var exists int
		database.DB.QueryRow(
			"SELECT COUNT(*) FROM Projets WHERE Id_Projets=? AND Id_Professionnels=?", projetID, profID,
		).Scan(&exists)
		if exists == 0 {
			httpx.JSONError(w, http.StatusForbidden, "Projet introuvable")
			return
		}
		result, _ := database.DB.Exec(
			"INSERT INTO Etapes (Nom, Description, Id_Projets) VALUES (?,?,?)",
			body.Nom, body.Description, projetID,
		)
		id, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Étape ajoutée"})

	case http.MethodDelete:
		etapeID := parts[len(parts)-1]
		database.DB.Exec(
			`DELETE e FROM Etapes e
			JOIN Projets p ON p.Id_Projets = e.Id_Projets
			WHERE e.Id_Etapes=? AND p.Id_Professionnels=?`, etapeID, profID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Étape supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
