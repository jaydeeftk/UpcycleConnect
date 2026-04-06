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
		Description   string  `json:"description"`
		Categorie     string  `json:"categorie"`
		Etat          string  `json:"etat"`
		TypeAnnonce   string  `json:"type_annonce"`
		Prix          float64 `json:"prix"`
		Ville         string  `json:"ville"`
		CodePostal    string  `json:"code_postal"`
		IdUtilisateur int     `json:"user_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	var idParticulier int
	err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdUtilisateur).Scan(&idParticulier)
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non trouvé comme particulier : "+err.Error())
		return
	}

	result, err := database.DB.Exec(
		`INSERT INTO Annonces (Titre, Description, Categorie, Etat, Type_annonce, Prix, Ville, Code_postal, Statut, Date_publication, Id_Particuliers)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)`,
		body.Titre, body.Description, body.Categorie, body.Etat, body.TypeAnnonce, body.Prix, body.Ville, body.CodePostal, idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur BDD : "+err.Error())
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
		`SELECT Id_Annonces, Titre, Description, Categorie, Etat, Type_annonce, COALESCE(Prix, 0), Ville, Code_postal, Statut, Date_publication
		FROM Annonces WHERE Statut = 'validee' ORDER BY Date_publication DESC`,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
		return
	}
	defer rows.Close()

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, description, categorie, etat, typeAnnonce, ville, codePostal, statut, date string
		var prix float64
		rows.Scan(&id, &titre, &description, &categorie, &etat, &typeAnnonce, &prix, &ville, &codePostal, &statut, &date)
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"categorie": categorie, "etat": etat, "type_annonce": typeAnnonce,
			"prix": prix, "ville": ville, "code_postal": codePostal,
			"statut": statut, "date": date,
		})
	}
	if annonces == nil {
		annonces = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func GetAnnoncesUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}

	rows, err := database.DB.Query(
		`SELECT Id_Annonces, Titre, Description, Categorie, Etat, Type_annonce, COALESCE(Prix, 0), Ville, Code_postal, Statut, Date_publication
		FROM Annonces WHERE Id_Particuliers = ? ORDER BY Date_publication DESC`,
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
		var titre, description, categorie, etat, typeAnnonce, ville, codePostal, statut, date string
		var prix float64
		rows.Scan(&id, &titre, &description, &categorie, &etat, &typeAnnonce, &prix, &ville, &codePostal, &statut, &date)
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "description": description,
			"categorie": categorie, "etat": etat, "type_annonce": typeAnnonce,
			"prix": prix, "ville": ville, "code_postal": codePostal,
			"statut": statut, "date": date,
		})
	}
	if annonces == nil {
		annonces = []map[string]interface{}{}
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT a.Id_Annonces, COALESCE(a.Titre, a.Contenu, ''), a.Statut, a.Date_publication, COALESCE(a.Categorie, ''),
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

	var annonces []map[string]interface{}
	for rows.Next() {
		var id int
		var titre, statut, date, categorie, nom, prenom, email string
		rows.Scan(&id, &titre, &statut, &date, &categorie, &nom, &prenom, &email)
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "statut": statut,
			"date": date, "categorie": categorie,
			"nom": nom, "prenom": prenom, "email": email,
		})
	}
	if annonces == nil {
		annonces = []map[string]interface{}{}
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
		database.DB.Exec("UPDATE Annonces SET Statut = ? WHERE Id_Annonces = ?", body.Statut, id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Statut mis à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Annonces WHERE Id_Annonces = ?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce supprimée"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
