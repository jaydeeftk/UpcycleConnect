package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
)

func GetPlanning(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(r.URL.Path, "/")
	idUtilisateur := parts[len(parts)-1]

	evRows, err := database.DB.Query(
		`SELECT e.Id_Evenements, e.Titre, COALESCE(DATE_FORMAT(e.Date_, '%Y-%m-%dT%H:%i:%s'),''), e.Lieu, e.Statut, COALESCE(e.Duree, 0)
		FROM Evenements e
		JOIN Participer_evenements pe ON pe.Id_Evenements = e.Id_Evenements
		JOIN Particuliers p ON p.Id_Particuliers = pe.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY e.Date_ ASC`, idUtilisateur,
	)

	var evenements []map[string]interface{}
	if err == nil {
		defer evRows.Close()
		for evRows.Next() {
			var id, duree int
			var titre, lieu, statut string
			var date *string
			evRows.Scan(&id, &titre, &date, &lieu, &statut, &duree)
			evenements = append(evenements, map[string]interface{}{
				"id": id, "titre": titre, "date": date,
				"lieu": lieu, "statut": statut, "duree": duree, "type": "evenement",
			})
		}
	}
	if evenements == nil {
		evenements = []map[string]interface{}{}
	}

	fRows, err := database.DB.Query(
		`SELECT f.Id_Formations, f.Titre, COALESCE(DATE_FORMAT(f.Date_formation, '%Y-%m-%dT%H:%i:%s'),''),
			COALESCE(f.Localisation, ''), f.Statut, COALESCE(f.Duree, 0), COALESCE(DATE_FORMAT(f.Date_fin, '%Y-%m-%d'),'')
		FROM Formations f
		JOIN Reserver_formation rf ON rf.Id_Formations = f.Id_Formations
		JOIN Particuliers p ON p.Id_Particuliers = rf.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY f.Date_formation ASC`, idUtilisateur,
	)

	var formations []map[string]interface{}
	if err == nil {
		defer fRows.Close()
		for fRows.Next() {
			var id, duree int
			var titre, lieu, statut, dateFin string
			var date *string
			fRows.Scan(&id, &titre, &date, &lieu, &statut, &duree, &dateFin)
			formations = append(formations, map[string]interface{}{
				"id": id, "titre": titre, "date": date, "date_fin": dateFin,
				"lieu": lieu, "statut": statut, "duree": duree, "type": "formation",
			})
		}
	}
	if formations == nil {
		formations = []map[string]interface{}{}
	}

	libreRows, err := database.DB.Query(
		`SELECT pp.Id_Planning_personnel, COALESCE(pp.Titre,''),
			COALESCE(DATE_FORMAT(pp.Date_debut, '%Y-%m-%dT%H:%i:%s'),''),
			COALESCE(DATE_FORMAT(pp.Date_fin,   '%Y-%m-%dT%H:%i:%s'),''),
			COALESCE(pp.Lieu,''), COALESCE(pp.Description,'')
		FROM Planning_personnel pp
		JOIN Particuliers p ON p.Id_Particuliers = pp.Id_Particuliers
		WHERE p.Id_Utilisateurs = ?
		ORDER BY pp.Date_debut ASC`, idUtilisateur,
	)
	libres := []map[string]interface{}{}
	if err == nil {
		defer libreRows.Close()
		for libreRows.Next() {
			var id int
			var titre, dDebut, dFin, lieu, desc string
			libreRows.Scan(&id, &titre, &dDebut, &dFin, &lieu, &desc)
			libres = append(libres, map[string]interface{}{
				"id": id, "titre": titre, "date": dDebut, "date_fin": dFin,
				"lieu": lieu, "description": desc, "statut": "personnel",
				"duree": dureeEnHeures(dDebut, dFin), "type": "libre",
			})
		}
	}

	depotRows, err := database.DB.Query(
		`SELECT dc.Id_Demandes_conteneurs, COALESCE(dc.Type_objet,''),
			COALESCE(DATE_FORMAT(dc.Date_depot, '%Y-%m-%dT%H:%i:%s'),''),
			COALESCE(c.Localisation,''), dc.Statut
		FROM Demandes_conteneurs dc
		JOIN Particuliers p ON p.Id_Particuliers = dc.Id_Particuliers
		LEFT JOIN Conteneurs c ON c.Id_Conteneurs = dc.Id_Conteneurs
		WHERE p.Id_Utilisateurs = ? AND dc.Date_depot IS NOT NULL
		ORDER BY dc.Date_depot ASC`, idUtilisateur,
	)
	depots := []map[string]interface{}{}
	if err == nil {
		defer depotRows.Close()
		for depotRows.Next() {
			var id int
			var typeObjet, date, lieu, statut string
			depotRows.Scan(&id, &typeObjet, &date, &lieu, &statut)
			depots = append(depots, map[string]interface{}{
				"id": id, "titre": "Dépôt : " + typeObjet, "date": date,
				"lieu": lieu, "statut": statut, "duree": 0, "type": "depot",
			})
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"evenements": evenements,
		"formations": formations,
		"libres":     libres,
		"depots":     depots,
		"stats": map[string]int{
			"evenements": len(evenements),
			"formations": len(formations),
			"libres":     len(libres),
			"depots":     len(depots),
		},
	})
}

func dureeEnHeures(debut, fin string) int {
	if debut == "" || fin == "" {
		return 0
	}
	const f = "2006-01-02T15:04:05"
	d, err := time.Parse(f, debut)
	if err != nil {
		return 0
	}
	e, err := time.Parse(f, fin)
	if err != nil {
		return 0
	}
	h := int(e.Sub(d).Hours())
	if h < 0 {
		return 0
	}
	return h
}

func AjouterEntreePlanning(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Titre       string `json:"titre"`
		DateDebut   string `json:"date_debut"`
		DateFin     string `json:"date_fin"`
		Lieu        string `json:"lieu"`
		Description string `json:"description"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	if strings.TrimSpace(body.Titre) == "" {
		httpx.JSONError(w, http.StatusUnprocessableEntity, "Le titre de l'entrée est obligatoire")
		return
	}
	if err := domain.ValiderDate(body.DateDebut); err != nil {
		httpx.WriteError(w, err)
		return
	}
	if body.DateFin != "" {
		if err := domain.ValiderDate(body.DateFin); err != nil {
			httpx.WriteError(w, err)
			return
		}
	}

	var idPart int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", middleware.GetUserID(r)).Scan(&idPart); err != nil {
		httpx.JSONError(w, http.StatusForbidden, "Action réservée aux particuliers")
		return
	}
	res, err := database.DB.Exec(
		`INSERT INTO Planning_personnel (Titre, Date_debut, Date_fin, Lieu, Description, Id_Particuliers)
		 VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?)`,
		body.Titre, body.DateDebut, body.DateFin, body.Lieu, body.Description, idPart,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	id, _ := res.LastInsertId()
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Entrée ajoutée au planning"})
}

func SupprimerEntreePlanning(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id, err := strconv.Atoi(parts[len(parts)-1])
	if err != nil || id <= 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	res, err := database.DB.Exec(
		`DELETE pp FROM Planning_personnel pp
		 JOIN Particuliers p ON p.Id_Particuliers = pp.Id_Particuliers
		 WHERE pp.Id_Planning_personnel = ? AND p.Id_Utilisateurs = ?`,
		id, middleware.GetUserID(r),
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	n, _ := res.RowsAffected()
	if n == 0 {
		httpx.JSONError(w, http.StatusNotFound, "Entrée introuvable ou non autorisée")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Entrée supprimée"})
}
