package repository

import (
	"errors"

	"github.com/go-sql-driver/mysql"

	"upcycleconnect/internal/domain"
)

// ConteneurRepo : accès SQL pour le vertical Demande / Conteneur / Box. Sans état
// — chaque méthode reçoit le Querier (DB ou Tx) sur lequel exécuter, de sorte que
// les décisions de capacité s'exécutent dans la transaction (et sous les verrous
// FOR UPDATE) ouverte par le service.
type ConteneurRepo struct{}

// codeMySQLDuplicate : violation d'unicité (ER_DUP_ENTRY). Sert au service à
// regénérer un Code_acces en cas de collision sur uq_demande_code_acces.
const codeMySQLDuplicate = 1062

// EstViolationUnicite indique si err est une violation de contrainte d'unicité
// MySQL. Le service s'en sert pour réessayer une génération de code, sans que la
// couche au-dessus n'ait à connaître le pilote SQL.
func (ConteneurRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeMySQLDuplicate
}

// IdParticulier résout l'Id_Particuliers de l'utilisateur AUTHENTIFIÉ. Renvoie
// sql.ErrNoRows si le compte n'est pas un particulier : le service en fait un 403.
func (ConteneurRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

// IdAdministrateur résout l'Id_Administrateurs de l'utilisateur AUTHENTIFIÉ.
// Renvoie sql.ErrNoRows si le compte n'est pas administrateur.
func (ConteneurRepo) IdAdministrateur(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Administrateurs FROM Administrateurs WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

// ConteneurStatut renvoie le statut d'un conteneur (sql.ErrNoRows si inexistant).
func (ConteneurRepo) ConteneurStatut(q Querier, idConteneur int) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Conteneurs WHERE Id_Conteneurs = ?",
		idConteneur,
	).Scan(&statut)
	return statut, err
}

// ConteneurStatutPourMAJ verrouille la ligne conteneur (FOR UPDATE) — utilisé par
// la suppression pour sérialiser face aux écritures concurrentes.
func (ConteneurRepo) ConteneurStatutPourMAJ(q Querier, idConteneur int) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Conteneurs WHERE Id_Conteneurs = ? FOR UPDATE",
		idConteneur,
	).Scan(&statut)
	return statut, err
}

// DemandeCreation : données d'une demande de dépôt, DÉJÀ validées par le domaine.
// Le statut initial (en_attente) et la date sont imposés par le repo, jamais par
// le client. Code_acces reste NULL jusqu'à la validation.
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
}

func (ConteneurRepo) CreerDemande(q Querier, d DemandeCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Demandes_conteneurs
		   (Type_objet, Description, Etat_usure, Id_Conteneurs, Date_depot, Destination,
		    Prix_vente, Photo_url, Statut, Date_demande, Id_Particuliers)
		 VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, ?, NULLIF(?, ''), 'en_attente', NOW(), ?)`,
		d.TypeObjet, d.Description, d.EtatUsure, d.IdConteneur, d.DateDepot,
		d.Destination, d.PrixVente, d.PhotoUrl, d.IdParticulier,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

// DemandePourMAJ verrouille la demande (FOR UPDATE) et renvoie le snapshot métier
// nécessaire à la décision de transition. Id_Conteneurs étant nullable, il
// remonte à 0 si absent — le service refuse alors la validation (pas de box cible).
func (ConteneurRepo) DemandePourMAJ(q Querier, idDemande int) (domain.DemandeSnapshot, error) {
	var s domain.DemandeSnapshot
	err := q.QueryRow(
		`SELECT COALESCE(Statut,''), Id_Particuliers, COALESCE(Id_Conteneurs,0), COALESCE(Type_objet,'')
		 FROM Demandes_conteneurs WHERE Id_Demandes_conteneurs = ? FOR UPDATE`,
		idDemande,
	).Scan(&s.Statut, &s.Proprietaire, &s.IdConteneur, &s.Type)
	return s, err
}

// BoxesDuConteneurPourMAJ verrouille les box d'un conteneur (FOR UPDATE) et calcule
// pour chacune son occupation DÉRIVÉE = COUNT des objets PHYSIQUEMENT présents
// (en_stock OU reserve_pro : un objet réservé reste dans la box tant que le pro ne
// l'a pas récupéré ; seul recupere libère la place). La sous-requête de comptage
// est volontairement non verrouillante : sous READ COMMITTED (cf. services/tx.go)
// elle reflète les insertions committées des transactions concurrentes une fois le
// verrou FOR UPDATE obtenu. C'est le point de sérialisation qui empêche deux
// validations simultanées de sur-remplir la box.
func (ConteneurRepo) BoxesDuConteneurPourMAJ(q Querier, idConteneur int) ([]domain.BoxSnapshot, error) {
	rows, err := q.Query(
		`SELECT b.Id_Box, b.Capacite, b.Statut,
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
		if err := rows.Scan(&b.ID, &b.Capacite, &b.Statut, &b.Occupation); err != nil {
			return nil, err
		}
		boxes = append(boxes, b)
	}
	return boxes, rows.Err()
}

// AssignerCodeEtValider applique la transition en_attente -> validee en posant le
// Code_acces. La garde « AND Statut='en_attente' » est une ceinture de sécurité
// (la ligne est déjà verrouillée). Une collision sur uq_demande_code_acces remonte
// telle quelle : le service la détecte (EstViolationUnicite) et regénère un code.
func (ConteneurRepo) AssignerCodeEtValider(q Querier, idDemande int, code string) error {
	_, err := q.Exec(
		"UPDATE Demandes_conteneurs SET Statut='validee', Code_acces=? WHERE Id_Demandes_conteneurs=? AND Statut='en_attente'",
		code, idDemande,
	)
	return err
}

// MajStatutDemande applique une transition d'état déjà décidée par le domaine
// (refuser, déposer).
func (ConteneurRepo) MajStatutDemande(q Querier, idDemande int, statut string) error {
	_, err := q.Exec(
		"UPDATE Demandes_conteneurs SET Statut=? WHERE Id_Demandes_conteneurs=?",
		statut, idDemande,
	)
	return err
}

// ObjetCreation : matérialisation physique d'une demande validée — un objet
// 'en_stock' rattaché à la box choisie. C'est cet objet qui OCCUPE la place (le
// modèle d'occupation compte les objets en_stock par box).
type ObjetCreation struct {
	Type          string
	IdConteneur   int
	IdParticulier int
	IdBox         int
}

func (ConteneurRepo) CreerObjetEnStock(q Querier, o ObjetCreation) error {
	_, err := q.Exec(
		`INSERT INTO Objets (Type, Statut, Id_Conteneurs, Id_Particuliers, Id_Box)
		 VALUES (?, 'en_stock', ?, ?, ?)`,
		o.Type, o.IdConteneur, o.IdParticulier, o.IdBox,
	)
	return err
}

// DemandeLigne : projection de liste pour le PROPRIÉTAIRE (sa propre file). Pas de
// PII tierce — c'est l'utilisateur qui regarde ses demandes.
type DemandeLigne struct {
	ID          int
	TypeObjet   string
	Description string
	EtatUsure   string
	Statut      string
	CodeAcces   string
	Date        string
}

func (ConteneurRepo) MesDemandes(q Querier, idParticulier int) ([]DemandeLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Demandes_conteneurs, COALESCE(Type_objet,''), COALESCE(Description,''),
		        COALESCE(Etat_usure,''), COALESCE(Statut,'en_attente'), COALESCE(Code_acces,''),
		        COALESCE(Date_demande,'')
		 FROM Demandes_conteneurs WHERE Id_Particuliers = ? ORDER BY Date_demande DESC`,
		idParticulier,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []DemandeLigne{}
	for rows.Next() {
		var d DemandeLigne
		if err := rows.Scan(&d.ID, &d.TypeObjet, &d.Description, &d.EtatUsure, &d.Statut, &d.CodeAcces, &d.Date); err != nil {
			return nil, err
		}
		liste = append(liste, d)
	}
	return liste, rows.Err()
}

// DemandeAdminLigne : projection de modération (admin). La PII du déposant est
// destinée au back-office (traitement de la demande), pas au public.
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
}

func (ConteneurRepo) AdminListerDemandes(q Querier) ([]DemandeAdminLigne, error) {
	rows, err := q.Query(
		`SELECT d.Id_Demandes_conteneurs, COALESCE(d.Type_objet,''), COALESCE(d.Description,''),
		        COALESCE(d.Etat_usure,''), COALESCE(d.Statut,'en_attente'), COALESCE(d.Date_demande,''),
		        COALESCE(d.Prix_vente,0), COALESCE(c.Localisation,''), COALESCE(d.Code_acces,''),
		        COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,'')
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
			&d.PrixVente, &d.Localisation, &d.CodeAcces, &d.Nom, &d.Prenom, &d.Email); err != nil {
			return nil, err
		}
		liste = append(liste, d)
	}
	return liste, rows.Err()
}

// ConteneurPublic : ligne de la liste publique (choix d'un point de dépôt).
type ConteneurPublic struct {
	ID           int
	Localisation string
	Capacite     int
	Statut       string
}

// ListerConteneursDisponibles : seuls les conteneurs disponibles acceptent de
// nouvelles demandes. Capacite est un VARCHAR en base : CAST pour l'exposer en int.
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

// ConteneurAdmin : ligne back-office. Occupation et CapaciteBox sont DÉRIVÉES des
// box (objets physiquement présents vs somme des capacités de box) — le service en
// tire le taux de remplissage réel, sans jamais le stocker.
type ConteneurAdmin struct {
	ID           int
	Localisation string
	Capacite     int
	Statut       string
	NbDemandes   int // demandes validées rattachées
	Occupation   int // objets présents (en_stock + reserve_pro) dans les box du conteneur
	CapaciteBox  int // somme des capacités des box du conteneur
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

// CreerConteneur insère un conteneur sous l'identité de l'administrateur (NOT NULL
// Id_Administrateurs — l'omettre provoquait un 500). Capacite est stockée telle
// quelle dans la colonne VARCHAR.
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

// CreerBox crée une box pour un conteneur. À la création d'un conteneur on
// matérialise systématiquement sa box, sinon ChoisirBox ne trouverait jamais de
// place et toute validation finirait en 409.
func (ConteneurRepo) CreerBox(q Querier, reference string, capacite, idConteneur int) error {
	_, err := q.Exec(
		"INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs) VALUES (?, ?, 'disponible', ?)",
		reference, capacite, idConteneur,
	)
	return err
}

// ModifierConteneur met à jour les champs du conteneur ; renvoie le nombre de
// lignes touchées pour distinguer « modifié » de « introuvable » (404).
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

// SyncBoxCapacite aligne la capacité des box d'un conteneur sur sa nouvelle
// capacité : sans cela l'édition de capacité côté UI ne changerait rien à la
// capacité RÉELLE de dépôt (qui est portée par les box).
func (ConteneurRepo) SyncBoxCapacite(q Querier, idConteneur, capacite int) error {
	_, err := q.Exec("UPDATE Box SET Capacite=? WHERE Id_Conteneurs=?", capacite, idConteneur)
	return err
}

// CompterObjetsConteneur compte TOUS les objets rattachés au conteneur (quel que
// soit leur statut) : un conteneur ayant un historique d'objets n'est pas
// supprimable (intégrité référentielle + piste d'audit).
func (ConteneurRepo) CompterObjetsConteneur(q Querier, idConteneur int) (int, error) {
	var n int
	err := q.QueryRow("SELECT COUNT(*) FROM Objets WHERE Id_Conteneurs = ?", idConteneur).Scan(&n)
	return n, err
}

// CompterDemandesActives compte les demandes non terminales (en attente ou
// validées) rattachées au conteneur — elles bloquent la suppression.
func (ConteneurRepo) CompterDemandesActives(q Querier, idConteneur int) (int, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Demandes_conteneurs WHERE Id_Conteneurs = ? AND Statut IN ('en_attente','validee')",
		idConteneur,
	).Scan(&n)
	return n, err
}

// SupprimerBoxesConteneur supprime les box d'un conteneur. N'est appelé qu'après
// avoir vérifié qu'aucun objet n'y est rattaché (sinon fk_objets_box bloquerait).
func (ConteneurRepo) SupprimerBoxesConteneur(q Querier, idConteneur int) error {
	_, err := q.Exec("DELETE FROM Box WHERE Id_Conteneurs = ?", idConteneur)
	return err
}

// SupprimerConteneur supprime le conteneur ; renvoie le nombre de lignes pour
// distinguer « supprimé » de « introuvable » (404).
func (ConteneurRepo) SupprimerConteneur(q Querier, idConteneur int) (int64, error) {
	res, err := q.Exec("DELETE FROM Conteneurs WHERE Id_Conteneurs = ?", idConteneur)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}
