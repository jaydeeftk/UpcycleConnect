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

func getSalarieFromContext(r *http.Request) (userID int, salarieID int, ok bool) {
	claims, valid := r.Context().Value(middleware.ClaimsKey).(jwt.MapClaims)
	if !valid {
		return 0, 0, false
	}
	sub, _ := claims["sub"].(float64)
	userID = int(sub)
	if err := database.DB.QueryRow(
		"SELECT Id_Salaries FROM Salaries WHERE Id_Utilisateurs=?", userID,
	).Scan(&salarieID); err != nil {
		return userID, 0, false
	}
	return userID, salarieID, true
}

func SalarieGetProfile(w http.ResponseWriter, r *http.Request) {
	userID, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	row := database.DB.QueryRow(
		`SELECT u.Nom, u.Prenom, u.Email, COALESCE(u.Telephone,''), COALESCE(u.Adresse,''), COALESCE(s.Poste,'')
		FROM Utilisateurs u
		JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
		WHERE u.Id_Utilisateurs=?`, userID,
	)
	var nom, prenom, email, tel, adresse, poste string
	if err := row.Scan(&nom, &prenom, &email, &tel, &adresse, &poste); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id_utilisateurs": userID, "id_salaries": salarieID,
		"nom": nom, "prenom": prenom, "email": email,
		"telephone": tel, "adresse": adresse, "poste": poste,
	})
}

func SalarieFormationsHandler(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}

	switch r.Method {
	case http.MethodGet:
		rows, err := database.DB.Query(
			`SELECT Id_Formations, Titre, Description, Prix, Duree, COALESCE(Statut,'en_attente'),
				COALESCE(Date_formation,''), COALESCE(Places_total,0), COALESCE(Places_dispo,0),
				COALESCE(Localisation,''), COALESCE(Categorie,'')
			FROM Formations WHERE Id_Salaries=? ORDER BY Id_Formations DESC`, salarieID,
		)
		if err != nil {
			httpx.JSONOK(w, http.StatusOK, []interface{}{})
			return
		}
		defer rows.Close()
		formations := []map[string]interface{}{}
		for rows.Next() {
			var id, duree, pTotal, pDispo int
			var titre, desc, statut, date, loc, cat string
			var prix float64
			rows.Scan(&id, &titre, &desc, &prix, &duree, &statut, &date, &pTotal, &pDispo, &loc, &cat)
			formations = append(formations, map[string]interface{}{
				"id": id, "titre": titre, "description": desc, "prix": prix,
				"duree": duree, "statut": statut, "date": date,
				"places_total": pTotal, "places_dispo": pDispo,
				"localisation": loc, "categorie": cat,
			})
		}
		httpx.JSONOK(w, http.StatusOK, formations)

	case http.MethodPost:
		var body struct {
			Titre         string  `json:"titre"`
			Description   string  `json:"description"`
			Prix          float64 `json:"prix"`
			Duree         int     `json:"duree"`
			DateFormation string  `json:"date_formation"`
			PlacesTotal   int     `json:"places_total"`
			Localisation  string  `json:"localisation"`
			Categorie     string  `json:"categorie"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			`INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Date_formation,
				Places_total, Places_dispo, Localisation, Categorie, Id_Salaries)
			VALUES (?,?,?,?,'en_attente',?,?,?,?,?,?)`,
			body.Titre, body.Description, body.Prix, body.Duree, body.DateFormation,
			body.PlacesTotal, body.PlacesTotal, body.Localisation, body.Categorie, salarieID,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		id, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Formation soumise pour validation"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func SalarieFormationAction(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Titre         string  `json:"titre"`
			Description   string  `json:"description"`
			Prix          float64 `json:"prix"`
			Duree         int     `json:"duree"`
			DateFormation string  `json:"date_formation"`
			PlacesTotal   int     `json:"places_total"`
			Localisation  string  `json:"localisation"`
			Categorie     string  `json:"categorie"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			`UPDATE Formations SET Titre=?, Description=?, Prix=?, Duree=?, Date_formation=?,
				Places_total=?, Localisation=?, Categorie=?
			WHERE Id_Formations=? AND Id_Salaries=? AND Statut='en_attente'`,
			body.Titre, body.Description, body.Prix, body.Duree, body.DateFormation,
			body.PlacesTotal, body.Localisation, body.Categorie, id, salarieID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation mise à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Formations WHERE Id_Formations=? AND Id_Salaries=?", id, salarieID)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Formation supprimée"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func SalarieConseils(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}

	switch r.Method {
	case http.MethodGet:
		rows, err := database.DB.Query(
			`SELECT Id_Conseils, COALESCE(Titre,''), Contenu, COALESCE(Categorie,''), COALESCE(Tags,''), Date_d_ajout
			FROM Conseils WHERE Id_Salaries=? ORDER BY Date_d_ajout DESC`, salarieID,
		)
		if err != nil {
			httpx.JSONOK(w, http.StatusOK, []interface{}{})
			return
		}
		defer rows.Close()
		conseils := []map[string]interface{}{}
		for rows.Next() {
			var id int
			var titre, contenu, cat, tags string
			var date *string
			rows.Scan(&id, &titre, &contenu, &cat, &tags, &date)
			conseils = append(conseils, map[string]interface{}{
				"id": id, "titre": titre, "contenu": contenu,
				"categorie": cat, "tags": tags, "date": date,
			})
		}
		httpx.JSONOK(w, http.StatusOK, conseils)

	case http.MethodPost:
		var body struct {
			Titre     string `json:"titre"`
			Contenu   string `json:"contenu"`
			Categorie string `json:"categorie"`
			Tags      string `json:"tags"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		result, err := database.DB.Exec(
			"INSERT INTO Conseils (Titre, Contenu, Categorie, Tags, Date_d_ajout, Id_Salaries, Statut) VALUES (?,?,?,?,NOW(),?,'en_attente')",
			body.Titre, body.Contenu, body.Categorie, body.Tags, salarieID,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		id, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Conseil publié"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func SalarieConseilAction(w http.ResponseWriter, r *http.Request) {
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Titre     string `json:"titre"`
			Contenu   string `json:"contenu"`
			Categorie string `json:"categorie"`
			Tags      string `json:"tags"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Conseils SET Titre=?, Contenu=?, Categorie=?, Tags=? WHERE Id_Conseils=? AND Id_Salaries=?",
			body.Titre, body.Contenu, body.Categorie, body.Tags, id, salarieID,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conseil mis à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Conseils WHERE Id_Conseils=? AND Id_Salaries=?", id, salarieID)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conseil supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
