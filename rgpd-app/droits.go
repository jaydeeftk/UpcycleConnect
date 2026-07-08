package main

import (
	"encoding/json"
	"net/http"
	"strconv"
)

type Consentement struct {
	ID               int    `json:"id"`
	Finalite         string `json:"finalite"`
	BaseLegale       string `json:"base_legale"`
	Consenti         bool   `json:"consenti"`
	DateConsentement string `json:"date_consentement"`
	DateRetrait      string `json:"date_retrait"`
	VersionPolitique string `json:"version_politique"`
}

func loadConsents(id string) []Consentement {
	list := []Consentement{}
	rows, err := db.Query(
		`SELECT Id_Consentement, Finalite, Base_Legale, Consenti,
		        COALESCE(Date_Consentement,''), COALESCE(Date_Retrait,''), COALESCE(Version_Politique,'')
		 FROM Consentements WHERE Id_Adherent = ? ORDER BY Id_Consentement DESC`, id)
	if err != nil {
		return list
	}
	defer rows.Close()
	for rows.Next() {
		var c Consentement
		var consenti int
		if err := rows.Scan(&c.ID, &c.Finalite, &c.BaseLegale, &consenti,
			&c.DateConsentement, &c.DateRetrait, &c.VersionPolitique); err == nil {
			c.Consenti = consenti == 1
			list = append(list, c)
		}
	}
	return list
}

// handleAddConsent enregistre un recueil ou un retrait de consentement.
// Le retrait (art. 7.3) doit être aussi simple que le recueil : il suffit
// d'envoyer consenti=false sur la même finalité.
func handleAddConsent(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	var in struct {
		Finalite         string `json:"finalite"`
		BaseLegale       string `json:"base_legale"`
		Consenti         bool   `json:"consenti"`
		VersionPolitique string `json:"version_politique"`
	}
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		jsonError(w, http.StatusBadRequest, "données invalides")
		return
	}
	if in.Finalite == "" {
		jsonError(w, http.StatusBadRequest, "la finalité est obligatoire")
		return
	}
	baseLegale := in.BaseLegale
	if baseLegale == "" {
		baseLegale = "Consentement"
	}
	consenti := 0
	if in.Consenti {
		consenti = 1
	}
	res, err := db.Exec(
		`INSERT INTO Consentements
		     (Id_Adherent, Finalite, Base_Legale, Consenti, Date_Consentement, Date_Retrait, Version_Politique)
		 VALUES
		     (?, ?, ?, ?, CASE WHEN ?=1 THEN NOW() ELSE NULL END, CASE WHEN ?=0 THEN NOW() ELSE NULL END, NULLIF(?,''))`,
		id, in.Finalite, baseLegale, consenti, consenti, consenti, in.VersionPolitique,
	)
	if err != nil {
		jsonError(w, http.StatusBadRequest, "enregistrement du consentement impossible")
		return
	}
	cid, _ := res.LastInsertId()
	action := "consentement_retrait"
	if in.Consenti {
		action = "consentement_recueil"
	}
	audit(r, action, "consentement", id, in.Finalite)
	jsonOK(w, http.StatusCreated, map[string]interface{}{"id": cid, "message": "consentement enregistré"})
}

type Demande struct {
	ID             int    `json:"id"`
	IdAdherent     int    `json:"id_adherent"`
	NomAdherent    string `json:"nom_adherent,omitempty"`
	TypeDroit      string `json:"type_droit"`
	Statut         string `json:"statut"`
	DateDemande    string `json:"date_demande"`
	DateEcheance   string `json:"date_echeance"`
	DateTraitement string `json:"date_traitement"`
	TraitePar      string `json:"traite_par"`
	Commentaire    string `json:"commentaire"`
}

// typesDroit borne les valeurs acceptées à l'ENUM du schéma : on rejette
// côté applicatif avant d'atteindre la base (défense en profondeur).
var typesDroit = map[string]bool{
	"acces":         true,
	"rectification": true,
	"effacement":    true,
	"portabilite":   true,
	"limitation":    true,
	"opposition":    true,
}

func loadDemandes(id string) []Demande {
	list := []Demande{}
	rows, err := db.Query(
		`SELECT Id_Demande, Id_Adherent, Type_Droit, Statut,
		        COALESCE(Date_Demande,''), COALESCE(Date_Echeance,''),
		        COALESCE(Date_Traitement,''), COALESCE(Traite_Par,''), COALESCE(Commentaire,'')
		 FROM Demandes_Droits WHERE Id_Adherent = ? ORDER BY Id_Demande DESC`, id)
	if err != nil {
		return list
	}
	defer rows.Close()
	for rows.Next() {
		var d Demande
		if err := rows.Scan(&d.ID, &d.IdAdherent, &d.TypeDroit, &d.Statut,
			&d.DateDemande, &d.DateEcheance, &d.DateTraitement, &d.TraitePar, &d.Commentaire); err == nil {
			list = append(list, d)
		}
	}
	return list
}

// handleCreateDemande ouvre une demande de droit avec échéance légale à
// J+30 (art. 12.3 RGPD), calculée par la base pour rester en heure serveur.
func handleCreateDemande(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	var in struct {
		TypeDroit   string `json:"type_droit"`
		Commentaire string `json:"commentaire"`
	}
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		jsonError(w, http.StatusBadRequest, "données invalides")
		return
	}
	if !typesDroit[in.TypeDroit] {
		jsonError(w, http.StatusBadRequest, "type de droit invalide")
		return
	}
	res, err := db.Exec(
		`INSERT INTO Demandes_Droits (Id_Adherent, Type_Droit, Date_Echeance, Commentaire)
		 VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULLIF(?,''))`,
		id, in.TypeDroit, in.Commentaire,
	)
	if err != nil {
		jsonError(w, http.StatusBadRequest, "création de la demande impossible")
		return
	}
	did, _ := res.LastInsertId()
	audit(r, "demande_droit", "demande", strconv.FormatInt(did, 10), in.TypeDroit)
	jsonOK(w, http.StatusCreated, map[string]interface{}{
		"id": did, "message": "demande enregistrée", "echeance": "J+30 (art. 12.3 RGPD)",
	})
}

// handleListDemandes liste toutes les demandes, les plus urgentes (échéance
// la plus proche) en tête, avec le nom de l'adhérent concerné.
func handleListDemandes(w http.ResponseWriter, r *http.Request) {
	rows, err := db.Query(
		`SELECT d.Id_Demande, d.Id_Adherent, CONCAT(a.Prenom, ' ', a.Nom),
		        d.Type_Droit, d.Statut, COALESCE(d.Date_Demande,''),
		        COALESCE(d.Date_Echeance,''), COALESCE(d.Date_Traitement,''),
		        COALESCE(d.Traite_Par,''), COALESCE(d.Commentaire,'')
		 FROM Demandes_Droits d JOIN Adherents a ON a.Id_Adherent = d.Id_Adherent
		 ORDER BY d.Date_Echeance ASC, d.Id_Demande DESC`)
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	defer rows.Close()
	list := []Demande{}
	for rows.Next() {
		var d Demande
		if err := rows.Scan(&d.ID, &d.IdAdherent, &d.NomAdherent, &d.TypeDroit, &d.Statut,
			&d.DateDemande, &d.DateEcheance, &d.DateTraitement, &d.TraitePar, &d.Commentaire); err == nil {
			list = append(list, d)
		}
	}
	audit(r, "consultation_demandes", "demande", "", "")
	jsonOK(w, http.StatusOK, list)
}

type Traitement struct {
	ID                int    `json:"id"`
	Nom               string `json:"nom"`
	Finalite          string `json:"finalite"`
	BaseLegale        string `json:"base_legale"`
	CategoriesDonnees string `json:"categories_donnees"`
	Destinataires     string `json:"destinataires"`
	TransfertHorsUE   bool   `json:"transfert_hors_ue"`
	PaysDestinataire  string `json:"pays_destinataire"`
	GarantieTransfert string `json:"garantie_transfert"`
	DureeConservation string `json:"duree_conservation"`
	MesuresSecurite   string `json:"mesures_securite"`
	DateCreation      string `json:"date_creation"`
}

func handleRegistre(w http.ResponseWriter, r *http.Request) {
	rows, err := db.Query(
		`SELECT Id_Traitement, Nom, Finalite, Base_Legale, COALESCE(Categories_Donnees,''),
		        COALESCE(Destinataires,''), Transfert_Hors_UE, COALESCE(Pays_Destinataire,''),
		        COALESCE(Garantie_Transfert,''), COALESCE(Duree_Conservation,''),
		        COALESCE(Mesures_Securite,''), COALESCE(Date_Creation,'')
		 FROM Registre_Traitements ORDER BY Id_Traitement`)
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	defer rows.Close()
	list := []Traitement{}
	for rows.Next() {
		var t Traitement
		var horsUE int
		if err := rows.Scan(&t.ID, &t.Nom, &t.Finalite, &t.BaseLegale, &t.CategoriesDonnees,
			&t.Destinataires, &horsUE, &t.PaysDestinataire, &t.GarantieTransfert,
			&t.DureeConservation, &t.MesuresSecurite, &t.DateCreation); err == nil {
			t.TransfertHorsUE = horsUE == 1
			list = append(list, t)
		}
	}
	audit(r, "consultation_registre", "registre", "", "")
	jsonOK(w, http.StatusOK, list)
}

type JournalEntry struct {
	ID         int64  `json:"id"`
	Acteur     string `json:"acteur"`
	Identite   string `json:"identite_teleport"`
	Action     string `json:"action"`
	CibleType  string `json:"cible_type"`
	CibleID    string `json:"cible_id"`
	Details    string `json:"details"`
	AdresseIP  string `json:"adresse_ip"`
	Horodatage string `json:"horodatage"`
}

func handleJournal(w http.ResponseWriter, r *http.Request) {
	rows, err := db.Query(
		`SELECT Id_Journal, Acteur, COALESCE(Identite_Teleport,''), Action,
		        COALESCE(Cible_Type,''), COALESCE(Cible_Id,''), COALESCE(Details,''),
		        COALESCE(Adresse_IP,''), COALESCE(Horodatage,'')
		 FROM Journal_Acces ORDER BY Id_Journal DESC LIMIT 200`)
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	defer rows.Close()
	list := []JournalEntry{}
	for rows.Next() {
		var j JournalEntry
		if err := rows.Scan(&j.ID, &j.Acteur, &j.Identite, &j.Action, &j.CibleType,
			&j.CibleID, &j.Details, &j.AdresseIP, &j.Horodatage); err == nil {
			list = append(list, j)
		}
	}

	audit(r, "consultation_journal", "journal", "", "")
	jsonOK(w, http.StatusOK, list)
}
