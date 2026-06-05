package repository

import (
	"database/sql"
	"errors"

	"github.com/go-sql-driver/mysql"
)

// FacturationRepo isole l'accès SQL du vertical Contrat / Abonnement / Facture /
// Commission / Paiement. Aucune règle métier ici : uniquement lecture/écriture,
// chaque méthode acceptant un Querier (DB ou Tx) pour fonctionner dans ou hors
// transaction.
type FacturationRepo struct{}

// codeMySQLDuplicate : ER_DUP_ENTRY, levé sur violation d'unicité (Numero_facture).
const codeFacturationDuplicate = 1062

// EstViolationUnicite reconnaît une collision de clé unique, pour distinguer un
// numéro de facture déjà pris (à régénérer) d'une vraie erreur SQL.
func (FacturationRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeFacturationDuplicate
}

// ===========================================================================
// Contrat
// ===========================================================================

type ContratAdminLigne struct {
	ID            int
	DateSignature string
	DateDebut     string
	DateFin       string
	Type          string
	Statut        string
	Nom           string
	Prenom        string
	Entreprise    string
}

type ContratProLigne struct {
	ID        int
	Type      string
	Statut    string
	DateDebut string
	DateFin   string
}

type ContratCreation struct {
	Type            string
	DateSignature   string
	DateDebut       string
	DateFin         string
	Statut          string
	IdProfessionnel int
}

func (FacturationRepo) AdminListerContrats(q Querier) ([]ContratAdminLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Contrats, COALESCE(c.Date_signature,''), COALESCE(c.Date_debut,''),
			COALESCE(c.Date_fin,''), COALESCE(c.Type,''), COALESCE(c.Statut,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(p.Nom_Entreprise,'')
		FROM Contrats c
		LEFT JOIN Professionnels_artisans p ON p.Id_Professionnels = c.Id_Professionnels
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY c.Date_debut DESC, c.Id_Contrats DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ContratAdminLigne{}
	for rows.Next() {
		var c ContratAdminLigne
		if err := rows.Scan(&c.ID, &c.DateSignature, &c.DateDebut, &c.DateFin, &c.Type,
			&c.Statut, &c.Nom, &c.Prenom, &c.Entreprise); err != nil {
			return nil, err
		}
		out = append(out, c)
	}
	return out, rows.Err()
}

// ContratsDuProfessionnel lit les contrats d'UN professionnel via la VRAIE clé
// (Id_Professionnels). Corrige la requête historique qui interrogeait des
// colonnes inexistantes (Type_contrat, Montant, Id_Utilisateurs).
func (FacturationRepo) ContratsDuProfessionnel(q Querier, idProfessionnel int) ([]ContratProLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Contrats, COALESCE(c.Type,''), COALESCE(c.Statut,''),
			COALESCE(c.Date_debut,''), COALESCE(c.Date_fin,'')
		FROM Contrats c
		WHERE c.Id_Professionnels = ?
		ORDER BY c.Id_Contrats DESC`, idProfessionnel,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ContratProLigne{}
	for rows.Next() {
		var c ContratProLigne
		if err := rows.Scan(&c.ID, &c.Type, &c.Statut, &c.DateDebut, &c.DateFin); err != nil {
			return nil, err
		}
		out = append(out, c)
	}
	return out, rows.Err()
}

func (FacturationRepo) ProfessionnelExiste(q Querier, idProfessionnel int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT EXISTS(SELECT 1 FROM Professionnels_artisans WHERE Id_Professionnels = ?)",
		idProfessionnel,
	).Scan(&n)
	return n != 0, err
}

// IdProfessionnel mappe l'utilisateur authentifié vers son Id_Professionnels.
// sql.ErrNoRows => l'appelant n'est pas un professionnel (403 décidé en service).
func (FacturationRepo) IdProfessionnel(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

// PrixFormation / PrixEvenement : montant et intitulé d'un article payable, lus
// EN BASE (jamais transmis par le client) — socle anti-falsification du checkout.
func (FacturationRepo) PrixFormation(q Querier, idFormation int) (float64, string, error) {
	var prix float64
	var titre string
	err := q.QueryRow(
		"SELECT COALESCE(Prix,0), COALESCE(Titre,'') FROM Formations WHERE Id_Formations = ?",
		idFormation,
	).Scan(&prix, &titre)
	return prix, titre, err
}

func (FacturationRepo) PrixEvenement(q Querier, idEvenement int) (float64, string, error) {
	var prix float64
	var titre string
	err := q.QueryRow(
		"SELECT COALESCE(Prix,0), COALESCE(Titre,'') FROM Evenements WHERE Id_Evenements = ?",
		idEvenement,
	).Scan(&prix, &titre)
	return prix, titre, err
}

func (FacturationRepo) CreerContrat(q Querier, c ContratCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Contrats (Type, Date_signature, Date_debut, Date_fin, Statut, Id_Professionnels)
		 VALUES (?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?)`,
		c.Type, c.DateSignature, c.DateDebut, c.DateFin, c.Statut, c.IdProfessionnel,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

// ContratStatutPourMAJ verrouille la ligne (FOR UPDATE) et renvoie son statut
// courant : base de toute transition concurrente-sûre.
func (FacturationRepo) ContratStatutPourMAJ(q Querier, idContrat int) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Contrats WHERE Id_Contrats = ? FOR UPDATE",
		idContrat,
	).Scan(&statut)
	return statut, err
}

func (FacturationRepo) MajStatutContrat(q Querier, idContrat int, statut string) error {
	_, err := q.Exec("UPDATE Contrats SET Statut = ? WHERE Id_Contrats = ?", statut, idContrat)
	return err
}

// MajContrat met à jour échéance et type. Un champ vide ne SUPPRIME pas la valeur
// existante (COALESCE/NULLIF) : une mise à jour partielle reste non destructive.
func (FacturationRepo) MajContrat(q Querier, idContrat int, dateFin, typ string) (int64, error) {
	res, err := q.Exec(
		"UPDATE Contrats SET Date_fin = NULLIF(?,''), Type = COALESCE(NULLIF(?,''), Type) WHERE Id_Contrats = ?",
		dateFin, typ, idContrat,
	)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

func (FacturationRepo) SupprimerContrat(q Querier, idContrat int) (int64, error) {
	res, err := q.Exec("DELETE FROM Contrats WHERE Id_Contrats = ?", idContrat)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

// ===========================================================================
// Facture
// ===========================================================================

type FactureLigne struct {
	ID           int
	Numero       string
	DateEmission string
	MontantHT    float64
	TVA          float64
	MontantTTC   float64
	Statut       string
	Type         string
	Nom          string
	Prenom       string
}

type FactureCreation struct {
	Numero        string
	DateEcheance  string
	MontantHT     float64
	TVA           float64
	MontantTTC    float64
	Statut        string
	Type          string
	IdUtilisateur int
}

type LigneFactureCreation struct {
	Description    string
	Quantite       int
	PrixUnitaireHT float64
	TotalHT        float64
	IdFacture      int64
	IdFormation    *int
	IdEvenement    *int
}

func scanFactures(rows *sql.Rows) ([]FactureLigne, error) {
	defer rows.Close()
	out := []FactureLigne{}
	for rows.Next() {
		var f FactureLigne
		if err := rows.Scan(&f.ID, &f.Numero, &f.DateEmission, &f.MontantHT, &f.TVA,
			&f.MontantTTC, &f.Statut, &f.Type, &f.Nom, &f.Prenom); err != nil {
			return nil, err
		}
		out = append(out, f)
	}
	return out, rows.Err()
}

func (FacturationRepo) AdminListerFactures(q Querier) ([]FactureLigne, error) {
	rows, err := q.Query(
		`SELECT f.Id_Facture, COALESCE(f.Numero_facture,''), COALESCE(f.Date_emission,''),
			COALESCE(f.Montant_HT,0), COALESCE(f.TVA,0), COALESCE(f.Montant_TTC,0),
			COALESCE(f.Statut,''), COALESCE(f.Type,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Factures f
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		ORDER BY f.Date_emission DESC, f.Id_Facture DESC`,
	)
	if err != nil {
		return nil, err
	}
	return scanFactures(rows)
}

func (FacturationRepo) FactureParID(q Querier, idFacture int) (FactureLigne, error) {
	var f FactureLigne
	err := q.QueryRow(
		`SELECT f.Id_Facture, COALESCE(f.Numero_facture,''), COALESCE(f.Date_emission,''),
			COALESCE(f.Montant_HT,0), COALESCE(f.TVA,0), COALESCE(f.Montant_TTC,0),
			COALESCE(f.Statut,''), COALESCE(f.Type,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Factures f
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		WHERE f.Id_Facture = ?`, idFacture,
	).Scan(&f.ID, &f.Numero, &f.DateEmission, &f.MontantHT, &f.TVA, &f.MontantTTC,
		&f.Statut, &f.Type, &f.Nom, &f.Prenom)
	return f, err
}

func (FacturationRepo) CreerFacture(q Querier, f FactureCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Factures
			(Numero_facture, Date_emission, Date_echeance, Montant_HT, TVA, Montant_TTC, Statut, Type, Id_Utilisateurs)
		 VALUES (?, NOW(), NULLIF(?,''), ?, ?, ?, ?, ?, ?)`,
		f.Numero, f.DateEcheance, f.MontantHT, f.TVA, f.MontantTTC, f.Statut, f.Type, f.IdUtilisateur,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (FacturationRepo) CreerLigneFacture(q Querier, l LigneFactureCreation) error {
	_, err := q.Exec(
		`INSERT INTO Lignes_Facture
			(Description, Quantite, Prix_unitaire_HT, Total_HT, Id_Facture, Id_Formations, Id_Evenements)
		 VALUES (?, ?, ?, ?, ?, ?, ?)`,
		l.Description, l.Quantite, l.PrixUnitaireHT, l.TotalHT, l.IdFacture, l.IdFormation, l.IdEvenement,
	)
	return err
}

// ===========================================================================
// Paiement
// ===========================================================================

type PaiementLigne struct {
	ID      int
	Montant float64
	Statut  string
	Methode string
	Date    string
	Facture string
}

type PaiementCreation struct {
	Montant         float64
	Statut          string
	Methode         string
	ReferenceStripe string
	IdFacture       int64
	IdUtilisateur   int
}

func (FacturationRepo) PaiementsDeLUtilisateur(q Querier, idUtilisateur int) ([]PaiementLigne, error) {
	rows, err := q.Query(
		`SELECT p.Id_Paiements, COALESCE(p.Montant,0), COALESCE(p.Statut,''),
			COALESCE(p.Methode,''), COALESCE(p.Date_,''), COALESCE(f.Numero_facture,'')
		FROM Paiements p
		LEFT JOIN Factures f ON f.Id_Facture = p.Id_Facture
		WHERE p.Id_Utilisateurs = ?
		ORDER BY p.Id_Paiements DESC`, idUtilisateur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []PaiementLigne{}
	for rows.Next() {
		var p PaiementLigne
		if err := rows.Scan(&p.ID, &p.Montant, &p.Statut, &p.Methode, &p.Date, &p.Facture); err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}

func (FacturationRepo) CreerPaiement(q Querier, p PaiementCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Paiements (Date_, Montant, Statut, Methode, Reference_stripe, Id_Facture, Id_Utilisateurs)
		 VALUES (NOW(), ?, ?, ?, NULLIF(?,''), ?, ?)`,
		p.Montant, p.Statut, p.Methode, p.ReferenceStripe, p.IdFacture, p.IdUtilisateur,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

// PaiementReferenceExiste assure l'idempotence du rapprochement Stripe : une même
// référence de session ne crée jamais deux paiements.
func (FacturationRepo) PaiementReferenceExiste(q Querier, reference string) (bool, error) {
	if reference == "" {
		return false, nil
	}
	var n int
	err := q.QueryRow(
		"SELECT EXISTS(SELECT 1 FROM Paiements WHERE Reference_stripe = ?)", reference,
	).Scan(&n)
	return n != 0, err
}

// UtilisateurAPayeFormation : existe-t-il un paiement 'paye' de cet utilisateur
// dont une ligne de facture porte cette formation ? Base de la garde 402.
func (FacturationRepo) UtilisateurAPayeFormation(q Querier, idUtilisateur, idFormation int) (bool, error) {
	var n int
	err := q.QueryRow(
		`SELECT EXISTS(
			SELECT 1 FROM Paiements p
			JOIN Lignes_Facture l ON l.Id_Facture = p.Id_Facture
			WHERE p.Id_Utilisateurs = ? AND p.Statut = 'paye' AND l.Id_Formations = ?
		)`, idUtilisateur, idFormation,
	).Scan(&n)
	return n != 0, err
}

func (FacturationRepo) UtilisateurAPayeEvenement(q Querier, idUtilisateur, idEvenement int) (bool, error) {
	var n int
	err := q.QueryRow(
		`SELECT EXISTS(
			SELECT 1 FROM Paiements p
			JOIN Lignes_Facture l ON l.Id_Facture = p.Id_Facture
			WHERE p.Id_Utilisateurs = ? AND p.Statut = 'paye' AND l.Id_Evenements = ?
		)`, idUtilisateur, idEvenement,
	).Scan(&n)
	return n != 0, err
}

// ===========================================================================
// Abonnement
// ===========================================================================

type AbonnementLigne struct {
	ID        string
	Type      string
	Statut    string
	Prix      float64
	DateDebut string
	DateFin   string
}

type AbonnementCreation struct {
	ID        string
	Type      string
	Prix      float64
	DateDebut string
	DateFin   string
	Statut    string
}

func (FacturationRepo) AdminListerAbonnements(q Querier) ([]AbonnementLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Abonnement, COALESCE(Type,''), COALESCE(Statut,''),
			COALESCE(Prix,0), COALESCE(Date_Debut,''), COALESCE(Date_Fin,'')
		FROM Abonnement ORDER BY Id_Abonnement`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []AbonnementLigne{}
	for rows.Next() {
		var a AbonnementLigne
		if err := rows.Scan(&a.ID, &a.Type, &a.Statut, &a.Prix, &a.DateDebut, &a.DateFin); err != nil {
			return nil, err
		}
		out = append(out, a)
	}
	return out, rows.Err()
}

func (FacturationRepo) CreerAbonnement(q Querier, a AbonnementCreation) error {
	_, err := q.Exec(
		`INSERT INTO Abonnement (Id_Abonnement, Type, Prix, Date_Debut, Date_Fin, Statut)
		 VALUES (?, ?, ?, NULLIF(?,''), NULLIF(?,''), ?)`,
		a.ID, a.Type, a.Prix, a.DateDebut, a.DateFin, a.Statut,
	)
	return err
}

func (FacturationRepo) AbonnementStatutPourMAJ(q Querier, id string) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Abonnement WHERE Id_Abonnement = ? FOR UPDATE", id,
	).Scan(&statut)
	return statut, err
}

func (FacturationRepo) MajStatutAbonnement(q Querier, id, statut string) error {
	_, err := q.Exec("UPDATE Abonnement SET Statut = ? WHERE Id_Abonnement = ?", statut, id)
	return err
}

func (FacturationRepo) SupprimerAbonnement(q Querier, id string) (int64, error) {
	res, err := q.Exec("DELETE FROM Abonnement WHERE Id_Abonnement = ?", id)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

// ===========================================================================
// Finances (agrégat dashboard admin)
// ===========================================================================

type MoisCA struct {
	Mois string  `json:"mois"`
	CA   float64 `json:"ca"`
}

type FinancesAgregat struct {
	NbFactures       int            `json:"nb_factures"`
	TotalHT          float64        `json:"total_ht"`
	TotalTTC         float64        `json:"total_ttc"`
	TotalCommissions float64        `json:"total_commissions"`
	CAParMois        []MoisCA       `json:"ca_par_mois"`
	Statuts          map[string]int `json:"statuts"`
}

func (FacturationRepo) AgregatFinances(q Querier) (FinancesAgregat, error) {
	agg := FinancesAgregat{CAParMois: []MoisCA{}, Statuts: map[string]int{}}

	if err := q.QueryRow(
		"SELECT COUNT(*), COALESCE(SUM(Montant_HT),0), COALESCE(SUM(Montant_TTC),0) FROM Factures",
	).Scan(&agg.NbFactures, &agg.TotalHT, &agg.TotalTTC); err != nil {
		return agg, err
	}
	if err := q.QueryRow(
		"SELECT COALESCE(SUM(Montant),0) FROM Commissions",
	).Scan(&agg.TotalCommissions); err != nil {
		return agg, err
	}

	rows, err := q.Query(
		`SELECT DATE_FORMAT(Date_emission,'%Y-%m') AS mois, COALESCE(SUM(Montant_TTC),0)
		FROM Factures
		WHERE Date_emission >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
		GROUP BY mois ORDER BY mois ASC`,
	)
	if err != nil {
		return agg, err
	}
	for rows.Next() {
		var m MoisCA
		if err := rows.Scan(&m.Mois, &m.CA); err != nil {
			rows.Close()
			return agg, err
		}
		agg.CAParMois = append(agg.CAParMois, m)
	}
	rows.Close()
	if err := rows.Err(); err != nil {
		return agg, err
	}

	sRows, err := q.Query("SELECT COALESCE(Statut,'inconnu'), COUNT(*) FROM Factures GROUP BY Statut")
	if err != nil {
		return agg, err
	}
	defer sRows.Close()
	for sRows.Next() {
		var s string
		var c int
		if err := sRows.Scan(&s, &c); err != nil {
			return agg, err
		}
		agg.Statuts[s] = c
	}
	return agg, sRows.Err()
}
