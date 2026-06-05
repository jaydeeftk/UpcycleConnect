package repository

import "database/sql"

// ForumRepo isole l'accès SQL du vertical Forum (Sujets / Reponses). Aucune règle
// métier ici : uniquement lecture/écriture, chaque méthode acceptant un Querier
// (DB ou Tx) pour fonctionner dans ou hors transaction.
type ForumRepo struct{}

// ---------------------------------------------------------------------------
// Lignes brutes renvoyées par le repo (dates en sql.NullTime : le formatage
// RFC3339 est fait par le service, comme le reste du code).
// ---------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------
// Lectures
// ---------------------------------------------------------------------------

// ListerSujets renvoie les sujets (avec le compte de réponses), filtrés par
// catégorie si fournie. LEFT JOIN sur l'auteur : un sujet dont l'auteur n'aurait
// pas été rattaché reste visible (l'admin doit pouvoir le modérer).
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

// SujetParID renvoie l'entête d'un sujet (sql.ErrNoRows si absent).
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

// ReponsesDuSujet renvoie les réponses d'un sujet, la solution d'abord.
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

// SujetStatutAuteurPourMAJ verrouille le sujet (FOR UPDATE) et renvoie son statut
// courant et l'identifiant de son auteur — base des décisions de transition et
// d'autorisation sous verrou. sql.ErrNoRows si le sujet est absent.
func (ForumRepo) SujetStatutAuteurPourMAJ(q Querier, idSujet int) (statut string, idAuteur int, err error) {
	err = q.QueryRow(
		"SELECT COALESCE(Statut,'ouvert'), COALESCE(Id_Utilisateurs,0) FROM Sujets WHERE Id_Sujets = ? FOR UPDATE",
		idSujet,
	).Scan(&statut, &idAuteur)
	return statut, idAuteur, err
}

// ReponseDansSujet : la réponse appartient-elle bien à ce sujet ? Garde contre la
// désignation d'une solution étrangère au fil.
func (ForumRepo) ReponseDansSujet(q Querier, idReponse, idSujet int) (bool, error) {
	var existe bool
	err := q.QueryRow(
		"SELECT EXISTS(SELECT 1 FROM Reponses WHERE Id_Reponses = ? AND Id_Sujets = ?)",
		idReponse, idSujet,
	).Scan(&existe)
	return existe, err
}

// ---------------------------------------------------------------------------
// Écritures
// ---------------------------------------------------------------------------

// IncrementerVues : compteur de consultations (effet de bord assumé d'un GET de
// détail). Id_Forum reste implicite (forum unique seedé Id=1).
func (ForumRepo) IncrementerVues(q Querier, idSujet int) error {
	_, err := q.Exec("UPDATE Sujets SET Vues = COALESCE(Vues,0) + 1 WHERE Id_Sujets = ?", idSujet)
	return err
}

// CreerSujet insère un sujet OUVERT au nom de l'utilisateur (identité du JWT).
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

// CreerReponse insère une réponse au nom de l'utilisateur (identité du JWT).
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

// ReinitialiserSolutions retire la marque de solution sur toutes les réponses d'un
// sujet (étape 1 du marquage : une seule solution à la fois).
func (ForumRepo) ReinitialiserSolutions(q Querier, idSujet int) error {
	_, err := q.Exec("UPDATE Reponses SET Est_Solution = 0 WHERE Id_Sujets = ?", idSujet)
	return err
}

// MarquerReponseSolution pose la marque de solution sur une réponse.
func (ForumRepo) MarquerReponseSolution(q Querier, idReponse int) error {
	_, err := q.Exec("UPDATE Reponses SET Est_Solution = 1 WHERE Id_Reponses = ?", idReponse)
	return err
}

// MajStatutSujet écrit le statut cible (le vocabulaire est garanti par le domaine
// puis par chk_sujets_statut).
func (ForumRepo) MajStatutSujet(q Querier, idSujet int, statut string) error {
	_, err := q.Exec("UPDATE Sujets SET Statut = ? WHERE Id_Sujets = ?", statut, idSujet)
	return err
}

// SupprimerReponsesDuSujet retire les réponses d'un sujet (étape de la suppression
// de sujet : pas de réponse orpheline).
func (ForumRepo) SupprimerReponsesDuSujet(q Querier, idSujet int) error {
	_, err := q.Exec("DELETE FROM Reponses WHERE Id_Sujets = ?", idSujet)
	return err
}

// SupprimerSujet retire un sujet ; renvoie le nombre de lignes touchées (0 => 404).
func (ForumRepo) SupprimerSujet(q Querier, idSujet int) (int64, error) {
	res, err := q.Exec("DELETE FROM Sujets WHERE Id_Sujets = ?", idSujet)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

// SupprimerReponse retire une réponse ; renvoie le nombre de lignes touchées.
func (ForumRepo) SupprimerReponse(q Querier, idReponse int) (int64, error) {
	res, err := q.Exec("DELETE FROM Reponses WHERE Id_Reponses = ?", idReponse)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}
