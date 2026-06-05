package repository

import (
	"errors"

	"github.com/go-sql-driver/mysql"

	"upcycleconnect/internal/domain"
)

// CodeBarreRepo : accès SQL du vertical CODE-BARRES (génération à la
// matérialisation d'un objet, résolution lors d'une récupération par scan, et
// consommation). Sans état — chaque méthode reçoit le Querier (DB ou Tx) pour
// s'exécuter dans la transaction et sous les verrous ouverts par le service.
type CodeBarreRepo struct{}

// EstViolationUnicite : collision sur uq_codebarres_code (ER_DUP_ENTRY 1062). Le
// service s'en sert pour regénérer un code sans jamais renvoyer un 500.
func (CodeBarreRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeMySQLDuplicate
}

// Creer insère un code-barres 'active' rattaché à un objet. Une collision sur
// uq_codebarres_code remonte telle quelle (le service réessaie une génération).
func (CodeBarreRepo) Creer(q Querier, idObjet int, code string) error {
	_, err := q.Exec(
		`INSERT INTO Codes_Barres (Code, Date_generation, Statut, Id_Objets)
		 VALUES (?, NOW(), ?, ?)`,
		code, domain.StatutCodeBarreActive, idObjet,
	)
	return err
}

// ResoudrePourMAJ verrouille le code-barres ET l'objet qu'il désigne (FOR UPDATE
// via la jointure) et renvoie l'instantané joint. Renvoie sql.ErrNoRows si le
// code est inconnu (le service en fait un 404).
func (CodeBarreRepo) ResoudrePourMAJ(q Querier, code string) (domain.CodeBarreSnapshot, error) {
	var s domain.CodeBarreSnapshot
	err := q.QueryRow(
		`SELECT cb.Id_Codes_Barres, COALESCE(cb.Statut,'active'),
		        o.Id_Objets, COALESCE(o.Statut,'en_stock'), COALESCE(o.Id_Professionnels,0)
		 FROM Codes_Barres cb
		 JOIN Objets o ON o.Id_Objets = cb.Id_Objets
		 WHERE cb.Code = ?
		 FOR UPDATE`,
		code,
	).Scan(&s.ID, &s.Statut, &s.IdObjet, &s.StatutObjet, &s.IdProprietairePro)
	return s, err
}

// MarquerUtilise consomme un code (active -> utilise) par son identifiant. La
// garde « AND Statut='active' » est une ceinture (la ligne est déjà verrouillée).
func (CodeBarreRepo) MarquerUtilise(q Querier, idCodeBarre int) error {
	_, err := q.Exec(
		"UPDATE Codes_Barres SET Statut=? WHERE Id_Codes_Barres=? AND Statut=?",
		domain.StatutCodeBarreUtilise, idCodeBarre, domain.StatutCodeBarreActive,
	)
	return err
}

// MarquerUtiliseParObjet consomme le(s) code(s) actif(s) d'un objet. Sert quand
// la récupération part de l'identifiant d'objet (et non d'un scan) : le
// code-barres reste cohérent avec l'objet récupéré (objet recupere <-> code utilise).
func (CodeBarreRepo) MarquerUtiliseParObjet(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Codes_Barres SET Statut=? WHERE Id_Objets=? AND Statut=?",
		domain.StatutCodeBarreUtilise, idObjet, domain.StatutCodeBarreActive,
	)
	return err
}
