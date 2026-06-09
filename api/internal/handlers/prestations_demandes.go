package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
	"upcycleconnect/internal/services"
)

// PrestationsDemandes gere les demandes de prestation du particulier connecte.
//   POST /api/prestations/demandes  -> creer une demande
//   GET  /api/prestations/demandes  -> lister MES demandes
// L'identite vient toujours du JWT (jamais du corps ni de l'URL).
func PrestationsDemandes(w http.ResponseWriter, r *http.Request) {
	uid := middleware.GetUserID(r)
	if uid == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}

	switch r.Method {
	case http.MethodPost:
		creerDemandePrestation(w, r, uid)
	case http.MethodGet:
		listerMesDemandesPrestations(w, uid)
	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func creerDemandePrestation(w http.ResponseWriter, r *http.Request, uid int) {
	var body struct {
		NomObjet     string `json:"nom_objet"`
		Categorie    string `json:"categorie"`
		TypeObjet    string `json:"type_objet"`
		Etat         string `json:"etat"`
		Description  string `json:"description"`
		Localisation string `json:"localisation"`
		Budget       string `json:"budget"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	body.NomObjet = strings.TrimSpace(body.NomObjet)
	if body.NomObjet == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Le nom de l'objet est requis.")
		return
	}
	res, err := database.DB.Exec(
		`INSERT INTO Demandes_prestations
			(Nom_objet, Categorie, Type_objet, Etat, Description, Localisation, Budget, Statut, Date_creation, Id_Utilisateurs)
		 VALUES (?, ?, ?, ?, ?, ?, ?, 'ouverte', NOW(), ?)`,
		body.NomObjet, body.Categorie, body.TypeObjet, body.Etat, body.Description, body.Localisation, body.Budget, uid,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	id, _ := res.LastInsertId()

	// Email de confirmation au demandeur (fail-safe : ignore si SMTP non configure).
	go func(userID int, objet string) {
		var email, prenom string
		if e := database.DB.QueryRow("SELECT Email, COALESCE(Prenom,'') FROM Utilisateurs WHERE Id_Utilisateurs = ?", userID).Scan(&email, &prenom); e == nil && email != "" {
			services.SendGenericEmail(email, "Votre demande de prestation est enregistree",
				"Bonjour "+prenom+",\n\nVotre demande \""+objet+"\" a bien ete enregistree sur UpcycleConnect. Un prestataire pourra vous contacter prochainement.\n\nL'equipe UpcycleConnect")
		}
	}(uid, body.NomObjet)

	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "statut": "ouverte"})
}

func listerMesDemandesPrestations(w http.ResponseWriter, uid int) {
	rows, err := database.DB.Query(
		`SELECT Id_Demandes_prestations, Nom_objet, COALESCE(Categorie,''), COALESCE(Type_objet,''),
			COALESCE(Etat,''), COALESCE(Description,''), COALESCE(Localisation,''), COALESCE(Budget,''),
			COALESCE(Statut,'ouverte'), COALESCE(DATE_FORMAT(Date_creation,'%d/%m/%Y'),'')
		 FROM Demandes_prestations WHERE Id_Utilisateurs = ? ORDER BY Id_Demandes_prestations DESC`, uid,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	type Demande struct {
		ID           int    `json:"id"`
		NomObjet     string `json:"nom_objet"`
		Categorie    string `json:"categorie"`
		TypeObjet    string `json:"type_objet"`
		Etat         string `json:"etat"`
		Description  string `json:"description"`
		Localisation string `json:"localisation"`
		Budget       string `json:"budget"`
		Statut       string `json:"statut"`
		Date         string `json:"date"`
	}
	demandes := []Demande{}
	for rows.Next() {
		var d Demande
		rows.Scan(&d.ID, &d.NomObjet, &d.Categorie, &d.TypeObjet, &d.Etat, &d.Description, &d.Localisation, &d.Budget, &d.Statut, &d.Date)
		demandes = append(demandes, d)
	}
	httpx.JSONOK(w, http.StatusOK, demandes)
}
