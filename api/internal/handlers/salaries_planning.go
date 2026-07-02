package handlers

import (
	"net/http"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

// GetPlanningGlobalSalarie agrege evenements, formations et ateliers dans un seul
// planning (vue equipe partagee), avec des cles communes (id, titre, date, duree,
// lieu, animateur, statut, type) pour que le front puisse les afficher dans une
// grille commune, comme le planning particulier. DateFin n'est renseigne que pour
// les formations qui s'etalent sur plusieurs jours.
//
// "animateur" reflete la delegation : c'est l'animateur assigne (Id_Salarie_Animateur)
// s'il existe, sinon le createur. peut_gerer indique si l'utilisateur courant peut
// gerer le programme (etapes) de l'item ; peut_deleguer indique s'il peut reassigner
// l'animateur (reserve au createur).
func GetPlanningGlobalSalarie(w http.ResponseWriter, r *http.Request) {
	_, salarieID, _ := getSalarieFromContext(r)
	items := []map[string]interface{}{}

	evRows, err := database.DB.Query(`
		SELECT e.Id_Evenements, COALESCE(e.Titre,''), COALESCE(e.Lieu,''),
		       COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), COALESCE(e.Duree,0),
		       COALESCE(e.Statut,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,''),
		       COALESCE(e.Id_Salaries,0), e.Id_Salarie_Animateur
		FROM Evenements e
		LEFT JOIN Salaries s ON s.Id_Salaries = COALESCE(e.Id_Salarie_Animateur, e.Id_Salaries)
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
	`)
	if err == nil {
		defer evRows.Close()
		for evRows.Next() {
			var id, duree, idCreateur int
			var titre, lieu, date, statut, nom, prenom string
			var idAnimateur *int
			if evRows.Scan(&id, &titre, &lieu, &date, &duree, &statut, &nom, &prenom, &idCreateur, &idAnimateur) == nil {
				items = append(items, itemPlanning(id, titre, lieu, date, "", duree, statut,
					prenomNom(prenom, nom), "evenement", idCreateur, idAnimateur, salarieID))
			}
		}
	}

	foRows, err := database.DB.Query(`
		SELECT f.Id_Formations, COALESCE(f.Titre,''), COALESCE(f.Localisation,''),
		       COALESCE(DATE_FORMAT(f.Date_formation, '%Y-%m-%dT%H:%i:%s'),''), COALESCE(DATE_FORMAT(f.Date_fin, '%Y-%m-%d'),''),
		       COALESCE(f.Duree,0), COALESCE(f.Statut,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,''),
		       COALESCE(f.Id_Salaries,0), f.Id_Salarie_Animateur
		FROM Formations f
		LEFT JOIN Salaries s ON s.Id_Salaries = COALESCE(f.Id_Salarie_Animateur, f.Id_Salaries)
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
	`)
	if err == nil {
		defer foRows.Close()
		for foRows.Next() {
			var id, duree, idCreateur int
			var titre, lieu, date, dateFin, statut, nom, prenom string
			var idAnimateur *int
			if foRows.Scan(&id, &titre, &lieu, &date, &dateFin, &duree, &statut, &nom, &prenom, &idCreateur, &idAnimateur) == nil {
				items = append(items, itemPlanning(id, titre, lieu, date, dateFin, duree, statut,
					prenomNom(prenom, nom), "formation", idCreateur, idAnimateur, salarieID))
			}
		}
	}

	atRows, err := database.DB.Query(`
		SELECT a.Id_Atelier, COALESCE(a.Theme,''), COALESCE(a.Lieu,''),
		       COALESCE(DATE_FORMAT(a.Date_atelier, '%Y-%m-%dT%H:%i:%s'),''),
		       COALESCE(a.Statut,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,''),
		       COALESCE(a.Id_Salaries,0), a.Id_Salarie_Animateur
		FROM Atelier a
		LEFT JOIN Salaries s ON s.Id_Salaries = COALESCE(a.Id_Salarie_Animateur, a.Id_Salaries)
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
	`)
	if err == nil {
		defer atRows.Close()
		for atRows.Next() {
			var id, idCreateur int
			var theme, lieu, date, statut, nom, prenom string
			var idAnimateur *int
			if atRows.Scan(&id, &theme, &lieu, &date, &statut, &nom, &prenom, &idCreateur, &idAnimateur) == nil {
				items = append(items, itemPlanning(id, theme, lieu, date, "", 0, statut,
					prenomNom(prenom, nom), "atelier", idCreateur, idAnimateur, salarieID))
			}
		}
	}

	httpx.JSONOK(w, http.StatusOK, items)
}

func itemPlanning(id int, titre, lieu, date, dateFin string, duree int, statut, animateur, typeItem string,
	idCreateur int, idAnimateur *int, salarieID int) map[string]interface{} {
	peutGerer := salarieID != 0 && (salarieID == idCreateur || (idAnimateur != nil && *idAnimateur == salarieID))
	return map[string]interface{}{
		"id": id, "titre": titre, "lieu": lieu, "date": date, "date_fin": dateFin,
		"duree": duree, "statut": statut, "animateur": animateur, "type": typeItem,
		"id_createur": idCreateur, "peut_gerer": peutGerer, "peut_deleguer": salarieID != 0 && salarieID == idCreateur,
	}
}

func prenomNom(prenom, nom string) string {
	if prenom == "" && nom == "" {
		return ""
	}
	return prenom + " " + nom
}
