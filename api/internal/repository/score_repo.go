package repository

import "upcycleconnect/internal/domain"

type ScoreRepo struct{}

type ParticulierLigne struct {
	IdUtilisateur int
	IdParticulier int
	Nom           string
}

func (ScoreRepo) ListerParticuliers(q Querier) ([]ParticulierLigne, error) {
	rows, err := q.Query(
		`SELECT p.Id_Utilisateurs, p.Id_Particuliers, TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,'')))
		 FROM Particuliers p JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ParticulierLigne{}
	for rows.Next() {
		var l ParticulierLigne
		if err := rows.Scan(&l.IdUtilisateur, &l.IdParticulier, &l.Nom); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (ScoreRepo) ResoudreParticulier(q Querier, idUtilisateur int) (int, error) {
	var idP int
	err := q.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idP)
	return idP, err
}

func (ScoreRepo) Activite(q Querier, idParticulier, idUtilisateur int) (domain.ActiviteParticulier, error) {
	var a domain.ActiviteParticulier
	var err error
	if a.Annonces, err = compte(q, "SELECT COUNT(*) FROM Annonces WHERE Id_Particuliers = ? AND Statut = 'validee'", idParticulier); err != nil {
		return a, err
	}
	if a.Evenements, err = compte(q, "SELECT COUNT(*) FROM Participer_evenements WHERE Id_Particuliers = ?", idParticulier); err != nil {
		return a, err
	}
	if a.Sujets, err = compte(q, "SELECT COUNT(*) FROM Sujets WHERE Id_Utilisateurs = ?", idUtilisateur); err != nil {
		return a, err
	}
	if a.Depots, err = compte(q, "SELECT COUNT(*) FROM Demandes_conteneurs WHERE Id_Particuliers = ? AND Statut = 'validee'", idParticulier); err != nil {
		return a, err
	}
	if a.Formations, err = compte(q, "SELECT COUNT(*) FROM Reserver_formation WHERE Id_Particuliers = ?", idParticulier); err != nil {
		return a, err
	}
	if a.DonsReserves, err = compte(q, "SELECT COUNT(*) FROM Annonces WHERE Id_Acheteur_Utilisateur = ? AND Type_annonce = 'don' AND Statut IN ('reservee','vendue')", idUtilisateur); err != nil {
		return a, err
	}
	if a.PrestationsAchetees, err = compte(q, "SELECT COUNT(*) FROM Commandes_Services WHERE Id_Utilisateurs = ? AND Statut != 'en_attente_paiement'", idUtilisateur); err != nil {
		return a, err
	}
	if a.DevisAcceptes, err = compte(q,
		`SELECT COUNT(*) FROM Demandes_prestations dp
		 JOIN Devis d ON d.Id_Demandes_prestations = dp.Id_Demandes_prestations
		 WHERE dp.Id_Utilisateurs = ? AND d.Statut = 'accepte'`, idUtilisateur); err != nil {
		return a, err
	}
	return a, nil
}

func compte(q Querier, requete string, args ...any) (int, error) {
	var n int
	err := q.QueryRow(requete, args...).Scan(&n)
	return n, err
}
