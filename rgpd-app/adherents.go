package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"time"
)

type Adherent struct {
	ID                int    `json:"id"`
	Civilite          string `json:"civilite"`
	Nom               string `json:"nom"`
	Prenom            string `json:"prenom"`
	Email             string `json:"email"`
	Telephone         string `json:"telephone"`
	PaysResidence     string `json:"pays_residence"`
	DateTransitSuisse string `json:"date_transit_suisse"`
	CantonSuisse      string `json:"canton_suisse"`
	MotifTransfert    string `json:"motif_transfert"`
	BaseLegale        string `json:"base_legale_transfert"`
	Statut            string `json:"statut"`
	DateCreation      string `json:"date_creation"`
	DateMaj           string `json:"date_maj"`
}

const adherentSelect = `SELECT Id_Adherent, COALESCE(Civilite,''), Nom, Prenom, Email,
	COALESCE(Telephone,''), COALESCE(Pays_Residence,''), COALESCE(Date_Transit_Suisse,''),
	COALESCE(Canton_Suisse,''), COALESCE(Motif_Transfert,''), COALESCE(Base_Legale_Transfert,''),
	Statut, COALESCE(Date_Creation,''), COALESCE(Date_Maj,'') FROM Adherents`

type scanner interface {
	Scan(dest ...any) error
}

func scanAdherent(s scanner) (Adherent, error) {
	var a Adherent
	err := s.Scan(&a.ID, &a.Civilite, &a.Nom, &a.Prenom, &a.Email, &a.Telephone,
		&a.PaysResidence, &a.DateTransitSuisse, &a.CantonSuisse, &a.MotifTransfert,
		&a.BaseLegale, &a.Statut, &a.DateCreation, &a.DateMaj)
	return a, err
}

func handleListAdherents(w http.ResponseWriter, r *http.Request) {
	rows, err := db.Query(adherentSelect + " ORDER BY Id_Adherent DESC")
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	defer rows.Close()
	list := []Adherent{}
	for rows.Next() {
		if a, e := scanAdherent(rows); e == nil {
			list = append(list, a)
		}
	}
	audit(r, "consultation_liste", "adherent", "", "")
	jsonOK(w, http.StatusOK, list)
}

func handleCreateAdherent(w http.ResponseWriter, r *http.Request) {
	var in Adherent
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		jsonError(w, http.StatusBadRequest, "données invalides")
		return
	}
	if in.Nom == "" || in.Prenom == "" || in.Email == "" {
		jsonError(w, http.StatusBadRequest, "nom, prénom et email sont obligatoires")
		return
	}
	res, err := db.Exec(
		`INSERT INTO Adherents (Civilite, Nom, Prenom, Email, Telephone, Pays_Residence, Date_Transit_Suisse, Canton_Suisse, Motif_Transfert)
		 VALUES (?, ?, ?, ?, ?, ?, NULLIF(?,''), ?, ?)`,
		in.Civilite, in.Nom, in.Prenom, in.Email, in.Telephone, in.PaysResidence,
		in.DateTransitSuisse, in.CantonSuisse, in.MotifTransfert,
	)
	if err != nil {
		jsonError(w, http.StatusBadRequest, "création impossible (email déjà présent ?)")
		return
	}
	id, _ := res.LastInsertId()
	audit(r, "creation", "adherent", strconv.FormatInt(id, 10), in.Email)
	jsonOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "adhérent créé"})
}

func handleGetAdherent(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	a, err := scanAdherent(db.QueryRow(adherentSelect+" WHERE Id_Adherent = ?", id))
	if err == sql.ErrNoRows {
		jsonError(w, http.StatusNotFound, "adhérent introuvable")
		return
	}
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	audit(r, "consultation", "adherent", id, "")
	jsonOK(w, http.StatusOK, map[string]interface{}{
		"adherent":      a,
		"consentements": loadConsents(id),
		"demandes":      loadDemandes(id),
	})
}

// handleUpdateAdherent — droit de rectification (art. 16).
func handleUpdateAdherent(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	var in Adherent
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		jsonError(w, http.StatusBadRequest, "données invalides")
		return
	}
	_, err := db.Exec(
		`UPDATE Adherents SET Civilite=?, Nom=?, Prenom=?, Email=?, Telephone=?, Pays_Residence=?, Canton_Suisse=?, Motif_Transfert=?
		 WHERE Id_Adherent=? AND Statut <> 'anonymise'`,
		in.Civilite, in.Nom, in.Prenom, in.Email, in.Telephone, in.PaysResidence, in.CantonSuisse, in.MotifTransfert, id,
	)
	if err != nil {
		jsonError(w, http.StatusBadRequest, "mise à jour impossible")
		return
	}
	audit(r, "rectification", "adherent", id, "")
	jsonOK(w, http.StatusOK, map[string]string{"message": "adhérent mis à jour"})
}

// handleEraseAdherent — droit à l'effacement (art. 17) par anonymisation
// irréversible. La ligne est conservée anonymisée pour préserver la trace
// d'audit (responsabilité, art. 5.2).
func handleEraseAdherent(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	_, err := db.Exec(
		`UPDATE Adherents
		 SET Civilite=NULL, Nom='—', Prenom='—',
		     Email=CONCAT('anonymise-', Id_Adherent, '@invalid'),
		     Telephone=NULL, Canton_Suisse=NULL, Motif_Transfert=NULL,
		     Statut='anonymise'
		 WHERE Id_Adherent=?`, id)
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "effacement impossible")
		return
	}
	_, _ = db.Exec("DELETE FROM Consentements WHERE Id_Adherent=?", id)
	audit(r, "effacement", "adherent", id, "anonymisation (art. 17)")
	jsonOK(w, http.StatusOK, map[string]string{"message": "adhérent anonymisé (droit à l'effacement)"})
}

// handleExportAdherent — droit à la portabilité (art. 20) : export complet
// des données personnelles dans un format structuré et lisible par machine.
func handleExportAdherent(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	if _, err := strconv.Atoi(id); err != nil {
		jsonError(w, http.StatusBadRequest, "id invalide")
		return
	}
	a, err := scanAdherent(db.QueryRow(adherentSelect+" WHERE Id_Adherent = ?", id))
	if err == sql.ErrNoRows {
		jsonError(w, http.StatusNotFound, "adhérent introuvable")
		return
	}
	if err != nil {
		jsonError(w, http.StatusInternalServerError, "erreur base")
		return
	}
	audit(r, "export_portabilite", "adherent", id, "")
	w.Header().Set("Content-Disposition", "attachment; filename=adherent-"+id+".json")
	jsonOK(w, http.StatusOK, map[string]interface{}{
		"format":        "RGPD portabilité (art. 20)",
		"genere_le":     time.Now().Format(time.RFC3339),
		"adherent":      a,
		"consentements": loadConsents(id),
		"demandes":      loadDemandes(id),
	})
}
