package repository

import (
	"strings"

	"upcycleconnect/internal/domain"
)

// ProjetRepo isole l'accès SQL des projets d'upcycling et de leurs étapes. Aucune
// règle métier ici : la décision « autorisé/refusé » (propriété, état, figé) est
// prise par la couche service à partir des snapshots lus ci-dessous.
type ProjetRepo struct{}

// ProjetLigne : projection de liste pour le dashboard pro. nb_etapes est dérivé
// par agrégat. Les noms de champs DTO (id/titre/description/statut/date_debut/
// nb_etapes) sont préservés pour rester compatibles avec le front existant.
type ProjetLigne struct {
	ID          int
	Titre       string
	Description string
	Statut      string
	DateDebut   string
	NbEtapes    int
}

// ProjetCreation : données d'insertion d'un projet. DateDebut vide -> NULL.
type ProjetCreation struct {
	Titre       string
	Description string
	DateDebut   string
	Statut      string
	IdPro       int
}

// EtapeLigne : projection d'une étape illustrée d'un projet.
type EtapeLigne struct {
	ID          int
	Nom         string
	Description string
	Visuel      string
}

// EtapeCreation : données d'insertion d'une étape.
type EtapeCreation struct {
	Nom         string
	Description string
	Visuel      string
}

// ListerParPro renvoie les projets d'UN professionnel, étapes comptées. Le
// filtre WHERE Id_Professionnels = ? est la frontière de propriété en lecture :
// un pro ne voit jamais les projets d'un autre.
func (ProjetRepo) ListerParPro(q Querier, idPro int) ([]ProjetLigne, error) {
	rows, err := q.Query(
		`SELECT p.Id_Projets, COALESCE(p.Titre,''), COALESCE(p.Description,''),
			COALESCE(p.Statut,'en_cours'), COALESCE(p.Date_Debut,''),
			COUNT(e.Id_Etapes) AS nb_etapes
		 FROM Projets p
		 LEFT JOIN Etapes e ON e.Id_Projets = p.Id_Projets
		 WHERE p.Id_Professionnels = ?
		 GROUP BY p.Id_Projets
		 ORDER BY p.Id_Projets DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	liste := []ProjetLigne{}
	for rows.Next() {
		var p ProjetLigne
		if err := rows.Scan(&p.ID, &p.Titre, &p.Description, &p.Statut, &p.DateDebut, &p.NbEtapes); err != nil {
			return nil, err
		}
		liste = append(liste, p)
	}
	return liste, rows.Err()
}

// Creer insère un projet et renvoie son identifiant. Une Date_Debut vide est
// écrite NULL (et non ”) : insérer ” dans un DATETIME échouerait en mode SQL
// strict — c'était un 500 latent du code d'origine.
func (ProjetRepo) Creer(q Querier, in ProjetCreation) (int, error) {
	var dateArg interface{}
	if strings.TrimSpace(in.DateDebut) != "" {
		dateArg = in.DateDebut
	}
	res, err := q.Exec(
		"INSERT INTO Projets (Titre, Description, Date_Debut, Statut, Id_Professionnels) VALUES (?,?,?,?,?)",
		in.Titre, in.Description, dateArg, in.Statut, in.IdPro,
	)
	if err != nil {
		return 0, err
	}
	id, err := res.LastInsertId()
	return int(id), err
}

// ChargerProjet lit l'état d'un projet SANS verrou (chemin lecture : vérifier la
// propriété avant de lister les étapes). Renvoie sql.ErrNoRows si inexistant.
func (ProjetRepo) ChargerProjet(q Querier, idProjet int) (domain.ProjetSnapshot, error) {
	return scanProjet(q.QueryRow(
		"SELECT Id_Projets, COALESCE(Statut,'en_cours'), Id_Professionnels FROM Projets WHERE Id_Projets = ?",
		idProjet,
	))
}

// ProjetPourMAJ lit l'état d'un projet SOUS VERROU (FOR UPDATE) avant une
// transition ou une mutation : la ligne est figée jusqu'au commit, ce qui
// sérialise deux écritures concurrentes sur le même projet. ErrNoRows si absent.
func (ProjetRepo) ProjetPourMAJ(q Querier, idProjet int) (domain.ProjetSnapshot, error) {
	return scanProjet(q.QueryRow(
		"SELECT Id_Projets, COALESCE(Statut,'en_cours'), Id_Professionnels FROM Projets WHERE Id_Projets = ? FOR UPDATE",
		idProjet,
	))
}

type rowScanner interface {
	Scan(dest ...any) error
}

func scanProjet(row rowScanner) (domain.ProjetSnapshot, error) {
	var s domain.ProjetSnapshot
	err := row.Scan(&s.ID, &s.Statut, &s.IdProprietairePro)
	return s, err
}

// MettreAJourStatut applique une transition d'état déjà validée et autorisée par
// le service (la ligne est verrouillée). Le contenu n'est pas touché.
func (ProjetRepo) MettreAJourStatut(q Querier, idProjet int, statut string) error {
	_, err := q.Exec("UPDATE Projets SET Statut = ? WHERE Id_Projets = ?", statut, idProjet)
	return err
}

// MettreAJourContenu met à jour le titre et la description. Le statut n'est PAS
// modifiable par cette voie : il ne change que par transition explicite.
func (ProjetRepo) MettreAJourContenu(q Querier, idProjet int, titre, description string) error {
	_, err := q.Exec("UPDATE Projets SET Titre = ?, Description = ? WHERE Id_Projets = ?", titre, description, idProjet)
	return err
}

// SupprimerEtapesDuProjet retire les étapes d'un projet (préalable à la
// suppression du projet, la FK Etapes->Projets interdisant l'ordre inverse).
func (ProjetRepo) SupprimerEtapesDuProjet(q Querier, idProjet int) error {
	_, err := q.Exec("DELETE FROM Etapes WHERE Id_Projets = ?", idProjet)
	return err
}

// Supprimer retire le projet lui-même. La propriété a été vérifiée par le service
// sous verrou avant l'appel.
func (ProjetRepo) Supprimer(q Querier, idProjet int) error {
	_, err := q.Exec("DELETE FROM Projets WHERE Id_Projets = ?", idProjet)
	return err
}

// ListerEtapes renvoie les étapes d'un projet, dans l'ordre de création (séquence
// pédagogique du tutoriel). La propriété est vérifiée en amont par le service.
func (ProjetRepo) ListerEtapes(q Querier, idProjet int) ([]EtapeLigne, error) {
	rows, err := q.Query(
		"SELECT Id_Etapes, COALESCE(Nom,''), COALESCE(Description,''), COALESCE(Visuel,'') FROM Etapes WHERE Id_Projets = ? ORDER BY Id_Etapes ASC",
		idProjet,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	liste := []EtapeLigne{}
	for rows.Next() {
		var e EtapeLigne
		if err := rows.Scan(&e.ID, &e.Nom, &e.Description, &e.Visuel); err != nil {
			return nil, err
		}
		liste = append(liste, e)
	}
	return liste, rows.Err()
}

// CreerEtape insère une étape dans un projet et renvoie son identifiant.
func (ProjetRepo) CreerEtape(q Querier, idProjet int, in EtapeCreation) (int, error) {
	res, err := q.Exec(
		"INSERT INTO Etapes (Nom, Description, Visuel, Id_Projets) VALUES (?,?,?,?)",
		in.Nom, in.Description, in.Visuel, idProjet,
	)
	if err != nil {
		return 0, err
	}
	id, err := res.LastInsertId()
	return int(id), err
}

// ProjetIdDeLEtape résout le projet parent d'une étape pour en vérifier la
// propriété avant suppression. Renvoie sql.ErrNoRows si l'étape n'existe pas.
func (ProjetRepo) ProjetIdDeLEtape(q Querier, idEtape int) (int, error) {
	var idProjet int
	err := q.QueryRow("SELECT Id_Projets FROM Etapes WHERE Id_Etapes = ?", idEtape).Scan(&idProjet)
	return idProjet, err
}

// SupprimerEtape retire une étape. La propriété (via le projet parent) a été
// vérifiée par le service sous verrou avant l'appel.
func (ProjetRepo) SupprimerEtape(q Querier, idEtape int) error {
	_, err := q.Exec("DELETE FROM Etapes WHERE Id_Etapes = ?", idEtape)
	return err
}
