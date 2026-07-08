package repository

import (
	"database/sql"
	"errors"
	"fmt"

	"github.com/go-sql-driver/mysql"
)

type FacturationRepo struct{}

const codeFacturationDuplicate = 1062

func (FacturationRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeFacturationDuplicate
}

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
	Montant   float64
	Frequence string
}

type FacturationProAgregat struct {
	NbContratsActifs    int
	NbContratsResilie   int
	TotalContratsActifs float64
	TotalAbonnements    float64
	TotalCampagnes      float64
	TotalCommissions    float64
	TotalGeneral        float64
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

func (FacturationRepo) ContratsDuProfessionnel(q Querier, idProfessionnel int) ([]ContratProLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Contrats, COALESCE(c.Type,''), COALESCE(c.Statut,''),
			COALESCE(c.Date_debut,''), COALESCE(c.Date_fin,''),
			COALESCE(c.Montant, 0), COALESCE(c.Frequence, 'mensuel')
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
		if err := rows.Scan(&c.ID, &c.Type, &c.Statut, &c.DateDebut, &c.DateFin, &c.Montant, &c.Frequence); err != nil {
			return nil, err
		}
		out = append(out, c)
	}
	return out, rows.Err()
}

func (FacturationRepo) FacturationDuProfessionnel(q Querier, idProfessionnel int) (FacturationProAgregat, error) {
	var a FacturationProAgregat
	err := q.QueryRow(
		`SELECT
			COALESCE(SUM(CASE WHEN Statut='actif' THEN 1 ELSE 0 END), 0),
			COALESCE(SUM(CASE WHEN Statut='resilie' THEN 1 ELSE 0 END), 0),
			COALESCE(SUM(CASE WHEN Statut='actif' THEN Montant ELSE 0 END), 0)
		FROM Contrats WHERE Id_Professionnels = ?`,
		idProfessionnel,
	).Scan(&a.NbContratsActifs, &a.NbContratsResilie, &a.TotalContratsActifs)
	if err != nil {
		return a, err
	}
	if err := q.QueryRow(
		`SELECT COALESCE(SUM(Prix), 0) FROM Abonnement WHERE Id_Professionnels = ? AND Statut = 'actif'`,
		idProfessionnel,
	).Scan(&a.TotalAbonnements); err != nil {
		return a, err
	}
	if err := q.QueryRow(
		`SELECT COALESCE(SUM(Prix), 0) FROM Publicites WHERE Id_Professionnels = ? AND Statut = 'active'`,
		idProfessionnel,
	).Scan(&a.TotalCampagnes); err != nil {
		return a, err
	}
	if err := q.QueryRow(
		`SELECT COALESCE(SUM(co.Montant), 0)
		 FROM Commissions co
		 JOIN Factures f ON f.Id_Facture = co.Id_Facture
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		 JOIN Professionnels_artisans p ON p.Id_Utilisateurs = u.Id_Utilisateurs
		 WHERE p.Id_Professionnels = ?`,
		idProfessionnel,
	).Scan(&a.TotalCommissions); err != nil {
		return a, err
	}
	a.TotalGeneral = a.TotalAbonnements + a.TotalCampagnes + a.TotalContratsActifs + a.TotalCommissions
	return a, nil
}

func (FacturationRepo) ProfessionnelExiste(q Querier, idProfessionnel int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT EXISTS(SELECT 1 FROM Professionnels_artisans WHERE Id_Professionnels = ?)",
		idProfessionnel,
	).Scan(&n)
	return n != 0, err
}

func (FacturationRepo) IdProfessionnel(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

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

func (FacturationRepo) ContratOwnerEtStatut(q Querier, idContrat int) (int, string, error) {
	var idPro int
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Id_Professionnels,0), COALESCE(Statut,'') FROM Contrats WHERE Id_Contrats = ? FOR UPDATE",
		idContrat,
	).Scan(&idPro, &statut)
	return idPro, statut, err
}

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

type AnnonceAchat struct {
	Prix           float64
	Titre          string
	Statut         string
	Type           string
	IdProprietaire int
}

func (FacturationRepo) AnnoncePourAchat(q Querier, idAnnonce int) (AnnonceAchat, error) {
	var a AnnonceAchat
	err := q.QueryRow(
		`SELECT COALESCE(a.Prix,0), COALESCE(a.Titre,''), COALESCE(a.Statut,''), COALESCE(a.Type_annonce,''),
			COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs, 0)
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 WHERE a.Id_Annonces = ?`, idAnnonce,
	).Scan(&a.Prix, &a.Titre, &a.Statut, &a.Type, &a.IdProprietaire)
	return a, err
}

func (FacturationRepo) MarquerAnnonceVendue(q Querier, idAnnonce, idAcheteur int) error {
	_, err := q.Exec(
		"UPDATE Annonces SET Statut = 'vendue', Id_Acheteur_Utilisateur = ? WHERE Id_Annonces = ?",
		idAcheteur, idAnnonce,
	)
	return err
}

type CommissionCreation struct {
	Taux                float64
	TauxApplique        float64
	Montant             float64
	IdAnnonce           int
	IdDevis             int
	IdCommandesServices int
	IdFacture           int64
}

type CommissionDetailLigne struct {
	ID                int
	Date              string
	Type              string
	Description       string
	PrixTotal         float64
	Taux              float64
	MontantCommission float64
	NomVendeur        string
}

func (FacturationRepo) ListerCommissionsPourAdmin(q Querier) ([]CommissionDetailLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Commission, COALESCE(DATE_FORMAT(c.Date_,'%d/%m/%Y %H:%i'),''), 'annonce',
			COALESCE(a.Titre,''), COALESCE(a.Prix,0), c.Taux, c.Montant,
			TRIM(CONCAT(COALESCE(uv.Prenom,''),' ',COALESCE(uv.Nom,''))), c.Date_
		 FROM Commissions c
		 JOIN Annonces a ON a.Id_Annonces = c.Id_Annonces
		 LEFT JOIN Particuliers pv ON pv.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pav ON pav.Id_Professionnels = a.Id_Professionnels
		 LEFT JOIN Utilisateurs uv ON uv.Id_Utilisateurs = COALESCE(pv.Id_Utilisateurs, pav.Id_Utilisateurs)
		 WHERE c.Id_Annonces IS NOT NULL
		 UNION ALL
		 SELECT c.Id_Commission, COALESCE(DATE_FORMAT(c.Date_,'%d/%m/%Y %H:%i'),''), 'devis',
			COALESCE(dp.Nom_objet,''), COALESCE(d.Prix,0), c.Taux, c.Montant,
			TRIM(CONCAT(COALESCE(uv.Prenom,''),' ',COALESCE(uv.Nom,''))), c.Date_
		 FROM Commissions c
		 JOIN Devis d ON d.Id_Devis = c.Id_Devis
		 JOIN Demandes_prestations dp ON dp.Id_Demandes_prestations = d.Id_Demandes_prestations
		 JOIN Professionnels_artisans pav ON pav.Id_Professionnels = d.Id_Professionnels
		 JOIN Utilisateurs uv ON uv.Id_Utilisateurs = pav.Id_Utilisateurs
		 WHERE c.Id_Devis IS NOT NULL
		 ORDER BY 9 DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []CommissionDetailLigne{}
	for rows.Next() {
		var l CommissionDetailLigne
		var dateTri sql.NullTime
		if err := rows.Scan(&l.ID, &l.Date, &l.Type, &l.Description, &l.PrixTotal, &l.Taux, &l.MontantCommission, &l.NomVendeur, &dateTri); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (FacturationRepo) ListerCommissionsPourPro(q Querier, idPro int) ([]CommissionDetailLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Commission, COALESCE(DATE_FORMAT(c.Date_,'%d/%m/%Y %H:%i'),''), 'annonce',
			COALESCE(a.Titre,''), COALESCE(a.Prix,0), c.Taux, c.Montant, ''
		 FROM Commissions c
		 JOIN Annonces a ON a.Id_Annonces = c.Id_Annonces
		 WHERE c.Id_Annonces IS NOT NULL AND a.Id_Professionnels = ?
		 UNION ALL
		 SELECT c.Id_Commission, COALESCE(DATE_FORMAT(c.Date_,'%d/%m/%Y %H:%i'),''), 'devis',
			COALESCE(dp.Nom_objet,''), COALESCE(d.Prix,0), c.Taux, c.Montant, ''
		 FROM Commissions c
		 JOIN Devis d ON d.Id_Devis = c.Id_Devis
		 JOIN Demandes_prestations dp ON dp.Id_Demandes_prestations = d.Id_Demandes_prestations
		 WHERE c.Id_Devis IS NOT NULL AND d.Id_Professionnels = ?
		 UNION ALL
		 SELECT c.Id_Commission, COALESCE(DATE_FORMAT(c.Date_,'%d/%m/%Y %H:%i'),''), 'prestation_catalogue',
			COALESCE(srv.Titre,''), COALESCE(cs.Prix,0), c.Taux, c.Montant, ''
		 FROM Commissions c
		 JOIN Commandes_Services cs ON cs.Id_Commandes_Services = c.Id_Commandes_Services
		 JOIN Services srv ON srv.Id_Services = cs.Id_Services
		 WHERE c.Id_Commandes_Services IS NOT NULL AND srv.Id_Professionnels = ?
		 ORDER BY 2 DESC`,
		idPro, idPro, idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []CommissionDetailLigne{}
	for rows.Next() {
		var l CommissionDetailLigne
		if err := rows.Scan(&l.ID, &l.Date, &l.Type, &l.Description, &l.PrixTotal, &l.Taux, &l.MontantCommission, &l.NomVendeur); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (FacturationRepo) CreerCommission(q Querier, c CommissionCreation) error {
	_, err := q.Exec(
		`INSERT INTO Commissions (Taux, taux_applique, Montant, Date_, Id_Annonces, Id_Devis, Id_Commandes_Services, Id_Facture)
		 VALUES (?, ?, ?, NOW(), NULLIF(?,0), NULLIF(?,0), NULLIF(?,0), ?)`,
		c.Taux, c.TauxApplique, c.Montant, c.IdAnnonce, c.IdDevis, c.IdCommandesServices, c.IdFacture,
	)
	return err
}

type PaiementLigne struct {
	ID        int
	Montant   float64
	Statut    string
	Methode   string
	Date      string
	Facture   string
	IdFacture int
}

type CommandeReference struct {
	Trouve        bool
	Statut        string
	Montant       float64
	IdFacture     int
	NumeroFacture string
	Type          string
}

type PaiementCreation struct {
	Montant         float64
	Statut          string
	Methode         string
	ReferenceStripe string
	PaymentIntent   string
	IdFacture       int64
	IdUtilisateur   int
}

func (FacturationRepo) PaiementsDeLUtilisateur(q Querier, idUtilisateur int) ([]PaiementLigne, error) {
	rows, err := q.Query(
		`SELECT p.Id_Paiements, COALESCE(p.Montant,0), COALESCE(p.Statut,''),
			COALESCE(p.Methode,''), COALESCE(p.Date_,''), COALESCE(f.Numero_facture,''),
			COALESCE(f.Id_Facture,0)
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
		if err := rows.Scan(&p.ID, &p.Montant, &p.Statut, &p.Methode, &p.Date, &p.Facture, &p.IdFacture); err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}

func (FacturationRepo) PaiementParReference(q Querier, reference string) (CommandeReference, error) {
	var c CommandeReference
	err := q.QueryRow(
		`SELECT COALESCE(p.Statut,''), COALESCE(p.Montant,0), COALESCE(f.Id_Facture,0),
			COALESCE(f.Numero_facture,''), COALESCE(f.Type,'')
		FROM Paiements p
		LEFT JOIN Factures f ON f.Id_Facture = p.Id_Facture
		WHERE p.Reference_stripe = ? LIMIT 1`, reference,
	).Scan(&c.Statut, &c.Montant, &c.IdFacture, &c.NumeroFacture, &c.Type)
	if errors.Is(err, sql.ErrNoRows) {
		return CommandeReference{}, nil
	}
	if err != nil {
		return CommandeReference{}, err
	}
	c.Trouve = true
	return c, nil
}

func (FacturationRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur,
	).Scan(&id)
	return id, err
}

func (FacturationRepo) IdUtilisateurDuPro(q Querier, idPro int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Utilisateurs FROM Professionnels_artisans WHERE Id_Professionnels = ?", idPro,
	).Scan(&id)
	return id, err
}

func (FacturationRepo) IdProfessionnelParUtilisateur(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs = ?", idUtilisateur,
	).Scan(&id)
	return id, err
}

func (FacturationRepo) CreerHistorique(q Querier, idParticulier int, statut, observations string) error {
	_, err := q.Exec(
		"INSERT INTO Historique (Date_Depot, Statut_depot, Observations, Id_Particuliers) VALUES (NOW(), ?, ?, ?)",
		statut, observations, idParticulier,
	)
	return err
}

func (FacturationRepo) NotifierAdmins(q Querier, contenu string) error {
	_, err := q.Exec(
		`INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs)
		 SELECT ?, NOW(), 0, a.Id_Administrateurs, a.Id_Utilisateurs FROM Administrateurs a`,
		contenu,
	)
	return err
}

func (FacturationRepo) CreerPaiement(q Querier, p PaiementCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Paiements (Date_, Montant, Statut, Methode, Reference_stripe, Ref_paiement_intent, Id_Facture, Id_Utilisateurs)
		 VALUES (NOW(), ?, ?, ?, NULLIF(?,''), NULLIF(?,''), ?, ?)`,
		p.Montant, p.Statut, p.Methode, p.ReferenceStripe, p.PaymentIntent, p.IdFacture, p.IdUtilisateur,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

type DemandeRembLigne struct {
	ID            int
	IdPaiement    int
	IdParticulier int
	Motif         string
	Statut        string
	DateDemande   string
	Montant       float64
	Nom           string
	Prenom        string
}

type DemandeRembSnapshot struct {
	Statut     string
	IdPaiement int
	IdPart     int
	Motif      string
}

type PaiementRembInfo struct {
	Statut        string
	Montant       float64
	IdUtilisateur int
	PaymentIntent string
	IdFacture     int
}

func (FacturationRepo) CreerDemandeRemboursement(q Querier, idPaiement, idParticulier int, motif string) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Demandes_remboursement (Id_Paiements, Id_Particuliers, Motif, Statut, Date_demande)
		 VALUES (?, ?, NULLIF(?,''), 'en_attente', NOW())`,
		idPaiement, idParticulier, motif,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (FacturationRepo) DemandeRembEnCoursExiste(q Querier, idPaiement int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Demandes_remboursement WHERE Id_Paiements = ? AND Statut IN ('en_attente','approuvee')",
		idPaiement,
	).Scan(&n)
	return n > 0, err
}

func (FacturationRepo) PaiementOwnerStatutPourMAJ(q Querier, idPaiement int) (int, string, error) {
	var idUser int
	var statut string
	err := q.QueryRow(
		"SELECT Id_Utilisateurs, COALESCE(Statut,'') FROM Paiements WHERE Id_Paiements = ? FOR UPDATE",
		idPaiement,
	).Scan(&idUser, &statut)
	return idUser, statut, err
}

func (FacturationRepo) DemandeRembPourMAJ(q Querier, idDemande int) (DemandeRembSnapshot, error) {
	var d DemandeRembSnapshot
	err := q.QueryRow(
		"SELECT COALESCE(Statut,''), Id_Paiements, Id_Particuliers, COALESCE(Motif,'') FROM Demandes_remboursement WHERE Id_Demande = ? FOR UPDATE",
		idDemande,
	).Scan(&d.Statut, &d.IdPaiement, &d.IdPart, &d.Motif)
	return d, err
}

func (FacturationRepo) PaiementsPayesPourEvenement(q Querier, idEvenement int) ([]int, error) {
	rows, err := q.Query(
		`SELECT DISTINCT p.Id_Paiements
		 FROM Paiements p
		 JOIN Lignes_Facture lf ON lf.Id_Facture = p.Id_Facture
		 WHERE lf.Id_Evenements = ? AND p.Statut = 'paye'`,
		idEvenement,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var ids []int
	for rows.Next() {
		var id int
		if err := rows.Scan(&id); err != nil {
			return nil, err
		}
		ids = append(ids, id)
	}
	return ids, rows.Err()
}

func (FacturationRepo) PaiementRembInfoPourMAJ(q Querier, idPaiement int) (PaiementRembInfo, error) {
	var p PaiementRembInfo
	err := q.QueryRow(
		`SELECT COALESCE(Statut,''), COALESCE(Montant,0), Id_Utilisateurs, COALESCE(Ref_paiement_intent,''), Id_Facture
		 FROM Paiements WHERE Id_Paiements = ? FOR UPDATE`,
		idPaiement,
	).Scan(&p.Statut, &p.Montant, &p.IdUtilisateur, &p.PaymentIntent, &p.IdFacture)
	return p, err
}

func (FacturationRepo) ItemDeFacture(q Querier, idFacture int) (string, int, error) {
	var idForm, idEvt sql.NullInt64
	err := q.QueryRow(
		"SELECT Id_Formations, Id_Evenements FROM Lignes_Facture WHERE Id_Facture = ? LIMIT 1",
		idFacture,
	).Scan(&idForm, &idEvt)
	if err != nil {
		return "", 0, err
	}
	if idForm.Valid {
		return "formation", int(idForm.Int64), nil
	}
	if idEvt.Valid {
		return "evenement", int(idEvt.Int64), nil
	}
	return "", 0, nil
}

func (FacturationRepo) MajPaiementStatut(q Querier, idPaiement int, statut string) error {
	_, err := q.Exec("UPDATE Paiements SET Statut = ? WHERE Id_Paiements = ?", statut, idPaiement)
	return err
}

func (FacturationRepo) FinaliserRemboursementPaiement(q Querier, idPaiement int, refundID, motif string) error {
	_, err := q.Exec(
		`UPDATE Paiements SET Statut = 'rembourse', Date_remboursement = NOW(),
		    Motif_remboursement = NULLIF(?,''), Ref_refund = ? WHERE Id_Paiements = ?`,
		motif, refundID, idPaiement,
	)
	return err
}

func (FacturationRepo) MajDemandeRembStatut(q Querier, idDemande int, statut string) error {
	_, err := q.Exec(
		"UPDATE Demandes_remboursement SET Statut = ?, Date_traitement = NOW() WHERE Id_Demande = ?",
		statut, idDemande,
	)
	return err
}

func (FacturationRepo) NotifierUtilisateur(q Querier, idUtilisateur int, contenu string) error {
	_, err := q.Exec(
		`INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs)
		 SELECT ?, NOW(), 0, (SELECT MIN(Id_Administrateurs) FROM Administrateurs), ?`,
		contenu, idUtilisateur,
	)
	return err
}

func (FacturationRepo) NotifierProsPremium(q Querier, contenu string) error {
	_, err := q.Exec(
		`INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs)
		 SELECT ?, NOW(), 0, (SELECT MIN(Id_Administrateurs) FROM Administrateurs), pa.Id_Utilisateurs
		 FROM Abonnement ab
		 JOIN Professionnels_artisans pa ON pa.Id_Professionnels = ab.Id_Professionnels
		 WHERE ab.Statut = 'actif'`,
		contenu,
	)
	return err
}

func (FacturationRepo) ListerDemandesRemb(q Querier) ([]DemandeRembLigne, error) {
	rows, err := q.Query(
		`SELECT d.Id_Demande, d.Id_Paiements, d.Id_Particuliers, COALESCE(d.Motif,''), COALESCE(d.Statut,''),
		    COALESCE(d.Date_demande,''), COALESCE(p.Montant,0), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		 FROM Demandes_remboursement d
		 JOIN Paiements p ON p.Id_Paiements = d.Id_Paiements
		 JOIN Particuliers pa ON pa.Id_Particuliers = d.Id_Particuliers
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = pa.Id_Utilisateurs
		 ORDER BY d.Id_Demande DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []DemandeRembLigne{}
	for rows.Next() {
		var d DemandeRembLigne
		if err := rows.Scan(&d.ID, &d.IdPaiement, &d.IdParticulier, &d.Motif, &d.Statut, &d.DateDemande, &d.Montant, &d.Nom, &d.Prenom); err != nil {
			return nil, err
		}
		out = append(out, d)
	}
	return out, rows.Err()
}

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

type AbonnementLigne struct {
	ID                         string
	Type                       string
	Statut                     string
	Prix                       float64
	DateDebut                  string
	DateFin                    string
	IdProfessionnels           int
	AnnoncesGratuitesIncluses  int
	AnnoncesGratuitesUtilisees int
	Nom                        string
	Prenom                     string
	Entreprise                 string
}

type AbonnementCreation struct {
	ID                        string
	Type                      string
	Prix                      float64
	DateDebut                 string
	DateFin                   string
	Statut                    string
	IdProfessionnels          int
	ReferenceStripe           string
	StripeSubscriptionID      string
	AnnoncesGratuitesIncluses int
}

func (FacturationRepo) AdminListerAbonnements(q Querier) ([]AbonnementLigne, error) {
	rows, err := q.Query(
		`SELECT ab.Id_Abonnement, COALESCE(ab.Type,''), COALESCE(ab.Statut,''),
			COALESCE(ab.Prix,0), COALESCE(ab.Date_Debut,''), COALESCE(ab.Date_Fin,''), COALESCE(ab.Id_Professionnels,0),
			COALESCE(ab.Annonces_Gratuites_Incluses,0), COALESCE(ab.Annonces_Gratuites_Utilisees,0),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(p.Nom_Entreprise,'')
		FROM Abonnement ab
		LEFT JOIN Professionnels_artisans p ON p.Id_Professionnels = ab.Id_Professionnels
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		ORDER BY ab.Date_Debut DESC, ab.Id_Abonnement DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []AbonnementLigne{}
	for rows.Next() {
		var a AbonnementLigne
		if err := rows.Scan(&a.ID, &a.Type, &a.Statut, &a.Prix, &a.DateDebut, &a.DateFin, &a.IdProfessionnels,
			&a.AnnoncesGratuitesIncluses, &a.AnnoncesGratuitesUtilisees, &a.Nom, &a.Prenom, &a.Entreprise); err != nil {
			return nil, err
		}
		out = append(out, a)
	}
	return out, rows.Err()
}

func (FacturationRepo) AbonnementsDuPro(q Querier, idPro int) ([]AbonnementLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Abonnement, COALESCE(Type,''), COALESCE(Statut,''),
			COALESCE(Prix,0), COALESCE(Date_Debut,''), COALESCE(Date_Fin,''), COALESCE(Id_Professionnels,0),
			COALESCE(Annonces_Gratuites_Incluses,0), COALESCE(Annonces_Gratuites_Utilisees,0)
		FROM Abonnement WHERE Id_Professionnels = ? ORDER BY Date_Debut DESC, Id_Abonnement DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []AbonnementLigne{}
	for rows.Next() {
		var a AbonnementLigne
		if err := rows.Scan(&a.ID, &a.Type, &a.Statut, &a.Prix, &a.DateDebut, &a.DateFin, &a.IdProfessionnels,
			&a.AnnoncesGratuitesIncluses, &a.AnnoncesGratuitesUtilisees); err != nil {
			return nil, err
		}
		out = append(out, a)
	}
	return out, rows.Err()
}

func (FacturationRepo) CreerAbonnement(q Querier, a AbonnementCreation) error {
	_, err := q.Exec(
		`INSERT INTO Abonnement (Id_Abonnement, Type, Prix, Date_Debut, Date_Fin, Statut, Id_Professionnels, Reference_Stripe, Stripe_Subscription_Id, Annonces_Gratuites_Incluses)
		 VALUES (?, ?, ?, NULLIF(?,''), NULLIF(?,''), ?, NULLIF(?,0), NULLIF(?,''), NULLIF(?,''), ?)`,
		a.ID, a.Type, a.Prix, a.DateDebut, a.DateFin, a.Statut, a.IdProfessionnels, a.ReferenceStripe, a.StripeSubscriptionID, a.AnnoncesGratuitesIncluses,
	)
	return err
}

func (FacturationRepo) MajStatutAbonnementParSubscriptionID(q Querier, stripeSubscriptionID, statut string) error {
	_, err := q.Exec(
		"UPDATE Abonnement SET Statut = ? WHERE Stripe_Subscription_Id = ?",
		statut, stripeSubscriptionID,
	)
	return err
}

func (FacturationRepo) ReactiverAbonnementParSubscriptionID(q Querier, stripeSubscriptionID string) error {
	_, err := q.Exec(
		"UPDATE Abonnement SET Statut = 'actif' WHERE Stripe_Subscription_Id = ? AND Statut IN ('expire','suspendu')",
		stripeSubscriptionID,
	)
	return err
}

func (FacturationRepo) CreerContratDepuisAbonnement(q Querier, idAbonnement string, idPro int, prix float64) error {
	description := fmt.Sprintf("Abonnement Premium UpcycleConnect - %.2f EUR/mois, renouvellement automatique mensuel via Stripe.", prix)
	_, err := q.Exec(
		`INSERT INTO Contrats (Date_signature, Date_debut, Type, Id_Professionnels, Statut, Id_Abonnement, Montant, Description)
		 VALUES (NOW(), CURDATE(), 'abonnement_premium', ?, 'actif', ?, ?, ?)`,
		idPro, idAbonnement, prix, description,
	)
	return err
}

func (FacturationRepo) CreerContratDepuisPublicite(q Querier, idPublicite string, idPro int, prix float64, description string) error {
	_, err := q.Exec(
		`INSERT INTO Contrats (Date_signature, Date_debut, Type, Id_Professionnels, Statut, Id_Publicites, Montant, Description)
		 VALUES (NOW(), CURDATE(), 'publicite', ?, 'actif', ?, ?, ?)`,
		idPro, idPublicite, prix, description,
	)
	return err
}

func (FacturationRepo) ConsommerAnnonceGratuite(q Querier, idPro int) bool {
	res, err := q.Exec(
		`UPDATE Abonnement SET Annonces_Gratuites_Utilisees = Annonces_Gratuites_Utilisees + 1
		 WHERE Id_Professionnels = ? AND Statut = 'actif' AND Annonces_Gratuites_Utilisees < Annonces_Gratuites_Incluses
		 ORDER BY Date_Debut DESC LIMIT 1`,
		idPro,
	)
	if err != nil {
		return false
	}
	n, err := res.RowsAffected()
	return err == nil && n > 0
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

type MoisCA struct {
	Mois string  `json:"mois"`
	CA   float64 `json:"ca"`
}

type FinancesAgregat struct {
	NbFactures       int                `json:"nb_factures"`
	TotalHT          float64            `json:"total_ht"`
	TotalTTC         float64            `json:"total_ttc"`
	TotalCommissions float64            `json:"total_commissions"`
	CAParMois        []MoisCA           `json:"ca_par_mois"`
	Statuts          map[string]int     `json:"statuts"`
	CAParSource      map[string]float64 `json:"ca_par_source"`
}

func (FacturationRepo) AgregatFinances(q Querier) (FinancesAgregat, error) {
	agg := FinancesAgregat{CAParMois: []MoisCA{}, Statuts: map[string]int{}, CAParSource: map[string]float64{}}

	var caAbonnements, caPublicites, caCommissions float64
	if err := q.QueryRow("SELECT COALESCE(SUM(Prix),0) FROM Abonnement WHERE Statut='actif'").Scan(&caAbonnements); err != nil {
		return agg, err
	}
	if err := q.QueryRow("SELECT COALESCE(SUM(Prix),0) FROM Publicites WHERE Statut='active'").Scan(&caPublicites); err != nil {
		return agg, err
	}
	if err := q.QueryRow("SELECT COALESCE(SUM(Montant),0) FROM Commissions").Scan(&caCommissions); err != nil {
		return agg, err
	}
	if err := q.QueryRow("SELECT COUNT(*) FROM Factures").Scan(&agg.NbFactures); err != nil {
		return agg, err
	}

	agg.TotalCommissions = caCommissions
	agg.TotalTTC = caAbonnements + caPublicites + caCommissions
	agg.TotalHT = agg.TotalTTC / 1.2
	agg.CAParSource["abonnements"] = caAbonnements
	agg.CAParSource["publicites"] = caPublicites
	agg.CAParSource["commissions"] = caCommissions

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
