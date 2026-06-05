package handlers

import (
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

// ProfessionnelGetContrats : un professionnel ne voit QUE ses contrats. L'identité
// vient du JWT (sub) ; le service la résout en Id_Professionnels et lit les contrats
// par cette VRAIE clé. (Corrige l'ancienne requête qui interrogeait des colonnes
// inexistantes — Type_contrat, Montant, Id_Utilisateurs sur Contrats — et renvoyait
// donc toujours une liste vide après avoir avalé l'erreur SQL.)
func ProfessionnelGetContrats(w http.ResponseWriter, r *http.Request) {
	contrats, err := facturationSvc.ContratsDuProfessionnel(middleware.GetUserID(r))
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, contrats)
}
