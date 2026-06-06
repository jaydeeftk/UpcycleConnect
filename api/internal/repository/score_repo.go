package repository

import "upcycleconnect/internal/domain"

type ScoreRepo struct{}

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
	return a, nil
}

func compte(q Querier, requete string, args ...any) (int, error) {
	var n int
	err := q.QueryRow(requete, args...).Scan(&n)
	return n, err
}
