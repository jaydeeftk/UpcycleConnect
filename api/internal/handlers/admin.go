package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"

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

	var annoncesEnAttente, demandesEnAttente int
	database.DB.QueryRow("SELECT COUNT(*) FROM Annonces WHERE Statut='en_attente'").Scan(&annoncesEnAttente)
	database.DB.QueryRow("SELECT COUNT(*) FROM Demandes_conteneurs WHERE Statut='en_attente'").Scan(&demandesEnAttente)

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"utilisateurs":        users,
		"annonces":            annonces,
		"evenements":          evenements,
		"messages":            messages,
		"formations":          formations,
		"conteneurs":          conteneurs,
		"annonces_en_attente": annoncesEnAttente,
		"demandes_en_attente": demandesEnAttente,
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
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
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
			FROM Participer p
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
		httpx.JSONError(w, http.StatusInternalServerError, err.Error())
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
			"id_utilisateur": idUser,
			"nom":            nom,
			"prenom":         prenom,
			"email":          email,
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
	rows, err := database.DB.Query(
		`SELECT c.Id_Conteneurs, COALESCE(c.Localisation,''), COALESCE(c.Capacite,0), COALESCE(c.Statut,'disponible'),
			COUNT(d.Id_Demandes_conteneurs) AS nb_demandes
		FROM Conteneurs c
		LEFT JOIN Demandes_conteneurs d ON d.Id_Conteneurs = c.Id_Conteneurs AND d.Statut = 'validee'
		GROUP BY c.Id_Conteneurs ORDER BY c.Id_Conteneurs DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	conteneurs := []map[string]interface{}{}
	for rows.Next() {
		var id, capacite, nbDemandes int
		var localisation, statut string
		rows.Scan(&id, &localisation, &capacite, &statut, &nbDemandes)
		fillRate := 0
		if capacite > 0 {
			fillRate = (nbDemandes * 100) / capacite
			if fillRate > 100 {
				fillRate = 100
			}
		}
		conteneurs = append(conteneurs, map[string]interface{}{
			"id": id, "localisation": localisation, "capacite": capacite,
			"statut": statut, "nb_demandes": nbDemandes, "fill_rate": fillRate,
		})
	}
	httpx.JSONOK(w, http.StatusOK, conteneurs)
}

func AdminCreateConteneur(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/conteneurs/")
	id = strings.TrimSuffix(id, "/")

	switch r.Method {
	case http.MethodPost:
		var body struct {
			Localisation string `json:"localisation"`
			Capacite     int    `json:"capacite"`
			Statut       string `json:"statut"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if body.Statut == "" {
			body.Statut = "disponible"
		}
		result, err := database.DB.Exec(
			"INSERT INTO Conteneurs (Localisation, Capacite, Statut) VALUES (?, ?, ?)",
			body.Localisation, body.Capacite, body.Statut,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		newID, _ := result.LastInsertId()
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": newID, "message": "Conteneur créé"})

	case http.MethodPut:
		var body struct {
			Localisation string `json:"localisation"`
			Capacite     int    `json:"capacite"`
			Statut       string `json:"statut"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		_, err := database.DB.Exec(
			"UPDATE Conteneurs SET Localisation=?, Capacite=?, Statut=? WHERE Id_Conteneurs=?",
			body.Localisation, body.Capacite, body.Statut, id,
		)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur mis à jour"})

	case http.MethodDelete:
		_, err := database.DB.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs=?", id)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, err.Error())
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur supprimé"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminConteneurAction(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]

	switch r.Method {
	case http.MethodPut, http.MethodPatch:
		var body struct {
			Localisation string `json:"localisation"`
			Capacite     int    `json:"capacite"`
			Statut       string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)
		database.DB.Exec(
			"UPDATE Conteneurs SET Localisation=?, Capacite=?, Statut=? WHERE Id_Conteneurs=?",
			body.Localisation, body.Capacite, body.Statut, id,
		)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur mis à jour"})
	case http.MethodDelete:
		database.DB.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs = ?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Conteneur supprimé"})
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func AdminGetDemandes(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT d.Id_Demandes_conteneurs, COALESCE(d.Type_objet,''), COALESCE(d.Description,''), COALESCE(d.Etat_usure,''), COALESCE(d.Statut,'en_attente'), COALESCE(d.Date_demande,''),
			COALESCE(d.Prix_vente,0), COALESCE(c.Localisation,''), COALESCE(d.Code_acces,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,'')
		FROM Demandes_conteneurs d
		LEFT JOIN Conteneurs c ON c.Id_Conteneurs = d.Id_Conteneurs
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
		var typeObjet, description, etatUsure, statut, date, localisation, code, nom, prenom, email string
		var prix float64
		rows.Scan(&id, &typeObjet, &description, &etatUsure, &statut, &date, &prix, &localisation, &code, &nom, &prenom, &email)
		demandes = append(demandes, map[string]interface{}{
			"id": id, "type_objet": typeObjet, "description": description,
			"etat_usure": etatUsure, "statut": statut, "date": date,
			"prix_vente": prix, "localisation": localisation, "code_acces": code,
			"nom": nom, "prenom": prenom, "email": email,
		})
	}
	httpx.JSONOK(w, http.StatusOK, demandes)
}

func AdminGetFinances(w http.ResponseWriter, r *http.Request) {
	var totalHT, totalTTC, totalCommissions float64
	var nbFactures int
	database.DB.QueryRow("SELECT COUNT(*), COALESCE(SUM(Montant_HT),0), COALESCE(SUM(Montant_TTC),0) FROM Factures").Scan(&nbFactures, &totalHT, &totalTTC)
	database.DB.QueryRow("SELECT COALESCE(SUM(Montant),0) FROM Commissions").Scan(&totalCommissions)

	rows, _ := database.DB.Query(
		`SELECT DATE_FORMAT(Date_emission,'%Y-%m') AS mois, COALESCE(SUM(Montant_TTC),0)
		FROM Factures
		WHERE Date_emission >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
		GROUP BY mois ORDER BY mois ASC`,
	)
	caParMois := []map[string]interface{}{}
	if rows != nil {
		defer rows.Close()
		for rows.Next() {
			var mois string
			var ca float64
			rows.Scan(&mois, &ca)
			caParMois = append(caParMois, map[string]interface{}{"mois": mois, "ca": ca})
		}
	}
	statuts := map[string]int{}
	sRows, _ := database.DB.Query("SELECT COALESCE(Statut,'inconnu'), COUNT(*) FROM Factures GROUP BY Statut")
	if sRows != nil {
		defer sRows.Close()
		for sRows.Next() {
			var s string
			var c int
			sRows.Scan(&s, &c)
			statuts[s] = c
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"nb_factures":       nbFactures,
		"total_ht":          totalHT,
		"total_ttc":         totalTTC,
		"total_commissions": totalCommissions,
		"ca_par_mois":       caParMois,
		"statuts":           statuts,
	})
}

func AdminForumSujetAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Reponses WHERE Id_Sujets=?", id)
	database.DB.Exec("DELETE FROM Sujets WHERE Id_Sujets=?", id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Sujet supprimé"})
}

func AdminForumReponseAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	id := parts[len(parts)-1]
	database.DB.Exec("DELETE FROM Reponses WHERE Id_Reponses=?", id)
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
	rows, err := database.DB.Query(
		`SELECT s.Id_Sujets, s.Titre, COALESCE(s.Categorie,'general'), COALESCE(s.Statut,'ouvert'), s.Date_Creation,
			u.Nom, u.Prenom,
			COUNT(rep.Id_Reponses) AS nb_reponses
		FROM Sujets s
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		LEFT JOIN Reponses rep ON rep.Id_Sujets = s.Id_Sujets
		GROUP BY s.Id_Sujets ORDER BY s.Date_Creation DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	sujets := []map[string]interface{}{}
	for rows.Next() {
		var id, nbRep int
		var titre, cat, statut, nom, prenom string
		var date *string
		rows.Scan(&id, &titre, &cat, &statut, &date, &nom, &prenom, &nbRep)
		sujets = append(sujets, map[string]interface{}{
			"id": id, "titre": titre, "categorie": cat, "statut": statut,
			"date": date, "auteur": nom + " " + prenom, "nb_reponses": nbRep,
		})
	}
	httpx.JSONOK(w, http.StatusOK, sujets)
}

func AdminDemandeAction(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/admin/demandes/")
	parts := strings.Split(strings.TrimSuffix(path, "/"), "/")
	if len(parts) < 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Paramètres manquants")
		return
	}
	action := parts[0]
	id := parts[1]
	switch action {
	case "valider":
		code := genererCode()
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut='validee', Code_acces=? WHERE Id_Demandes_conteneurs=?", code, id)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande validée", "code_acces": code})
	case "refuser":
		database.DB.Exec("UPDATE Demandes_conteneurs SET Statut='refusee' WHERE Id_Demandes_conteneurs=?", id)
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Demande refusée"})
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
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur: "+err.Error())
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

	var idPart int
	if err := database.DB.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", uid).Scan(&idPart); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Utilisateur non particulier")
		return
	}

	switch planType {
	case "evenement":
		database.DB.Exec("DELETE FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?", idPart, itemId)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription événement effectuée"})
	case "formation":
		database.DB.Exec("DELETE FROM Participer WHERE Id_Particuliers = ? AND Id_Formations = ?", idPart, itemId)
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Désinscription formation effectuée"})
	default:
		httpx.JSONError(w, http.StatusBadRequest, "Type inconnu")
	}
}
