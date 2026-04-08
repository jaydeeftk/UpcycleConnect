package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetForumSujets(w http.ResponseWriter, r *http.Request) {
	categorie := r.URL.Query().Get("categorie")

	query := `
		SELECT s.Id_Sujets, s.Titre, COALESCE(s.Contenu, ''), COALESCE(s.Categorie, 'general'),
			COALESCE(s.Statut, 'ouvert'), s.Date_Creation, COALESCE(s.Vues, 0),
			u.Nom, u.Prenom,
			COUNT(rep.Id_Reponses) AS nb_reponses
		FROM Sujets s
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		LEFT JOIN Reponses rep ON rep.Id_Sujets = s.Id_Sujets
	`
	args := []interface{}{}

	if categorie != "" && categorie != "tous" {
		query += " WHERE s.Categorie = ?"
		args = append(args, categorie)
	}

	query += " GROUP BY s.Id_Sujets ORDER BY s.Date_Creation DESC"

	rows, err := database.DB.Query(query, args...)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var sujets []map[string]interface{}
	for rows.Next() {
		var id, vues, nbReponses int
		var titre, contenu, cat, statut, nom, prenom string
		var date *string
		rows.Scan(&id, &titre, &contenu, &cat, &statut, &date, &vues, &nom, &prenom, &nbReponses)
		sujets = append(sujets, map[string]interface{}{
			"id":          id,
			"titre":       titre,
			"contenu":     contenu,
			"categorie":   cat,
			"statut":      statut,
			"date":        date,
			"vues":        vues,
			"auteur":      nom + " " + prenom,
			"nb_reponses": nbReponses,
			"resolu":      statut == "resolu",
		})
	}
	if sujets == nil {
		sujets = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, sujets)
}

func GetForumSujet(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	id := parts[len(parts)-1]

	database.DB.Exec("UPDATE Sujets SET Vues = Vues + 1 WHERE Id_Sujets = ?", id)

	row := database.DB.QueryRow(`
		SELECT s.Id_Sujets, s.Titre, COALESCE(s.Contenu, ''), COALESCE(s.Categorie, 'general'),
			COALESCE(s.Statut, 'ouvert'), s.Date_Creation, COALESCE(s.Vues, 0),
			u.Id_Utilisateurs, u.Nom, u.Prenom
		FROM Sujets s
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		WHERE s.Id_Sujets = ?
	`, id)

	var idS, idU, vues int
	var titre, contenu, cat, statut, nom, prenom string
	var date *string

	if err := row.Scan(&idS, &titre, &contenu, &cat, &statut, &date, &vues, &idU, &nom, &prenom); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Sujet non trouvé")
		return
	}

	repRows, err := database.DB.Query(`
		SELECT r.Id_Reponses, r.Contenu, r.Date_, COALESCE(r.Est_Solution, 0),
			u.Id_Utilisateurs, u.Nom, u.Prenom, u.Statut
		FROM Reponses r
		JOIN Utilisateurs u ON u.Id_Utilisateurs = r.Id_Utilisateurs
		WHERE r.Id_Sujets = ?
		ORDER BY r.Est_Solution DESC, r.Date_ ASC
	`, id)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer repRows.Close()

	var reponses []map[string]interface{}
	for repRows.Next() {
		var idR, idU, estSolution int
		var contenuR, nom, prenom, statutU string
		var dateR *string
		repRows.Scan(&idR, &contenuR, &dateR, &estSolution, &idU, &nom, &prenom, &statutU)
		reponses = append(reponses, map[string]interface{}{
			"id":            idR,
			"contenu":       contenuR,
			"date":          dateR,
			"est_solution":  estSolution == 1,
			"auteur_id":     idU,
			"auteur":        nom + " " + prenom,
			"auteur_statut": statutU,
		})
	}
	if reponses == nil {
		reponses = []map[string]interface{}{}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id":        idS,
		"titre":     titre,
		"contenu":   contenu,
		"categorie": cat,
		"statut":    statut,
		"date":      date,
		"vues":      vues,
		"auteur_id": idU,
		"auteur":    nom + " " + prenom,
		"resolu":    statut == "resolu",
		"reponses":  reponses,
	})
}

func CreateForumSujet(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	var body struct {
		Titre         string `json:"titre"`
		Contenu       string `json:"contenu"`
		Categorie     string `json:"categorie"`
		IdUtilisateur int    `json:"id_utilisateur"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	if body.Titre == "" || body.Contenu == "" || body.IdUtilisateur == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Titre, contenu et utilisateur requis")
		return
	}

	if body.Categorie == "" {
		body.Categorie = "general"
	}

	result, err := database.DB.Exec(
		"INSERT INTO Sujets (Titre, Contenu, Categorie, Statut, Date_Creation, Vues, Id_Forum, Id_Utilisateurs) VALUES (?, ?, ?, 'ouvert', NOW(), 0, 1, ?)",
		body.Titre, body.Contenu, body.Categorie, body.IdUtilisateur,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Sujet créé avec succès",
	})
}

func CreateForumReponse(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	parts := strings.Split(r.URL.Path, "/")
	idSujet := ""
	for i, p := range parts {
		if p == "sujets" && i+1 < len(parts) {
			idSujet = parts[i+1]
			break
		}
	}

	if idSujet == "" {
		httpx.JSONError(w, http.StatusBadRequest, "ID sujet manquant")
		return
	}

	var body struct {
		Contenu       string `json:"contenu"`
		IdUtilisateur int    `json:"id_utilisateur"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	if body.Contenu == "" || body.IdUtilisateur == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Contenu et utilisateur requis")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Reponses (Contenu, Date_, Est_Solution, Id_Sujets, Id_Utilisateurs) VALUES (?, NOW(), 0, ?, ?)",
		body.Contenu, idSujet, body.IdUtilisateur,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}

	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Réponse ajoutée avec succès",
	})
}

func MarquerSolution(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPatch {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	parts := strings.Split(r.URL.Path, "/")
	idSujet, idReponse := "", ""
	for i, p := range parts {
		if p == "sujets" && i+1 < len(parts) {
			idSujet = parts[i+1]
		}
		if p == "reponses" && i+1 < len(parts) {
			idReponse = parts[i+1]
		}
	}

	if idSujet == "" || idReponse == "" {
		httpx.JSONError(w, http.StatusBadRequest, "IDs manquants")
		return
	}

	database.DB.Exec("UPDATE Reponses SET Est_Solution = 0 WHERE Id_Sujets = ?", idSujet)
	database.DB.Exec("UPDATE Reponses SET Est_Solution = 1 WHERE Id_Reponses = ?", idReponse)
	database.DB.Exec("UPDATE Sujets SET Statut = 'resolu' WHERE Id_Sujets = ?", idSujet)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"message": "Réponse marquée comme solution",
	})
}

func ForumSujetsHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		GetForumSujets(w, r)
	case http.MethodPost:
		CreateForumSujet(w, r)
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ForumSujetDispatch(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")

	switch {
	case len(parts) == 4:
		GetForumSujet(w, r)
	case len(parts) == 5 && parts[4] == "reponses":
		CreateForumReponse(w, r)
	case len(parts) == 7 && parts[4] == "reponses" && parts[6] == "solution":
		MarquerSolution(w, r)
	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}