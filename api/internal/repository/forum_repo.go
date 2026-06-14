package repository

import "database/sql"

type ForumRepo struct{}

type SujetLigne struct {
	ID           int
	Titre        string
	Contenu      string
	Categorie    string
	Statut       string
	Date         sql.NullTime
	Vues         int
	AuteurNom    string
	AuteurPrenom string
	NbReponses   int
}

type SujetEntete struct {
	ID           int
	Titre        string
	Contenu      string
	Categorie    string
	Statut       string
	Date         sql.NullTime
	Vues         int
	IdAuteur     int
	AuteurNom    string
	AuteurPrenom string
}

type ReponseLigne struct {
	ID           int
	Contenu      string
	Date         sql.NullTime
	EstSolution  bool
	IdAuteur     int
	AuteurNom    string
	AuteurPrenom string
	AuteurStatut string
}

func (ForumRepo) ListerSujets(q Querier, categorie string) ([]SujetLigne, error) {
	base := `
		SELECT s.Id_Sujets, s.Titre, COALESCE(s.Contenu,''), COALESCE(s.Categorie,'general'),
			COALESCE(s.Statut,'ouvert'), s.Date_Creation, COALESCE(s.Vues,0),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,''),
			COUNT(rep.Id_Reponses) AS nb_reponses
		FROM Sujets s
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		LEFT JOIN Reponses rep ON rep.Id_Sujets = s.Id_Sujets`
	args := []interface{}{}
	if categorie != "" && categorie != "tous" {
		base += " WHERE s.Categorie = ?"
		args = append(args, categorie)
	}
	base += " GROUP BY s.Id_Sujets ORDER BY s.Date_Creation DESC"

	rows, err := q.Query(base, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	out := []SujetLigne{}
	for rows.Next() {
		var s SujetLigne
		if err := rows.Scan(&s.ID, &s.Titre, &s.Contenu, &s.Categorie, &s.Statut,
			&s.Date, &s.Vues, &s.AuteurNom, &s.AuteurPrenom, &s.NbReponses); err != nil {
			return nil, err
		}
		out = append(out, s)
	}
	return out, rows.Err()
}

func (ForumRepo) SujetParID(q Querier, idSujet int) (SujetEntete, error) {
	var s SujetEntete
	err := q.QueryRow(`
		SELECT s.Id_Sujets, s.Titre, COALESCE(s.Contenu,''), COALESCE(s.Categorie,'general'),
			COALESCE(s.Statut,'ouvert'), s.Date_Creation, COALESCE(s.Vues,0),
			COALESCE(s.Id_Utilisateurs,0), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Sujets s
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		WHERE s.Id_Sujets = ?`, idSujet,
	).Scan(&s.ID, &s.Titre, &s.Contenu, &s.Categorie, &s.Statut, &s.Date, &s.Vues,
		&s.IdAuteur, &s.AuteurNom, &s.AuteurPrenom)
	return s, err
}

func (ForumRepo) ReponsesDuSujet(q Querier, idSujet int) ([]ReponseLigne, error) {
	rows, err := q.Query(`
		SELECT r.Id_Reponses, COALESCE(r.Contenu,''), r.Date_, COALESCE(r.Est_Solution,0),
			COALESCE(r.Id_Utilisateurs,0), COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Statut,'')
		FROM Reponses r
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = r.Id_Utilisateurs
		WHERE r.Id_Sujets = ?
		ORDER BY r.Est_Solution DESC, r.Date_ ASC`, idSujet,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	out := []ReponseLigne{}
	for rows.Next() {
		var r ReponseLigne
		var sol int
		if err := rows.Scan(&r.ID, &r.Contenu, &r.Date, &sol, &r.IdAuteur,
			&r.AuteurNom, &r.AuteurPrenom, &r.AuteurStatut); err != nil {
			return nil, err
		}
		r.EstSolution = sol == 1
		out = append(out, r)
	}
	return out, rows.Err()
}

func (ForumRepo) SujetStatutAuteurPourMAJ(q Querier, idSujet int) (statut string, idAuteur int, err error) {
	err = q.QueryRow(
		"SELECT COALESCE(Statut,'ouvert'), COALESCE(Id_Utilisateurs,0) FROM Sujets WHERE Id_Sujets = ? FOR UPDATE",
		idSujet,
	).Scan(&statut, &idAuteur)
	return statut, idAuteur, err
}

func (ForumRepo) ReponseDansSujet(q Querier, idReponse, idSujet int) (bool, error) {
	var existe bool
	err := q.QueryRow(
		"SELECT EXISTS(SELECT 1 FROM Reponses WHERE Id_Reponses = ? AND Id_Sujets = ?)",
		idReponse, idSujet,
	).Scan(&existe)
	return existe, err
}

func (ForumRepo) IncrementerVues(q Querier, idSujet int) error {
	_, err := q.Exec("UPDATE Sujets SET Vues = COALESCE(Vues,0) + 1 WHERE Id_Sujets = ?", idSujet)
	return err
}

func (ForumRepo) CreerSujet(q Querier, idUtilisateur int, titre, contenu, categorie string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Sujets (Titre, Contenu, Categorie, Statut, Date_Creation, Vues, Id_Forum, Id_Utilisateurs) VALUES (?,?,?,?,NOW(),0,1,?)",
		titre, contenu, categorie, "ouvert", idUtilisateur,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ForumRepo) CreerReponse(q Querier, idSujet, idUtilisateur int, contenu string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Reponses (Contenu, Date_, Est_Solution, Id_Sujets, Id_Utilisateurs) VALUES (?,NOW(),0,?,?)",
		contenu, idSujet, idUtilisateur,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ForumRepo) ReinitialiserSolutions(q Querier, idSujet int) error {
	_, err := q.Exec("UPDATE Reponses SET Est_Solution = 0 WHERE Id_Sujets = ?", idSujet)
	return err
}

func (ForumRepo) MarquerReponseSolution(q Querier, idReponse int) error {
	_, err := q.Exec("UPDATE Reponses SET Est_Solution = 1 WHERE Id_Reponses = ?", idReponse)
	return err
}

func (ForumRepo) MajStatutSujet(q Querier, idSujet int, statut string) error {
	_, err := q.Exec("UPDATE Sujets SET Statut = ? WHERE Id_Sujets = ?", statut, idSujet)
	return err
}

func (ForumRepo) SupprimerReponsesDuSujet(q Querier, idSujet int) error {
	_, err := q.Exec("DELETE FROM Reponses WHERE Id_Sujets = ?", idSujet)
	return err
}

func (ForumRepo) SupprimerSujet(q Querier, idSujet int) (int64, error) {
	res, err := q.Exec("DELETE FROM Sujets WHERE Id_Sujets = ?", idSujet)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

func (ForumRepo) AuteurReponse(q Querier, idReponse int) (int, error) {
	var idUtilisateur int
	err := q.QueryRow("SELECT Id_Utilisateurs FROM Reponses WHERE Id_Reponses = ?", idReponse).Scan(&idUtilisateur)
	return idUtilisateur, err
}

func (ForumRepo) SupprimerReponse(q Querier, idReponse int) (int64, error) {
	res, err := q.Exec("DELETE FROM Reponses WHERE Id_Reponses = ?", idReponse)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}
