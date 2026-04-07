package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func ForumSujetsHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		getSujets(w, r)
	case http.MethodPost:
		createSujet(w, r)
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func getSujets(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT s.Id_Sujets, s.Titre, s.Date_Creation,
			u.Nom, u.Prenom,
			COUNT(rep.Id_Reponses) AS nb_reponses
		FROM Sujets s
		JOIN Particuliers p ON p.Id_Particuliers = s.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		LEFT JOIN Reponses rep ON rep.Id_Sujets = s.Id_Sujets
		GROUP BY s.Id_Sujets
		ORDER BY s.Date_Creation DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var sujets []map[string]interface{}
	for rows.Next() {
		var id, nbReponses int
		var titre, nom, prenom string
		var date *string
		rows.Scan(&id, &titre, &date, &nom, &prenom, &nbReponses)
		sujets = append(sujets, map[string]interface{}{
			"id": id, "titre": titre, "date": date,
			"auteur": nom + " " + prenom, "nb_reponses": nbReponses,
		})
	}
	if sujets == nil {
		sujets = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, sujets)
}

func createSujet(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Titre         string `json:"titre"`
		IdUtilisateur int    `json:"user_id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non particulier")
		return
	}

	var idForum int
	database.DB.QueryRow("SELECT Id_Forum FROM Forum LIMIT 1").Scan(&idForum)
	if idForum == 0 {
		res, _ := database.DB.Exec("INSERT INTO Forum () VALUES ()")
		newID, _ := res.LastInsertId()
		idForum = int(newID)
	}

	result, err := database.DB.Exec(
		"INSERT INTO Sujets (Titre, Date_Creation, Id_Forum, Id_Particuliers) VALUES (?, NOW(), ?, ?)",
		body.Titre, idForum, idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id})
}

func ForumSujetDispatch(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/forum/sujets/")
	parts := strings.Split(strings.TrimSuffix(path, "/"), "/")
	id := parts[0]

	if len(parts) >= 2 && (parts[1] == "reponses" || parts[1] == "repondre") {
		creerReponse(w, r, id)
		return
	}

	if len(parts) >= 3 && parts[1] == "solution" {
		marquerSolution(w, r, id, parts[2])
		return
	}

	getSujet(w, r, id)
}

func getSujet(w http.ResponseWriter, r *http.Request, id string) {
	row := database.DB.QueryRow(
		`SELECT s.Id_Sujets, s.Titre, s.Date_Creation, u.Nom, u.Prenom
		FROM Sujets s
		JOIN Particuliers p ON p.Id_Particuliers = s.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		WHERE s.Id_Sujets = ?`, id,
	)
	var sid int
	var titre, nom, prenom string
	var date *string
	if err := row.Scan(&sid, &titre, &date, &nom, &prenom); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Sujet non trouvé")
		return
	}

	rows, _ := database.DB.Query(
		`SELECT r.Id_Reponses, r.Contenu, r.Date_,
			u.Nom, u.Prenom
		FROM Reponses r
		JOIN Professionnels_artisans pro ON pro.Id_Professionnels = r.Id_Professionnels
		JOIN Utilisateurs u ON u.Id_Utilisateurs = pro.Id_Utilisateurs
		WHERE r.Id_Sujets = ?
		ORDER BY r.Date_ ASC`, id,
	)
	defer rows.Close()

	var reponses []map[string]interface{}
	for rows.Next() {
		var rid int
		var contenu, rnom, rprenom string
		var rdate *string
		rows.Scan(&rid, &contenu, &rdate, &rnom, &rprenom)
		reponses = append(reponses, map[string]interface{}{
			"id": rid, "contenu": contenu, "date": rdate,
			"auteur": rnom + " " + rprenom,
		})
	}
	if reponses == nil {
		reponses = []map[string]interface{}{}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": sid, "titre": titre, "date": date,
		"auteur": nom + " " + prenom, "reponses": reponses,
	})
}

func creerReponse(w http.ResponseWriter, r *http.Request, idSujet string) {
	var body struct {
		Contenu       string `json:"contenu"`
		IdUtilisateur int    `json:"user_id"`
	}
	json.NewDecoder(r.Body).Decode(&body)

	var idPro int
	if err := database.DB.QueryRow("SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs = ?", body.IdUtilisateur).Scan(&idPro); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non professionnel")
		return
	}

	result, err := database.DB.Exec(
		"INSERT INTO Reponses (Contenu, Date_, Id_Sujets, Id_Professionnels) VALUES (?, NOW(), ?, ?)",
		body.Contenu, idSujet, idPro,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id})
}

func marquerSolution(w http.ResponseWriter, r *http.Request, idSujet, idReponse string) {
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Solution marquée"})
}
