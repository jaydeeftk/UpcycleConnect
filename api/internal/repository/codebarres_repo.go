package repository

import (
	"errors"

	"github.com/go-sql-driver/mysql"

	"upcycleconnect/internal/domain"
)

type CodeBarreRepo struct{}

func (CodeBarreRepo) EstViolationUnicite(err error) bool {
	var me *mysql.MySQLError
	return errors.As(err, &me) && me.Number == codeMySQLDuplicate
}

func (CodeBarreRepo) Creer(q Querier, idObjet int, code string, idBox int) error {
	_, err := q.Exec(
		`INSERT INTO Codes_Barres (Code, Date_generation, Statut, Id_Objets, Id_Box)
		 VALUES (?, NOW(), ?, ?, ?)`,
		code, domain.StatutCodeBarreActive, idObjet, idBox,
	)
	return err
}

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

func (CodeBarreRepo) MarquerUtilise(q Querier, idCodeBarre int) error {
	_, err := q.Exec(
		"UPDATE Codes_Barres SET Statut=? WHERE Id_Codes_Barres=? AND Statut=?",
		domain.StatutCodeBarreUtilise, idCodeBarre, domain.StatutCodeBarreActive,
	)
	return err
}

func (CodeBarreRepo) MarquerUtiliseParObjet(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Codes_Barres SET Statut=? WHERE Id_Objets=? AND Statut=?",
		domain.StatutCodeBarreUtilise, idObjet, domain.StatutCodeBarreActive,
	)
	return err
}
