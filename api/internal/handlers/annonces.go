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

// annonceSvc — cas d'usage du cycle de vie des annonces. Sans état, partagé.
var annonceSvc = services.NewAnnonceService()

// GetAnnonces : place de marché publique (annonces publiées uniquement, sans PII).
func GetAnnonces(w http.ResponseWriter, r *http.Request) {
	liste, err := annonceSvc.ListerPubliees()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// GetAnnonceDispatch route les sous-chemins de /api/annonces/. Les ACTIONS
// (create/annuler/vendre) et la liste privée (user) exigent un JWT — l'identité
// vient du token (sub), jamais de l'URL ni du corps. La fiche est à identité
// optionnelle : la visibilité et les actions en sont dérivées côté serveur.
func GetAnnonceDispatch(w http.ResponseWriter, r *http.Request) {
	path := strings.TrimPrefix(r.URL.Path, "/api/annonces/")
	parts := strings.Split(strings.Trim(path, "/"), "/")

	if parts[0] == "create" {
		middleware.JWTAuth(CreateAnnonce)(w, r)
		return
	}
	if parts[0] == "user" {
		middleware.JWTAuth(GetAnnoncesUser)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "annuler" {
		middleware.JWTAuth(AnnulerAnnonce)(w, r)
		return
	}
	if len(parts) >= 2 && parts[1] == "vendre" {
		middleware.JWTAuth(VendreAnnonce)(w, r)
		return
	}
	middleware.OptionalJWT(ficheAnnonce)(w, r)
}

// CreateAnnonce : handler fin. Identité = JWT (sub) ; le champ user_id du corps
// est ignoré. La validation (titre, cohérence type↔prix) et l'insertion vivent
// dans le service.
func CreateAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	var body struct {
		Titre       string  `json:"titre"`
		Description string  `json:"description"`
		Categorie   string  `json:"categorie"`
		Etat        string  `json:"etat"`
		TypeAnnonce string  `json:"type_annonce"`
		Prix        float64 `json:"prix"`
		Ville       string  `json:"ville"`
		CodePostal  string  `json:"code_postal"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := annonceSvc.CreerAnnonce(middleware.GetUserID(r), services.CreationAnnonceInput{
		Titre: body.Titre, Description: body.Description, Categorie: body.Categorie,
		Etat: body.Etat, Type: body.TypeAnnonce, Prix: body.Prix,
		Ville: body.Ville, CodePostal: body.CodePostal,
	})
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{
		"id":      id,
		"message": "Annonce soumise avec succès, en attente de validation",
	})
}

// ficheAnnonce sert la fiche en lecture. L'identité (token si présent) ne sert
// qu'à dériver la visibilité, est_proprietaire, l'email et allowed_actions ;
// elle n'autorise aucune écriture.
func ficheAnnonce(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	dto, err := annonceSvc.FicheAnnonce(middleware.GetUserID(r), middleware.GetRole(r), id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

// GetAnnoncesUser : liste privée de l'utilisateur AUTHENTIFIÉ. L'identifiant
// éventuel en fin d'URL est ignoré — l'identité vient du JWT, fermant la fuite
// « lister les annonces de n'importe qui ».
func GetAnnoncesUser(w http.ResponseWriter, r *http.Request) {
	liste, err := annonceSvc.MesAnnonces(middleware.GetUserID(r))
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// AnnulerAnnonce : retrait DOUX par le propriétaire (en_attente|validee ->
// retiree). Identité = JWT (sub) ; le corps est ignoré.
func AnnulerAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID annonce manquant")
		return
	}
	if err := annonceSvc.RetirerAnnonce(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce retirée"})
}

// VendreAnnonce : transition propriétaire validee -> vendue. Identité = JWT (sub).
func VendreAnnonce(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "ID annonce manquant")
		return
	}
	if err := annonceSvc.MarquerVendue(middleware.GetUserID(r), id); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce marquée comme vendue"})
}

func AdminGetAnnonces(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT a.Id_Annonces, COALESCE(a.Titre,''), COALESCE(a.Statut,'en_attente'),
			COALESCE(a.Date_publication,''), COALESCE(a.Categorie,''),
			COALESCE(a.Description,''), COALESCE(a.Prix,0), COALESCE(a.Ville,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,'')
		FROM Annonces a
		JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY a.Id_Annonces DESC`,
	)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	defer rows.Close()

	annonces := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var prix float64
		var titre, statut, date, categorie, desc, ville, nom, prenom, email string
		rows.Scan(&id, &titre, &statut, &date, &categorie, &desc, &prix, &ville, &nom, &prenom, &email)
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "statut": statut, "date_publication": date,
			"categorie": categorie, "description": desc, "prix": prix, "ville": ville,
			"nom": nom, "prenom": prenom, "email": email,
		})
	}
	httpx.JSONOK(w, http.StatusOK, annonces)
}

// AdminAnnonceAction : actions admin sur une annonce. Le PUT ne reçoit plus un
// statut LIBRE (qui violait chk_annonces_statut en 500) : on mappe l'intention
// (validee / refusee|rejetee) vers une transition canonique gardée par le domaine.
func AdminAnnonceAction(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/admin/annonces/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch r.Method {
	case http.MethodPut:
		var body struct {
			Statut string `json:"statut"`
		}
		json.NewDecoder(r.Body).Decode(&body)

		var serr error
		switch body.Statut {
		case "validee":
			serr = annonceSvc.ValiderAnnonce(id)
		case "refusee", "rejetee":
			serr = annonceSvc.RefuserAnnonce(id)
		default:
			httpx.JSONError(w, http.StatusUnprocessableEntity, "Transition non supportée")
			return
		}
		if serr != nil {
			httpx.WriteError(w, serr)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Statut mis à jour"})

	case http.MethodDelete:
		if err := annonceSvc.SupprimerAnnonce(id); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]string{"message": "Annonce supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}
