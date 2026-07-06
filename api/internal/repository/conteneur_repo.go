package repository

import (
	"errors"

	"github.com/go-sql-driver/mysql"

	"upcycleconnect/internal/domain"
)

type ConteneurRepo struct{}

const codeMySQLDuplicate = 1062

func (ConteneurRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeMySQLDuplicate
}

func (ConteneurRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

func (ConteneurRepo) IdAdministrateur(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Administrateurs FROM Administrateurs WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

func (ConteneurRepo) ConteneurStatut(q Querier, idConteneur int) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Conteneurs WHERE Id_Conteneurs = ?",
		idConteneur,
	).Scan(&statut)
	return statut, err
}

func (ConteneurRepo) ConteneurStatutPourMAJ(q Querier, idConteneur int) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Conteneurs WHERE Id_Conteneurs = ? FOR UPDATE",
		idConteneur,
	).Scan(&statut)
	return statut, err
}

type DemandeCreation struct {
	TypeObjet     string
	Description   string
	EtatUsure     string
	IdConteneur   int
	DateDepot     string
	Destination   string
	PrixVente     float64
	PhotoUrl      string
	IdParticulier int
	IdAnnonce     int
}

func (ConteneurRepo) AnnoncePourDepot(q Querier, idAnnonce, idUtilisateur int) (titre, description, categorie, typeAnnonce, statut string, prix float64, err error) {
	err = q.QueryRow(
		`SELECT COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
			COALESCE(a.Type_annonce,''), COALESCE(a.Statut,''), COALESCE(a.Prix,0)
		 FROM Annonces a
		 JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 WHERE a.Id_Annonces = ? AND p.Id_Utilisateurs = ?`,
		idAnnonce, idUtilisateur,
	).Scan(&titre, &description, &categorie, &typeAnnonce, &statut, &prix)
	return
}

func (ConteneurRepo) AnnonceDejaEnDepot(q Querier, idAnnonce int) (bool, error) {
	var n int
	err := q.QueryRow("SELECT COUNT(*) FROM Demandes_conteneurs WHERE Id_Annonces = ?", idAnnonce).Scan(&n)
	return n > 0, err
}

type AnnonceEligibleDepot struct {
	ID      int
	Titre   string
	TypeAnn string
	Statut  string
	Prix    float64
}

func (ConteneurRepo) ListerAnnoncesEligiblesPourDepot(q Querier, idUtilisateur int) ([]AnnonceEligibleDepot, error) {
	rows, err := q.Query(
		`SELECT a.Id_Annonces, COALESCE(a.Titre,''), COALESCE(a.Type_annonce,''), COALESCE(a.Statut,''), COALESCE(a.Prix,0)
		 FROM Annonces a
		 JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 WHERE p.Id_Utilisateurs = ? AND a.Statut IN ('vendue','reservee')
		   AND NOT EXISTS (SELECT 1 FROM Demandes_conteneurs d WHERE d.Id_Annonces = a.Id_Annonces)
		 ORDER BY a.Id_Annonces DESC`,
		idUtilisateur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []AnnonceEligibleDepot{}
	for rows.Next() {
		var a AnnonceEligibleDepot
		if err := rows.Scan(&a.ID, &a.Titre, &a.TypeAnn, &a.Statut, &a.Prix); err != nil {
			return nil, err
		}
		out = append(out, a)
	}
	return out, rows.Err()
}

func (ConteneurRepo) CreerDemande(q Querier, d DemandeCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Demandes_conteneurs
		   (Type_objet, Description, Etat_usure, Id_Conteneurs, Date_depot, Destination,
		    Prix_vente, Photo_url, Statut, Date_demande, Id_Particuliers, Id_Annonces)
		 VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, ?, NULLIF(?, ''), 'en_attente', NOW(), ?, NULLIF(?, 0))`,
		d.TypeObjet, d.Description, d.EtatUsure, d.IdConteneur, d.DateDepot,
		d.Destination, d.PrixVente, d.PhotoUrl, d.IdParticulier, d.IdAnnonce,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ConteneurRepo) AcheteurDeAnnonce(q Querier, idAnnonce int) (idUtilisateur int, nom, email string, err error) {
	err = q.QueryRow(
		`SELECT u.Id_Utilisateurs, TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,''))), COALESCE(u.Email,'')
		 FROM Annonces a JOIN Utilisateurs u ON u.Id_Utilisateurs = a.Id_Acheteur_Utilisateur
		 WHERE a.Id_Annonces = ?`,
		idAnnonce,
	).Scan(&idUtilisateur, &nom, &email)
	return
}

func (ConteneurRepo) DemandePourMAJ(q Querier, idDemande int) (domain.DemandeSnapshot, error) {
	var s domain.DemandeSnapshot
	err := q.QueryRow(
		`SELECT COALESCE(Statut,''), Id_Particuliers, COALESCE(Id_Conteneurs,0), COALESCE(Type_objet,''), COALESCE(Id_Annonces,0)
		 FROM Demandes_conteneurs WHERE Id_Demandes_conteneurs = ? FOR UPDATE`,
		idDemande,
	).Scan(&s.Statut, &s.Proprietaire, &s.IdConteneur, &s.Type, &s.IdAnnonce)
	return s, err
}

func (ConteneurRepo) BoxesDuConteneurPourMAJ(q Querier, idConteneur int) ([]domain.BoxSnapshot, error) {
	rows, err := q.Query(
		`SELECT b.Id_Box, b.Capacite, b.Statut, COALESCE(b.Taille, 'standard'),
		        (SELECT COUNT(*) FROM Objets o WHERE o.Id_Box = b.Id_Box AND o.Statut IN ('en_stock','reserve_pro')) AS occupation
		 FROM Box b
		 WHERE b.Id_Conteneurs = ?
		 ORDER BY b.Id_Box
		 FOR UPDATE`,
		idConteneur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	boxes := []domain.BoxSnapshot{}
	for rows.Next() {
		var b domain.BoxSnapshot
		if err := rows.Scan(&b.ID, &b.Capacite, &b.Statut, &b.Taille, &b.Occupation); err != nil {
			return nil, err
		}
		boxes = append(boxes, b)
	}
	return boxes, rows.Err()
}

func (ConteneurRepo) AssignerCodeEtValider(q Querier, idDemande int, code string, idBox int) error {
	_, err := q.Exec(
		"UPDATE Demandes_conteneurs SET Statut='validee', Code_acces=?, Id_Box=? WHERE Id_Demandes_conteneurs=? AND Statut='en_attente'",
		code, idBox, idDemande,
	)
	return err
}

func (ConteneurRepo) MajStatutDemande(q Querier, idDemande int, statut string) error {
	_, err := q.Exec(
		"UPDATE Demandes_conteneurs SET Statut=? WHERE Id_Demandes_conteneurs=?",
		statut, idDemande,
	)
	return err
}

type ObjetCreation struct {
	Type          string
	IdConteneur   int
	IdParticulier int
	IdBox         int
	IdDemande     int
}

func (ConteneurRepo) CreerObjetEnStock(q Querier, o ObjetCreation) (int, error) {
	res, err := q.Exec(
		`INSERT INTO Objets (Type, Statut, Id_Conteneurs, Id_Particuliers, Id_Box, Id_Demandes_conteneurs)
		 VALUES (?, 'en_stock', ?, ?, ?, ?)`,
		o.Type, o.IdConteneur, o.IdParticulier, o.IdBox, o.IdDemande,
	)
	if err != nil {
		return 0, err
	}
	id, err := res.LastInsertId()
	if err != nil {
		return 0, err
	}
	return int(id), nil
}

type DemandeLigne struct {
	ID           int
	TypeObjet    string
	Description  string
	EtatUsure    string
	Statut       string
	CodeAcces    string
	Date         string
	CodeBarre    string
	IdBox        int
	BoxReference string
	BoxTaille    string
}

func (ConteneurRepo) MesDemandes(q Querier, idParticulier int) ([]DemandeLigne, error) {
	rows, err := q.Query(
		`SELECT d.Id_Demandes_conteneurs, COALESCE(d.Type_objet,''), COALESCE(d.Description,''),
		        COALESCE(d.Etat_usure,''), COALESCE(d.Statut,'en_attente'), COALESCE(d.Code_acces,''),
		        COALESCE(d.Date_demande,''), COALESCE(cb.Code,''),
		        COALESCE(d.Id_Box, 0), COALESCE(b.Reference, ''), COALESCE(b.Taille, '')
		 FROM Demandes_conteneurs d
		 LEFT JOIN Objets o ON o.Id_Demandes_conteneurs = d.Id_Demandes_conteneurs
		 LEFT JOIN Codes_Barres cb ON cb.Id_Objets = o.Id_Objets
		 LEFT JOIN Box b ON b.Id_Box = d.Id_Box
		 WHERE d.Id_Particuliers = ? ORDER BY d.Date_demande DESC`,
		idParticulier,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []DemandeLigne{}
	for rows.Next() {
		var d DemandeLigne
		if err := rows.Scan(&d.ID, &d.TypeObjet, &d.Description, &d.EtatUsure, &d.Statut, &d.CodeAcces, &d.Date, &d.CodeBarre,
			&d.IdBox, &d.BoxReference, &d.BoxTaille); err != nil {
			return nil, err
		}
		liste = append(liste, d)
	}
	return liste, rows.Err()
}

type DemandeAdminLigne struct {
	ID           int
	TypeObjet    string
	Description  string
	EtatUsure    string
	Statut       string
	Date         string
	PrixVente    float64
	Localisation string
	CodeAcces    string
	Nom          string
	Prenom       string
	Email        string
	IdConteneur  int
}

func (ConteneurRepo) AdminListerDemandes(q Querier) ([]DemandeAdminLigne, error) {
	rows, err := q.Query(
		`SELECT d.Id_Demandes_conteneurs, COALESCE(d.Type_objet,''), COALESCE(d.Description,''),
		        COALESCE(d.Etat_usure,''), COALESCE(d.Statut,'en_attente'), COALESCE(d.Date_demande,''),
		        COALESCE(d.Prix_vente,0), COALESCE(c.Localisation,''), COALESCE(d.Code_acces,''),
		        COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,''),
		        COALESCE(d.Id_Conteneurs,0)
		 FROM Demandes_conteneurs d
		 LEFT JOIN Conteneurs c ON c.Id_Conteneurs = d.Id_Conteneurs
		 JOIN Particuliers p ON p.Id_Particuliers = d.Id_Particuliers
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		 ORDER BY d.Date_demande DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []DemandeAdminLigne{}
	for rows.Next() {
		var d DemandeAdminLigne
		if err := rows.Scan(&d.ID, &d.TypeObjet, &d.Description, &d.EtatUsure, &d.Statut, &d.Date,
			&d.PrixVente, &d.Localisation, &d.CodeAcces, &d.Nom, &d.Prenom, &d.Email, &d.IdConteneur); err != nil {
			return nil, err
		}
		liste = append(liste, d)
	}
	return liste, rows.Err()
}

type ConteneurPublic struct {
	ID           int
	Localisation string
	Capacite     int
	Statut       string
}

func (ConteneurRepo) ListerConteneursDisponibles(q Querier) ([]ConteneurPublic, error) {
	rows, err := q.Query(
		`SELECT Id_Conteneurs, COALESCE(Localisation,''), COALESCE(CAST(Capacite AS UNSIGNED),0),
		        COALESCE(Statut,'disponible')
		 FROM Conteneurs WHERE Statut = 'disponible' ORDER BY Id_Conteneurs`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []ConteneurPublic{}
	for rows.Next() {
		var c ConteneurPublic
		if err := rows.Scan(&c.ID, &c.Localisation, &c.Capacite, &c.Statut); err != nil {
			return nil, err
		}
		liste = append(liste, c)
	}
	return liste, rows.Err()
}

type ConteneurAdmin struct {
	ID           int
	Localisation string
	Capacite     int
	Statut       string
	NbDemandes   int
	Occupation   int
	CapaciteBox  int
}

func (ConteneurRepo) AdminListerConteneurs(q Querier) ([]ConteneurAdmin, error) {
	rows, err := q.Query(
		`SELECT c.Id_Conteneurs, COALESCE(c.Localisation,''), COALESCE(CAST(c.Capacite AS UNSIGNED),0),
		        COALESCE(c.Statut,'disponible'),
		        (SELECT COUNT(*) FROM Demandes_conteneurs d
		           WHERE d.Id_Conteneurs = c.Id_Conteneurs AND d.Statut = 'validee') AS nb_demandes,
		        COALESCE((SELECT COUNT(*) FROM Objets o
		           JOIN Box b2 ON b2.Id_Box = o.Id_Box
		           WHERE b2.Id_Conteneurs = c.Id_Conteneurs AND o.Statut IN ('en_stock','reserve_pro')),0) AS occupation,
		        COALESCE((SELECT SUM(b.Capacite) FROM Box b WHERE b.Id_Conteneurs = c.Id_Conteneurs),0) AS capacite_box
		 FROM Conteneurs c
		 ORDER BY c.Id_Conteneurs DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []ConteneurAdmin{}
	for rows.Next() {
		var c ConteneurAdmin
		if err := rows.Scan(&c.ID, &c.Localisation, &c.Capacite, &c.Statut,
			&c.NbDemandes, &c.Occupation, &c.CapaciteBox); err != nil {
			return nil, err
		}
		liste = append(liste, c)
	}
	return liste, rows.Err()
}

func (ConteneurRepo) CreerConteneur(q Querier, localisation string, capacite int, statut string, idAdmin int) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Conteneurs (Localisation, Capacite, Statut, Id_Administrateurs) VALUES (?, ?, ?, ?)",
		localisation, capacite, statut, idAdmin,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ConteneurRepo) CreerBox(q Querier, reference string, capacite, idConteneur int) error {
	_, err := q.Exec(
		"INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs) VALUES (?, ?, 'disponible', ?)",
		reference, capacite, idConteneur,
	)
	return err
}

func (ConteneurRepo) ModifierConteneur(q Querier, idConteneur int, localisation string, capacite int, statut string) (int64, error) {
	res, err := q.Exec(
		"UPDATE Conteneurs SET Localisation=?, Capacite=?, Statut=? WHERE Id_Conteneurs=?",
		localisation, capacite, statut, idConteneur,
	)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

func (ConteneurRepo) SyncBoxCapacite(q Querier, idConteneur, capacite int) error {
	_, err := q.Exec("UPDATE Box SET Capacite=? WHERE Id_Conteneurs=?", capacite, idConteneur)
	return err
}

func (ConteneurRepo) CompterObjetsConteneur(q Querier, idConteneur int) (int, error) {
	var n int
	err := q.QueryRow("SELECT COUNT(*) FROM Objets WHERE Id_Conteneurs = ?", idConteneur).Scan(&n)
	return n, err
}

func (ConteneurRepo) CompterDemandesActives(q Querier, idConteneur int) (int, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Demandes_conteneurs WHERE Id_Conteneurs = ? AND Statut IN ('en_attente','validee')",
		idConteneur,
	).Scan(&n)
	return n, err
}

func (ConteneurRepo) SupprimerBoxesConteneur(q Querier, idConteneur int) error {
	_, err := q.Exec("DELETE FROM Box WHERE Id_Conteneurs = ?", idConteneur)
	return err
}

func (ConteneurRepo) SupprimerConteneur(q Querier, idConteneur int) (int64, error) {
	res, err := q.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs = ?", idConteneur)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}
