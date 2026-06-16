package repository

import (
	"strings"

	"upcycleconnect/internal/domain"
)

type ProjetRepo struct{}

type ProjetLigne struct {
	ID          int
	Titre       string
	Description string
	Statut      string
	DateDebut   string
	NbEtapes    int
}

type ProjetCreation struct {
	Titre       string
	Description string
	DateDebut   string
	Statut      string
	IdPro       int
}

type EtapeLigne struct {
	ID          int
	Nom         string
	Description string
	Visuel      string
}

type EtapeCreation struct {
	Nom         string
	Description string
	Visuel      string
}

func (ProjetRepo) ListerParPro(q Querier, idPro int) ([]ProjetLigne, error) {
	rows, err := q.Query(
		`SELECT p.Id_Projets, COALESCE(p.Titre,''), COALESCE(p.Description,''),
			COALESCE(p.Statut,'en_cours'), COALESCE(p.Date_Debut,''), COUNT(e.Id_Etapes)
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

func (ProjetRepo) ChargerProjet(q Querier, idProjet int) (domain.ProjetSnapshot, error) {
	var s domain.ProjetSnapshot
	err := q.QueryRow(
		"SELECT Id_Projets, COALESCE(Statut,'en_cours'), Id_Professionnels FROM Projets WHERE Id_Projets = ?",
		idProjet,
	).Scan(&s.ID, &s.Statut, &s.IdProprietairePro)
	return s, err
}

func (ProjetRepo) ProjetPourMAJ(q Querier, idProjet int) (domain.ProjetSnapshot, error) {
	var s domain.ProjetSnapshot
	err := q.QueryRow(
		"SELECT Id_Projets, COALESCE(Statut,'en_cours'), Id_Professionnels FROM Projets WHERE Id_Projets = ? FOR UPDATE",
		idProjet,
	).Scan(&s.ID, &s.Statut, &s.IdProprietairePro)
	return s, err
}

func (ProjetRepo) MettreAJourStatut(q Querier, idProjet int, statut string) error {
	_, err := q.Exec("UPDATE Projets SET Statut = ? WHERE Id_Projets = ?", statut, idProjet)
	return err
}

func (ProjetRepo) MettreAJourContenu(q Querier, idProjet int, titre, description string) error {
	_, err := q.Exec("UPDATE Projets SET Titre = ?, Description = ? WHERE Id_Projets = ?", titre, description, idProjet)
	return err
}

func (ProjetRepo) SupprimerEtapesDuProjet(q Querier, idProjet int) error {
	_, err := q.Exec("DELETE FROM Etapes WHERE Id_Projets = ?", idProjet)
	return err
}

func (ProjetRepo) Supprimer(q Querier, idProjet int) error {
	_, err := q.Exec("DELETE FROM Projets WHERE Id_Projets = ?", idProjet)
	return err
}

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

func (ProjetRepo) ProjetIdDeLEtape(q Querier, idEtape int) (int, error) {
	var idProjet int
	err := q.QueryRow("SELECT Id_Projets FROM Etapes WHERE Id_Etapes = ?", idEtape).Scan(&idProjet)
	return idProjet, err
}

func (ProjetRepo) SupprimerEtape(q Querier, idEtape int) error {
	_, err := q.Exec("DELETE FROM Medias WHERE Id_Etapes = ?", idEtape)
	if err != nil {
		return err
	}
	_, err = q.Exec("DELETE FROM Etapes WHERE Id_Etapes = ?", idEtape)
	return err
}

func (ProjetRepo) AjouterPhotoEtape(q Querier, idEtape int, url, typePhoto string) (int, error) {
	res, err := q.Exec(
		`INSERT INTO Medias (Date_Ajout, URL, Id_Etapes, Type_photo) VALUES (NOW(), ?, ?, ?)`,
		url, idEtape, typePhoto,
	)
	if err != nil {
		return 0, err
	}
	id, err := res.LastInsertId()
	return int(id), err
}

type PhotoLigne struct {
	ID        int
	URL       string
	TypePhoto string
}

func (ProjetRepo) PhotosDesEtapes(q Querier, idProjet int) (map[int][]PhotoLigne, error) {
	rows, err := q.Query(
		`SELECT m.Id_Medias, COALESCE(m.URL,''), COALESCE(m.Type_photo,''), m.Id_Etapes
		 FROM Medias m
		 JOIN Etapes e ON e.Id_Etapes = m.Id_Etapes
		 WHERE e.Id_Projets = ? AND m.Id_Etapes IS NOT NULL
		 ORDER BY m.Id_Medias ASC`, idProjet,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := map[int][]PhotoLigne{}
	for rows.Next() {
		var p PhotoLigne
		var idEt int
		if err := rows.Scan(&p.ID, &p.URL, &p.TypePhoto, &idEt); err != nil {
			return nil, err
		}
		out[idEt] = append(out[idEt], p)
	}
	return out, rows.Err()
}
