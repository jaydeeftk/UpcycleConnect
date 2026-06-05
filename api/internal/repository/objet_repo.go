package repository

import (
	"database/sql"

	"upcycleconnect/internal/domain"
)

// ObjetRepo : accès SQL du vertical RÉCUPÉRATION PRO (cycle de vie d'un objet
// déposé côté professionnel : reserver / recuperer / annuler). Sans état — chaque
// méthode reçoit le Querier (DB ou Tx) afin que les transitions s'exécutent dans
// la transaction et sous le verrou FOR UPDATE ouverts par le service.
type ObjetRepo struct{}

// ObjetLigne : projection de liste (catalogue d'objets disponibles d'un pro et ses
// propres réservations). IdPro est nullable (NULL tant qu'aucun pro n'a réservé).
type ObjetLigne struct {
	ID          int
	Type        string
	Poids       string
	Statut      string
	IdPro       sql.NullInt64
	IdConteneur int
	Conteneur   string
}

// ListerDisponibles renvoie les objets en_stock (le catalogue à réserver). Si
// idConteneur > 0, la liste est restreinte à ce conteneur.
func (ObjetRepo) ListerDisponibles(q Querier, idConteneur int) ([]ObjetLigne, error) {
	base := `SELECT o.Id_Objets, COALESCE(o.Type,''), COALESCE(o.Poids,''),
	                COALESCE(o.Statut,'en_stock'), o.Id_Professionnels,
	                o.Id_Conteneurs, COALESCE(c.Localisation,'')
	         FROM Objets o
	         JOIN Conteneurs c ON c.Id_Conteneurs = o.Id_Conteneurs
	         WHERE o.Statut = 'en_stock'`
	args := []interface{}{}
	if idConteneur > 0 {
		base += " AND o.Id_Conteneurs = ?"
		args = append(args, idConteneur)
	}
	base += " ORDER BY o.Id_Objets DESC"
	return scanObjets(q, base, args...)
}

// ListerParPro renvoie les objets réservés OU récupérés par CE professionnel
// (Id_Professionnels = idPro) : l'historique de récupération du pro.
func (ObjetRepo) ListerParPro(q Querier, idPro int) ([]ObjetLigne, error) {
	const base = `SELECT o.Id_Objets, COALESCE(o.Type,''), COALESCE(o.Poids,''),
	                     COALESCE(o.Statut,'en_stock'), o.Id_Professionnels,
	                     o.Id_Conteneurs, COALESCE(c.Localisation,'')
	              FROM Objets o
	              JOIN Conteneurs c ON c.Id_Conteneurs = o.Id_Conteneurs
	              WHERE o.Id_Professionnels = ? AND o.Statut IN ('reserve_pro','recupere')
	              ORDER BY o.Id_Objets DESC`
	return scanObjets(q, base, idPro)
}

func scanObjets(q Querier, query string, args ...interface{}) ([]ObjetLigne, error) {
	rows, err := q.Query(query, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	liste := []ObjetLigne{}
	for rows.Next() {
		var o ObjetLigne
		if err := rows.Scan(&o.ID, &o.Type, &o.Poids, &o.Statut, &o.IdPro, &o.IdConteneur, &o.Conteneur); err != nil {
			return nil, err
		}
		liste = append(liste, o)
	}
	return liste, rows.Err()
}

// ObjetPourMAJ lit l'état d'un objet sous FOR UPDATE avant transition. Renvoie
// sql.ErrNoRows si l'objet n'existe pas (le service en fait un 404). Id_Professionnels
// nullable est ramené à 0 via COALESCE pour alimenter ObjetSnapshot.
func (ObjetRepo) ObjetPourMAJ(q Querier, idObjet int) (domain.ObjetSnapshot, error) {
	var s domain.ObjetSnapshot
	err := q.QueryRow(
		`SELECT Id_Objets, COALESCE(Statut,'en_stock'), COALESCE(Id_Professionnels,0)
		 FROM Objets WHERE Id_Objets = ? FOR UPDATE`,
		idObjet,
	).Scan(&s.ID, &s.Statut, &s.IdProprietairePro)
	return s, err
}

// Reserver applique en_stock -> reserve_pro et pose le propriétaire (idPro). La
// garde « AND Statut='en_stock' » est une ceinture de sécurité (la ligne est déjà
// verrouillée et le domaine a déjà validé l'état).
func (ObjetRepo) Reserver(q Querier, idObjet, idPro int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='reserve_pro', Id_Professionnels=? WHERE Id_Objets=? AND Statut='en_stock'",
		idPro, idObjet,
	)
	return err
}

// Recuperer applique reserve_pro -> recupere. Le propriétaire (Id_Professionnels)
// est conservé : piste d'audit de qui a récupéré l'objet.
func (ObjetRepo) Recuperer(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='recupere' WHERE Id_Objets=? AND Statut='reserve_pro'",
		idObjet,
	)
	return err
}

// AnnulerReservation applique reserve_pro -> en_stock et libère le propriétaire
// (Id_Professionnels = NULL) : l'objet redevient disponible pour tous.
func (ObjetRepo) AnnulerReservation(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='en_stock', Id_Professionnels=NULL WHERE Id_Objets=? AND Statut='reserve_pro'",
		idObjet,
	)
	return err
}
