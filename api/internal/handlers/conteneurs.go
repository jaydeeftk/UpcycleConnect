package handlers

import (
	"crypto/rand"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func GetConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		"SELECT Id_Conteneurs, COALESCE(Localisation,''), COALESCE(Capacite,0), COALESCE(Statut,'disponible') FROM Conteneurs WHERE Statut='disponible'",
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	conteneurs := []map[string]interface{}{}
	for rows.Next() {
		var id, capacite int
		var localisation, statut string
		rows.Scan(&id, &localisation, &capacite, &statut)
		conteneurs = append(conteneurs, map[string]interface{}{
			"id": id, "localisation": localisation, "capacite": capacite, "statut": statut,
		})
	}
	httpx.JSONOK(w, http.StatusOK, conteneurs)
}

func CreateDemandeConteneur(w http.ResponseWriter, r *http.Request) {
	var body struct {
		TypeObjet   string  `json:"type_objet"`
		Description string  `json:"description"`
		EtatUsure   string  `json:"etat_usure"`
		IdConteneur int     `json:"conteneur_id"`
		DateDepot   string  `json:"date_depot"`
		Destination string  `json:"destination"`
		PrixVente   float64 `json:"prix_vente"`
		PhotoUrl    string  `json:"photo_url"`
		IdUser      int     `json:"user_id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", body.IdUser).Scan(&idParticulier); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non trouvé comme particulier")
		return
	}

	_, err := database.DB.Exec(
		"INSERT INTO Demandes_conteneurs (Type_objet, Description, Etat_usure, Id_Conteneurs, Date_depot, Destination, Prix_vente, Photo_url, Statut, Date_demande, Id_Particuliers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)",
		body.TypeObjet, body.Description, body.EtatUsure, body.IdConteneur, body.DateDepot, body.Destination, body.PrixVente, body.PhotoUrl, idParticulier,
	)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur BDD : "+err.Error())
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Demande envoyée"})
}

func GetDemandesConteneurUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	var idParticulier int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idParticulier); err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}

	rows, err := database.DB.Query(
		`SELECT Id_Demandes_conteneurs, COALESCE(Type_objet,''), COALESCE(Description,''), COALESCE(Etat_usure,''),
			COALESCE(Statut,'en_attente'), COALESCE(Code_acces,''), COALESCE(Date_demande,'')
		FROM Demandes_conteneurs WHERE Id_Particuliers = ? ORDER BY Date_demande DESC`,
		idParticulier,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()

	list := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var typeObjet, desc, etat, statut, code, date string
		rows.Scan(&id, &typeObjet, &desc, &etat, &statut, &code, &date)
		list = append(list, map[string]interface{}{
			"id": id, "type_objet": typeObjet, "description": desc,
			"etat_usure": etat, "statut": statut, "code_acces": code, "date": date,
		})
	}
	httpx.JSONOK(w, http.StatusOK, list)
}

func AdminGetDemandesConteneurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT d.Id_Demandes_conteneurs, COALESCE(d.Type_objet,''), COALESCE(d.Description,''),
			COALESCE(d.Etat_usure,''), COALESCE(d.Statut,'en_attente'), COALESCE(d.Date_demande,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,''), COALESCE(d.Code_acces,'')
		FROM Demandes_conteneurs d
		JOIN Particuliers p ON p.Id_Particuliers = d.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY d.Date_demande DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	demandes := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var typeObjet, desc, etat, statut, date, nom, prenom, email, code string
		rows.Scan(&id, &typeObjet, &desc, &etat, &statut, &date, &nom, &prenom, &email, &code)
		demandes = append(demandes, map[string]interface{}{
			"id": id, "type_objet": typeObjet, "description": desc,
			"etat_usure": etat, "statut": statut, "date": date,
			"nom": nom, "prenom": prenom, "email": email, "code_acces": code,
		})
	}
	httpx.JSONOK(w, http.StatusOK, demandes)
}

func AdminDemandeConteneurAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}
	id := parts[len(parts)-2]
	action := parts[len(parts)-1]

	switch action {
	case "accept":
		code := genererCode()
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut='validee', Code_acces=? WHERE Id_Demandes_conteneurs=?", code, id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande acceptée", "code_acces": code})
	case "refuse":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut='refusee' WHERE Id_Demandes_conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande refusée"})
	default:
		database.DB.Exec("DELETE FROM Demandes_conteneurs WHERE Id_Demandes_conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande supprimée"})
	}
}

func genererCode() string {
	const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
	b := make([]byte, 8)
	rand.Read(b)
	result := make([]byte, 8)
	for i := range result {
		result[i] = chars[int(b[i])%len(chars)]
	}
	return fmt.Sprintf("UC-%s", string(result))
}
