package handlers

import (
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func notifierSalarieFormation(idFormation string, contenu string) {
	notifierProprietaire(`SELECT s.Id_Utilisateurs FROM Formations f
		JOIN Salaries s ON s.Id_Salaries = f.Id_Salaries WHERE f.Id_Formations = ?`, idFormation, contenu)
}

func notifierSalarieEvenement(idEvenement string, contenu string) {
	notifierProprietaire(`SELECT s.Id_Utilisateurs FROM Evenements e
		JOIN Salaries s ON s.Id_Salaries = e.Id_Salaries WHERE e.Id_Evenements = ?`, idEvenement, contenu)
}

func notifierSalarieAtelier(idAtelier string, contenu string) {
	notifierProprietaire(`SELECT s.Id_Utilisateurs FROM Atelier a
		JOIN Salaries s ON s.Id_Salaries = a.Id_Salaries WHERE a.Id_Atelier = ?`, idAtelier, contenu)
}

func notifierProprietaire(queryUtilisateur string, id string, contenu string) {
	var idUser int
	if err := database.DB.QueryRow(queryUtilisateur, id).Scan(&idUser); err != nil {
		return
	}
	database.DB.Exec(
		`INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs)
		 SELECT ?, NOW(), 0, (SELECT MIN(Id_Administrateurs) FROM Administrateurs), ?`,
		contenu, idUser,
	)
}

func AdminEvenementAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/evenements/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	if len(parts) < 2 {
		AdminCreateEvenement(w, r)
		return
	}
	id := parts[0]
	switch parts[1] {
	case "valider":
		database.DB.Exec("UPDATE Evenements SET Statut_validation='valide', Motif_refus=NULL WHERE Id_Evenements=?", id)
		notifierSalarieEvenement(id, "Votre événement a été validé et publié.")
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement validé"})
	case "rejeter":
		motif := r.URL.Query().Get("motif")
		database.DB.Exec("UPDATE Evenements SET Statut_validation='refuse', Motif_refus=NULLIF(?, '') WHERE Id_Evenements=?", motif, id)
		msg := "Votre événement a été refusé."
		if motif != "" {
			msg += " Motif : " + motif
		}
		notifierSalarieEvenement(id, msg)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Événement rejeté"})
	default:
		AdminCreateEvenement(w, r)
	}
}

func AdminGetAteliers(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(`
		SELECT a.Id_Atelier, COALESCE(a.Theme,''), COALESCE(DATE_FORMAT(a.Date_atelier,'%Y-%m-%dT%H:%i:%s'),''),
		       COALESCE(a.Lieu,''), COALESCE(a.Statut,''), COALESCE(a.Statut_validation,''),
		       COALESCE(a.Motif_refus,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Atelier a
		LEFT JOIN Salaries s ON s.Id_Salaries = a.Id_Salaries
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY a.Id_Atelier DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()
	ateliers := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var theme, date, lieu, statut, sv, motif, nom, prenom string
		rows.Scan(&id, &theme, &date, &lieu, &statut, &sv, &motif, &nom, &prenom)
		ateliers = append(ateliers, map[string]interface{}{
			"id": id, "theme": theme, "date_atelier": date, "lieu": lieu,
			"statut": statut, "statut_validation": sv, "motif_refus": motif,
			"nom_salarie": nom, "prenom_salarie": prenom,
		})
	}
	httpx.JSONOK(w, http.StatusOK, ateliers)
}

func AdminAtelierAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/ateliers/")
	parts := strings.Split(strings.Trim(path, "/"), "/")
	if len(parts) == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "ID atelier manquant")
		return
	}
	id := parts[0]
	if len(parts) >= 2 {
		switch parts[1] {
		case "valider":
			database.DB.Exec("UPDATE Atelier SET Statut_validation='valide', Motif_refus=NULL WHERE Id_Atelier=?", id)
			notifierSalarieAtelier(id, "Votre atelier a été validé et publié.")
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Atelier validé"})
			return
		case "rejeter":
			motif := r.URL.Query().Get("motif")
			database.DB.Exec("UPDATE Atelier SET Statut_validation='refuse', Motif_refus=NULLIF(?, '') WHERE Id_Atelier=?", motif, id)
			msg := "Votre atelier a été refusé."
			if motif != "" {
				msg += " Motif : " + motif
			}
			notifierSalarieAtelier(id, msg)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Atelier rejeté"})
			return
		}
	}
	if r.Method == http.MethodDelete {
		database.DB.Exec("DELETE FROM Animer_atelier WHERE Id_Atelier=?", id)
		database.DB.Exec("DELETE FROM Atelier WHERE Id_Atelier=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Atelier supprimé"})
		return
	}
	httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
}
