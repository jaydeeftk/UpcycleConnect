package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"

	"golang.org/x/crypto/bcrypt"
)

func RecordVisite(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}
	var body struct {
		Page string `json:"page"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	if body.Page == "" {
		body.Page = "/"
	}
	ip := r.Header.Get("X-Forwarded-For")
	if ip == "" {
		ip = r.RemoteAddr
	}
	ua := r.Header.Get("User-Agent")
	database.DB.Exec(
		"INSERT INTO Visites (Page, Ip, User_agent, Date_visite) VALUES (?, ?, ?, NOW())",
		body.Page, ip, ua,
	)
	w.WriteHeader(http.StatusNoContent)
}

func AdminGetVisites(w http.ResponseWriter, r *http.Request) {
	var today, week, month, total int
	database.DB.QueryRow("SELECT COUNT(*) FROM Visites WHERE DATE(Date_visite) = CURDATE()").Scan(&today)
	database.DB.QueryRow("SELECT COUNT(*) FROM Visites WHERE Date_visite >= DATE_SUB(NOW(), INTERVAL 7 DAY)").Scan(&week)
	database.DB.QueryRow("SELECT COUNT(*) FROM Visites WHERE Date_visite >= DATE_SUB(NOW(), INTERVAL 30 DAY)").Scan(&month)
	database.DB.QueryRow("SELECT COUNT(*) FROM Visites").Scan(&total)

	rows, _ := database.DB.Query(
		`SELECT DATE(Date_visite) AS jour, COUNT(*) AS nb
		FROM Visites
		WHERE Date_visite >= DATE_SUB(NOW(), INTERVAL 7 DAY)
		GROUP BY jour ORDER BY jour ASC`,
	)
	par7j := []map[string]interface{}{}
	if rows != nil {
		defer rows.Close()
		for rows.Next() {
			var jour string
			var nb int
			rows.Scan(&jour, &nb)
			par7j = append(par7j, map[string]interface{}{"date": jour, "nb": nb})
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"today":    today,
		"week":     week,
		"month":    month,
		"total":    total,
		"par_jour": par7j,
	})
}

func AdminDashboard(w http.ResponseWriter, r *http.Request) {
	var users, annonces, evenements, messages, formations, conteneurs int
	database.DB.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&users)
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces").Scan(&annonces)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements").Scan(&evenements)
	database.DB.QueryRow("SELECT COUNT(*) FROM Messages").Scan(&messages)
	database.DB.QueryRow("SELECT COUNT(*) FROM Formations").Scan(&formations)
	database.DB.QueryRow("SELECT COUNT(*) FROM Conteneurs").Scan(&conteneurs)

	var annoncesEnAttente, demandesEnAttente, evenementsEnAttente, formationsEnAttente, ateliersEnAttente int
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces WHERE Statut='en_attente'").Scan(&annoncesEnAttente)
	database.DB.QueryRow("SELECT COUNT(*) FROM Demandes_conteneurs WHERE Statut='en_attente'").Scan(&demandesEnAttente)
	database.DB.QueryRow("SELECT COUNT(*) FROM Evenements WHERE Statut_validation='en_attente'").Scan(&evenementsEnAttente)
	database.DB.QueryRow("SELECT COUNT(*) FROM Formations WHERE Statut_validation='en_attente'").Scan(&formationsEnAttente)
	database.DB.QueryRow("SELECT COUNT(*) FROM Atelier WHERE Statut_validation='en_attente'").Scan(&ateliersEnAttente)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"utilisateurs":          users,
		"annonces":              annonces,
		"evenements":            evenements,
		"messages":              messages,
		"formations":            formations,
		"conteneurs":            conteneurs,
		"annonces_en_attente":   annoncesEnAttente,
		"demandes_en_attente":   demandesEnAttente,
		"evenements_en_attente": evenementsEnAttente,
		"formations_en_attente": formationsEnAttente,
		"ateliers_en_attente":   ateliersEnAttente,
	})
}

func AdminGetUtilisateurs(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT u.Id_Utilisateurs, COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,''), COALESCE(u.Statut,''), COALESCE(u.Date_Inscription,''),
			CASE
				WHEN a.Id_Administrateurs IS NOT NULL THEN 'admin'
				WHEN s.Id_Salaries IS NOT NULL THEN 'salarie'
				WHEN p.Id_Professionnels IS NOT NULL THEN 'professionnel'
				ELSE 'particulier'
			END AS role
		FROM Utilisateurs u
		LEFT JOIN Administrateurs a ON a.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
		LEFT JOIN Professionnels_artisans p ON p.Id_Utilisateurs = u.Id_Utilisateurs`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()
	users := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, prenom, email, statut, date, role string
		rows.Scan(&id, &nom, &prenom, &email, &statut, &date, &role)
		users = append(users, map[string]interface{}{
			"id": id, "nom": nom, "prenom": prenom, "email": email,
			"statut": statut, "date_inscription": date, "role": role,
		})
	}
	httpx.JSONOK(w, http.StatusOK, users)
}

func AdminUtilisateurAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/utilisateurs/")
	parts := strings.Split(strings.TrimSuffix(path, "/"), "/")
	id := parts[0]

	if len(parts) >= 2 && parts[1] == "planning" && r.Method == http.MethodDelete {
		AdminPlanningAction(w, r)
		return
	}

	if len(parts) >= 2 {
		action := parts[1]
		switch action {
		case "role":
			if r.Method == http.MethodPut || r.Method == http.MethodPatch {
				var body struct {
					Role string `json:"role"`
				}
				json.NewDecoder(r.Body).Decode(&body)
				adminUpdateRole(id, body.Role, w)
				return
			}
		case "statut":
			if r.Method == http.MethodPut || r.Method == http.MethodPatch {
				var body struct {
					Statut string `json:"statut"`
				}
				json.NewDecoder(r.Body).Decode(&body)
				database.DB.Exec("UPDATE Utilisateurs SET Statut=? WHERE Id_Utilisateurs=?", body.Statut, id)
				httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Statut mis à jour"})
				return
			}
		}
	}

	switch r.Method {
	case http.MethodGet:
		row := database.DB.QueryRow(
			`SELECT u.Id_Utilisateurs, COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,''), COALESCE(u.Statut,''), COALESCE(u.Telephone,''), COALESCE(u.Adresse,''),
				CASE
					WHEN a.Id_Administrateurs IS NOT NULL THEN 'admin'
					WHEN s.Id_Salaries IS NOT NULL THEN 'salarie'
					WHEN p.Id_Professionnels IS NOT NULL THEN 'professionnel'
					ELSE 'particulier'
				END AS role
			FROM Utilisateurs u
			LEFT JOIN Administrateurs a ON a.Id_Utilisateurs = u.Id_Utilisateurs
			LEFT JOIN Salaries s ON s.Id_Utilisateurs = u.Id_Utilisateurs
			LEFT JOIN Professionnels_artisans p ON p.Id_Utilisateurs = u.Id_Utilisateurs
			WHERE u.Id_Utilisateurs = ?`, id,
		)
		var uid int
		var nom, prenom, email, statut, tel, adresse, role string
		if err := row.Scan(&uid, &nom, &prenom, &email, &statut, &tel, &adresse, &role); err != nil {
			httpx.JSONError(w, http.StatusNotFound, "Utilisateur introuvable")
			return
		}

		annonces := []map[string]interface{}{}
		demandes := []map[string]interface{}{}

		var idPart int
		if errPart := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", uid).Scan(&idPart); errPart == nil {
			rAnn, _ := database.DB.Query("SELECT Id_Annonces, Titre, Statut, Date_publication FROM Annonces WHERE Id_Particuliers = ? ORDER BY Date_publication DESC", idPart)
			if rAnn != nil {
				defer rAnn.Close()
				for rAnn.Next() {
					var aId int
					var aTit, aStat string
					var aDate *string
					rAnn.Scan(&aId, &aTit, &aStat, &aDate)
					annonces = append(annonces, map[string]interface{}{"id": aId, "titre": aTit, "statut": aStat, "date": aDate})
				}
			}
			rDem, _ := database.DB.Query("SELECT Id_Demandes_conteneurs, Type_objet, Statut, Date_demande FROM Demandes_conteneurs WHERE Id_Particuliers = ? ORDER BY Date_demande DESC", idPart)
			if rDem != nil {
				defer rDem.Close()
				for rDem.Next() {
					var dId int
					var dType, dStat string
					var dDate *string
					rDem.Scan(&dId, &dType, &dStat, &dDate)
					demandes = append(demandes, map[string]interface{}{"id": dId, "type_objet": dType, "statut": dStat, "date": dDate})
				}
			}
		}

		planning := []map[string]interface{}{}
		rEvt, _ := database.DB.Query(
			`SELECT e.Id_Evenements, e.Titre, COALESCE(e.Date_,''), COALESCE(e.Lieu,''), COALESCE(e.Statut,'')
			FROM Participer_evenements pe
			JOIN Evenements e ON e.Id_Evenements = pe.Id_Evenements
			WHERE pe.Id_Particuliers = ?
			ORDER BY e.Date_ DESC`, idPart,
		)
		if rEvt != nil {
			defer rEvt.Close()
			for rEvt.Next() {
				var eId int
				var eTit, eDate, eLieu, eStat string
				rEvt.Scan(&eId, &eTit, &eDate, &eLieu, &eStat)
				planning = append(planning, map[string]interface{}{
					"id": eId, "titre": eTit, "date": eDate, "lieu": eLieu, "statut": eStat, "type": "evenement",
				})
			}
		}
		rForm, _ := database.DB.Query(
			`SELECT f.Id_Formations, f.Titre, COALESCE(f.Date_formation,''), COALESCE(f.Localisation,''), COALESCE(f.Statut,'')
			FROM Reserver_formation p
			JOIN Formations f ON f.Id_Formations = p.Id_Formations
			WHERE p.Id_Particuliers = ?
			ORDER BY f.Date_formation DESC`, idPart,
		)
		if rForm != nil {
			defer rForm.Close()
			for rForm.Next() {
				var fId int
				var fTit, fDate, fLieu, fStat string
				rForm.Scan(&fId, &fTit, &fDate, &fLieu, &fStat)
				planning = append(planning, map[string]interface{}{
					"id": fId, "titre": fTit, "date": fDate, "lieu": fLieu, "statut": fStat, "type": "formation",
				})
			}
		}

		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
			"id": uid, "nom": nom, "prenom": prenom, "email": email,
			"statut": statut, "telephone": tel, "adresse": adresse, "role": role,
			"historique": map[string]interface{}{
				"annonces": annonces,
				"demandes": demandes,
			},
			"planning": planning,
		})

	case http.MethodPut:
		var body struct {
			Nom       string `json:"nom"`
			Prenom    string `json:"prenom"`
			Email     string `json:"email"`
			Telephone string `json:"telephone"`
			Adresse   string `json:"adresse"`
			Statut    string `json:"statut"`
			Password  string `json:"mot_de_passe"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		if body.Password != "" {
			hashed, _ := bcrypt.GenerateFromPassword([]byte(body.Password), bcrypt.DefaultCost)
			database.DB.Exec(
				"UPDATE Utilisateurs SET Nom=?, Prenom=?, Email=?, Telephone=?, Adresse=?, Statut=?, Mot_de_passe=? WHERE Id_Utilisateurs=?",
				body.Nom, body.Prenom, body.Email, body.Telephone, body.Adresse, body.Statut, string(hashed), id,
			)
		} else {
			database.DB.Exec(
				"UPDATE Utilisateurs SET Nom=?, Prenom=?, Email=?, Telephone=?, Adresse=?, Statut=? WHERE Id_Utilisateurs=?",
				body.Nom, body.Prenom, body.Email, body.Telephone, body.Adresse, body.Statut, id,
			)
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur mis à jour"})

	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Administrateurs WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Salaries WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Professionnels_artisans WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Particuliers WHERE Id_Utilisateurs=?", id)
		database.DB.Exec("DELETE FROM Utilisateurs WHERE Id_Utilisateurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Utilisateur supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func adminUpdateRole(id, role string, w http.ResponseWriter) {
	database.DB.Exec("DELETE FROM Administrateurs WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Salaries WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Professionnels_artisans WHERE Id_Utilisateurs=?", id)
	database.DB.Exec("DELETE FROM Particuliers WHERE Id_Utilisateurs=?", id)

	switch role {
	case "admin":
		database.DB.Exec("INSERT IGNORE INTO Administrateurs (Id_Utilisateurs) VALUES (?)", id)
		database.DB.Exec("UPDATE Utilisateurs SET Statut='admin' WHERE Id_Utilisateurs=?", id)
	case "salarie":
		database.DB.Exec("INSERT IGNORE INTO Salaries (Id_Utilisateurs) VALUES (?)", id)
		database.DB.Exec("UPDATE Utilisateurs SET Statut='salarie' WHERE Id_Utilisateurs=?", id)
	case "professionnel":
		database.DB.Exec("INSERT IGNORE INTO Professionnels_artisans (Id_Utilisateurs) VALUES (?)", id)
		database.DB.Exec("UPDATE Utilisateurs SET Statut='actif' WHERE Id_Utilisateurs=?", id)
	default:
		database.DB.Exec("INSERT IGNORE INTO Particuliers (Score, Id_Utilisateurs) VALUES (0, ?)", id)
		database.DB.Exec("UPDATE Utilisateurs SET Statut='actif' WHERE Id_Utilisateurs=?", id)
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Rôle mis à jour"})
}

func AdminGetCategories(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Id_Categories, COALESCE(Nom,''), COALESCE(Description,'') FROM Categories")
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()
	categories := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, description string
		rows.Scan(&id, &nom, &description)
		categories = append(categories, map[string]interface{}{"id": id, "nom": nom, "description": description})
	}
	httpx.JSONOK(w, http.StatusOK, categories)
}

func AdminCreateCategorie(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Nom         string `json:"nom"`
		Description string `json:"description"`
		Icone       string `json:"icone"`
	}
	json.NewDecoder(r.Body).Decode(&body)
	database.DB.Exec("INSERT INTO Categories (Nom, Description) VALUES (?, ?)", body.Nom, body.Description)
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Catégorie créée"})
}

func AdminDeleteCategorie(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/categories/")
	id = strings.TrimSuffix(id, "/")
	database.DB.Exec("DELETE FROM Categories WHERE Id_Categories = ?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Catégorie supprimée"})
}

func AdminGetMessages(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodDelete {
		path := strings.TrimPrefix(r.URL.Path, "/api/admin/messages/")
		id := strings.Split(strings.Trim(path, "/"), "/")[0]
		database.DB.Exec("DELETE FROM Messages WHERE Id_Messages=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Message supprimé"})
		return
	}

	rows, err := database.DB.Query(
		`SELECT
			md.uid,
			COALESCE(u.Nom, '') AS nom,
			COALESCE(u.Prenom, '') AS prenom,
			COALESCE(u.Email, '') AS email,
			COALESCE(m_last.Contenu, '') AS dernier_message,
			COALESCE(DATE_FORMAT(m_last.Date_envoi, '%Y-%m-%d %H:%i:%s'), '') AS derniere_date
		FROM (
			SELECT
				COALESCE(m.Id_Utilisateurs, p.Id_Utilisateurs) AS uid,
				MAX(m.Id_Messages) AS last_id
			FROM Messages m
			LEFT JOIN Particuliers p ON p.Id_Particuliers = m.Id_Particuliers
			WHERE COALESCE(m.Id_Utilisateurs, p.Id_Utilisateurs) IS NOT NULL
			GROUP BY COALESCE(m.Id_Utilisateurs, p.Id_Utilisateurs)
		) AS md
		JOIN Utilisateurs u ON u.Id_Utilisateurs = md.uid
		JOIN Messages m_last ON m_last.Id_Messages = md.last_id
		ORDER BY md.last_id DESC
		LIMIT 50`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	msgs := []map[string]interface{}{}
	for rows.Next() {
		var idUser int
		var nom, prenom, email, dernierMsg, derniereDate string
		rows.Scan(&idUser, &nom, &prenom, &email, &dernierMsg, &derniereDate)
		msgs = append(msgs, map[string]interface{}{
			"id_utilisateur":  idUser,
			"nom":             nom,
			"prenom":          prenom,
			"email":           email,
			"dernier_message": dernierMsg,
			"derniere_date":   derniereDate,
		})
	}
	httpx.JSONOK(w, http.StatusOK, msgs)
}

func AdminGetConteneurs(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodPost {
		AdminCreateConteneur(w, r)
		return
	}
	liste, err := conteneurSvc.AdminListerConteneurs()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminCreateConteneur(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Localisation string  `json:"localisation"`
		Capacite     int     `json:"capacite"`
		Statut       string  `json:"statut"`
		Hauteur      float64 `json:"hauteur"`
		Largeur      float64 `json:"largeur"`
		Longueur     float64 `json:"longueur"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := conteneurSvc.CreerConteneur(middleware.GetUserID(r), services.ConteneurInput{
		Localisation: body.Localisation, Capacite: body.Capacite, Statut: body.Statut,
		Hauteur: body.Hauteur, Largeur: body.Largeur, Longueur: body.Longueur,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Conteneur créé"})
}

func AdminConteneurAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/conteneurs/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	switch r.Method {
	case http.MethodPut, http.MethodPatch:
		var body struct {
			Localisation string  `json:"localisation"`
			Capacite     int     `json:"capacite"`
			Statut       string  `json:"statut"`
			Hauteur      float64 `json:"hauteur"`
			Largeur      float64 `json:"largeur"`
			Longueur     float64 `json:"longueur"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := conteneurSvc.ModifierConteneur(id, services.ConteneurInput{
			Localisation: body.Localisation, Capacite: body.Capacite, Statut: body.Statut,
			Hauteur: body.Hauteur, Largeur: body.Largeur, Longueur: body.Longueur,
		}); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur mis à jour"})
	case http.MethodDelete:
		if err := conteneurSvc.SupprimerConteneur(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminGetDemandes(w http.ResponseWriter, r *http.Request) {
	liste, err := conteneurSvc.AdminListerDemandes()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminGetFinances(w http.ResponseWriter, r *http.Request) {
	agg, err := facturationSvc.Finances()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, agg)
}

func AdminForumSujetAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/forum/sujets/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/admin/forum/sujets/")
	action := ""
	if len(segs) > 1 {
		action = segs[1]
	}

	switch r.Method {
	case http.MethodPatch:
		if action == "" {
			httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
			return
		}
		if err := forumSvc.ModererSujet(id, action); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Sujet mis à jour"})

	case http.MethodDelete:
		if err := forumSvc.SupprimerSujet(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Sujet supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminForumReponseAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/forum/reponses/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	if err := forumSvc.SupprimerReponse(id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Réponse supprimée"})
}

func AdminGetConseils(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT c.Id_Conseils, COALESCE(c.Titre,''), COALESCE(c.Contenu,''), COALESCE(c.Categorie,''), COALESCE(c.Tags,''), COALESCE(c.Statut,'en_attente'), c.Date_d_ajout,
			u.Nom, u.Prenom, COALESCE(u.Role,'salarie')
		FROM Conseils c
		JOIN Salaries s ON s.Id_Salaries = c.Id_Salaries
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY c.Date_d_ajout DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	conseils := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var titre, contenu, cat, tags, statut, nom, prenom, role string
		var date *string
		rows.Scan(&id, &titre, &contenu, &cat, &tags, &statut, &date, &nom, &prenom, &role)
		conseils = append(conseils, map[string]interface{}{
			"id": id, "titre": titre, "contenu": contenu,
			"categorie": cat, "tags": tags, "statut": statut, "date": date,
			"auteur": nom + " " + prenom, "role": role,
		})
	}
	httpx.JSONOK(w, http.StatusOK, conseils)
}

func AdminConseilAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	if len(parts) >= 4 {
		id := parts[len(parts)-2]
		action := parts[len(parts)-1]
		switch action {
		case "valider":
			database.DB.Exec("UPDATE Conseils SET Statut='valide' WHERE Id_Conseils=?", id)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conseil validé"})
			return
		case "rejeter":
			database.DB.Exec("UPDATE Conseils SET Statut='rejete' WHERE Id_Conseils=?", id)
			httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conseil rejeté"})
			return
		}
	}
	id := parts[len(parts)-1]
	if r.Method == http.MethodDelete {
		database.DB.Exec("DELETE FROM Conseils WHERE Id_Conseils=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conseil supprimé"})
		return
	}
	httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
}

func AdminGetForumSujets(w http.ResponseWriter, r *http.Request) {
	liste, err := forumSvc.AdminListerSujets()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminDemandeAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/demandes/")
	parts := strings.Split(strings.TrimSuffix(path, "/"), "/")
	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}
	action := parts[0]
	id, err := strconv.Atoi(parts[1])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	switch action {
	case "valider":
		code, err := conteneurSvc.ValiderDemande(id)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande validée", "code_acces": code})
	case "refuser":
		if err := conteneurSvc.RefuserDemande(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Demande refusée"})
	case "deposer":
		if err := conteneurSvc.MarquerDeposee(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Dépôt confirmé"})
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Action inconnue")
	}
}

func AdminGetPlanning(w http.ResponseWriter, r *http.Request) {
	type PlanningItem struct {
		ID          int    `json:"id"`
		Titre       string `json:"titre"`
		Description string `json:"description"`
		Date        string `json:"date"`
		Type        string `json:"type"`
		Statut      string `json:"statut"`
	}
	var items []PlanningItem

	rowsEvt, err := database.DB.Query("SELECT Id_Evenements, Titre, COALESCE(Description,''), COALESCE(Date_,''), COALESCE(Statut,'') FROM Evenements ORDER BY Date_ ASC")
	if err == nil {
		defer rowsEvt.Close()
		for rowsEvt.Next() {
			var i PlanningItem
			i.Type = "evenement"
			rowsEvt.Scan(&i.ID, &i.Titre, &i.Description, &i.Date, &i.Statut)
			items = append(items, i)
		}
	}

	rowsForm, err := database.DB.Query("SELECT Id_Formations, Titre, COALESCE(Description,''), COALESCE(Date_formation,''), COALESCE(Statut,'') FROM Formations ORDER BY Date_formation ASC")
	if err == nil {
		defer rowsForm.Close()
		for rowsForm.Next() {
			var i PlanningItem
			i.Type = "formation"
			rowsForm.Scan(&i.ID, &i.Titre, &i.Description, &i.Date, &i.Statut)
			items = append(items, i)
		}
	}

	if items == nil {
		items = []PlanningItem{}
	}
	httpx.JSONOK(w, http.StatusOK, items)
}

func AdminGetSalariesList(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT s.Id_Salaries, u.Nom, u.Prenom, u.Email
		FROM Salaries s
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY u.Nom ASC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	list := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, prenom, email string
		rows.Scan(&id, &nom, &prenom, &email)
		list = append(list, map[string]interface{}{
			"id": id, "nom": nom, "prenom": prenom, "email": email,
			"label": nom + " " + prenom,
		})
	}
	httpx.JSONOK(w, http.StatusOK, list)
}

func AdminReplyMessage(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		IdUtilisateur int    `json:"id_utilisateur"`
		Contenu       string `json:"contenu"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil || body.Contenu == "" || body.IdUtilisateur == 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	_, err := database.DB.Exec(
		"INSERT INTO Messages (Contenu, Date_envoi, Id_Utilisateurs) VALUES (?, NOW(), ?)",
		body.Contenu, body.IdUtilisateur,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"message": "Réponse envoyée"})
}
func AdminPlanningAction(w http.ResponseWriter, r *http.Request) {

	path := strings.TrimPrefix(r.URL.Path, "/api/admin/utilisateurs/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if len(parts) < 4 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}
	uid := parts[0]
	planType := parts[2]
	itemId := parts[3]

	if planType != "formation" && planType != "evenement" {
		httpx.JSONError(w, http.StatusBadRequest, "Type inconnu")
		return
	}
	uidInt, _ := strconv.Atoi(uid)
	itemIdInt, _ := strconv.Atoi(itemId)
	// Même seam in-tx que la désinscription user (DELETE réel + ré-incrément
	// formation), au lieu de l'ancien DELETE sur la table orpheline Participer.
	if err := inscriptionSvc.AnnulerInscription(uidInt, planType, itemIdInt); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription effectuée"})
}
