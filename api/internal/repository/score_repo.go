package repository

import "upcycleconnect/internal/domain"

// ScoreRepo isole l'accès SQL de la gamification : résolution du particulier et
// comptage de son activité. Aucun calcul de points ici (c'est le domaine) — le
// repo ne fait que LIRE les volumes.
type ScoreRepo struct{}

// ResoudreParticulier mappe un Id_Utilisateurs vers son Id_Particuliers. Renvoie
// sql.ErrNoRows si l'utilisateur n'est pas un particulier (admin, pro, salarié) :
// le service traite ce cas comme « aucune activité » (score 0), pas une erreur.
func (ScoreRepo) ResoudreParticulier(q Querier, idUtilisateur int) (int, error) {
	var idP int
	err := q.QueryRow("SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", idUtilisateur).Scan(&idP)
	return idP, err
}

// Activite compte les volumes qui alimentent le score. Les dépôts et annonces ne
// comptent qu'une fois VALIDÉS ; les sujets de forum se comptent par utilisateur
// (le forum est ouvert au-delà des particuliers), d'où le double identifiant.
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
