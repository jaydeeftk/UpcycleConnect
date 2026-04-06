package handlers

import (
	"encoding/json"
	"net/http"
	"strings"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func CreateAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Titre         string  `json:"titre"`
		Contenu       string  `json:"description"`
		Categorie     string  `json:"categorie"`
		Etat          string  `json:"etat"`
		TypeAnnonce   string  `json:"type_annonce"`
		Prix          float64 `json:"prix"`
		Ville         string  `json:"ville"`
		CodePostal    string  `json:"code_postal"`
		IdParticulier int     `json:"user_id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	result, err := database.DB.Exec(
		"INSERT INTO Annonces (Titre, Contenu, Categorie, Etat, Type_annonce, Prix, Ville, Code_postal, Statut, Date_publication, Id_Particuliers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)",
		body.Titre, body.Contenu, body.Categorie, body.Etat, body.TypeAnnonce, body.Prix, body.Ville, body.CodePostal, body.IdParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	id, _ := result.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Annonce soumise avec succès, en attente de validation",
	})
}

func GetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Titre, Contenu, Categorie, Etat, Type_annonce, COALESCE(Prix, 0), Ville, Code_postal, Statut, Date_publication FROM Annonces WHERE Statut = 'validee' ORDER BY Date_publication DESC",
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()
	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, contenu, categorie, etat, typeAnnonce, ville, codePostal, statut, date string
		var prix float64
		if err := rows.Scan(&id, &titre, &contenu, &categorie, &etat, &typeAnnonce, &prix, &ville, &codePostal, &statut, &date); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		annonces = append(annonces, map[string]interface{}{
			"id":           id,
			"titre":        titre,
			"contenu":      contenu,
			"categorie":    categorie,
			"etat":         etat,
			"type_annonce": typeAnnonce,
			"prix":         prix,
			"ville":        ville,
			"code_postal":  codePostal,
			"statut":       statut,
			"date":         date,
		})
	}
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(annonces)
}

func GetAnnoncesUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idParticulier := parts[len(parts)-1]

	rows, err := database.DB.Query(
		"SELECT Id_Annonces, Titre, Contenu, Categorie, Etat, Type_annonce, COALESCE(Prix, 0), Ville, Code_postal, Statut, Date_publication FROM Annonces WHERE Id_Particuliers = ? ORDER BY Date_publication DESC",
		idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, contenu, categorie, etat, typeAnnonce, ville, codePostal, statut, date string
		var prix float64
		if err := rows.Scan(&id, &titre, &contenu, &categorie, &etat, &typeAnnonce, &prix, &ville, &codePostal, &statut, &date); err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		annonces = append(annonces, map[string]interface{}{
			"id":           id,
			"titre":        titre,
			"contenu":      contenu,
			"categorie":    categorie,
			"etat":         etat,
			"type_annonce": typeAnnonce,
			"prix":         prix,
			"ville":        ville,
			"code_postal":  codePostal,
			"statut":       statut,
			"date":         date,
		})
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(annonces)
}