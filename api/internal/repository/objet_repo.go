package repository

import (
	"database/sql"

	"upcycleconnect/internal/domain"
)

type ObjetRepo struct{}

type ObjetLigne struct {
	ID           int
	Type         string
	Poids        string
	Statut       string
	IdPro        sql.NullInt64
	IdConteneur  int
	Conteneur    string
	CodeBarre    string
	TitreAnnonce string
	TypeAnnonce  string
}

func (ObjetRepo) ListerDisponibles(q Querier, idConteneur int) ([]ObjetLigne, error) {
	base := `SELECT o.Id_Objets, COALESCE(o.Type,''), COALESCE(o.Poids,''),
	                COALESCE(o.Statut,'en_stock'), o.Id_Professionnels,
	                o.Id_Conteneurs, COALESCE(c.Localisation,''),
	                COALESCE((SELECT cb.Code FROM Codes_Barres cb
	                          WHERE cb.Id_Objets = o.Id_Objets AND cb.Statut = 'active'
	                          ORDER BY cb.Id_Codes_Barres DESC LIMIT 1),''),
	                COALESCE(a.Titre,''), COALESCE(a.Type_annonce,'')
	         FROM Objets o
	         JOIN Conteneurs c ON c.Id_Conteneurs = o.Id_Conteneurs
	         LEFT JOIN Demandes_conteneurs d ON d.Id_Demandes_conteneurs = o.Id_Demandes_conteneurs
	         LEFT JOIN Annonces a ON a.Id_Annonces = d.Id_Annonces
	         WHERE o.Statut = 'en_stock'`
	args := []interface{}{}
	if idConteneur > 0 {
		base += " AND o.Id_Conteneurs = ?"
		args = append(args, idConteneur)
	}
	base += " ORDER BY o.Id_Objets DESC"
	return scanObjets(q, base, args...)
}

func (ObjetRepo) ListerParPro(q Querier, idPro int) ([]ObjetLigne, error) {
	const base = `SELECT o.Id_Objets, COALESCE(o.Type,''), COALESCE(o.Poids,''),
	                     COALESCE(o.Statut,'en_stock'), o.Id_Professionnels,
	                     o.Id_Conteneurs, COALESCE(c.Localisation,''),
	                     COALESCE((SELECT cb.Code FROM Codes_Barres cb
	                               WHERE cb.Id_Objets = o.Id_Objets AND cb.Statut = 'active'
	                               ORDER BY cb.Id_Codes_Barres DESC LIMIT 1),''),
	                     COALESCE(a.Titre,''), COALESCE(a.Type_annonce,'')
	              FROM Objets o
	              JOIN Conteneurs c ON c.Id_Conteneurs = o.Id_Conteneurs
	              LEFT JOIN Demandes_conteneurs d ON d.Id_Demandes_conteneurs = o.Id_Demandes_conteneurs
	              LEFT JOIN Annonces a ON a.Id_Annonces = d.Id_Annonces
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
		if err := rows.Scan(&o.ID, &o.Type, &o.Poids, &o.Statut, &o.IdPro, &o.IdConteneur, &o.Conteneur, &o.CodeBarre, &o.TitreAnnonce, &o.TypeAnnonce); err != nil {
			return nil, err
		}
		liste = append(liste, o)
	}
	return liste, rows.Err()
}

func (ObjetRepo) ObjetPourMAJ(q Querier, idObjet int) (domain.ObjetSnapshot, error) {
	var s domain.ObjetSnapshot
	err := q.QueryRow(
		`SELECT Id_Objets, COALESCE(Statut,'en_stock'), COALESCE(Id_Professionnels,0)
		 FROM Objets WHERE Id_Objets = ? FOR UPDATE`,
		idObjet,
	).Scan(&s.ID, &s.Statut, &s.IdProprietairePro)
	return s, err
}

func (ObjetRepo) Reserver(q Querier, idObjet, idPro int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='reserve_pro', Id_Professionnels=? WHERE Id_Objets=? AND Statut='en_stock'",
		idPro, idObjet,
	)
	return err
}

func (ObjetRepo) Recuperer(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='recupere' WHERE Id_Objets=? AND Statut='reserve_pro'",
		idObjet,
	)
	return err
}

func (ObjetRepo) AnnulerReservation(q Querier, idObjet int) error {
	_, err := q.Exec(
		"UPDATE Objets SET Statut='en_stock', Id_Professionnels=NULL WHERE Id_Objets=? AND Statut='reserve_pro'",
		idObjet,
	)
	return err
}
