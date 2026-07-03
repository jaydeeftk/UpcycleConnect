package repository

import (
	"database/sql"

	"upcycleconnect/internal/domain"
)

type InscriptionRepo struct{}

func (InscriptionRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

type EvenementFiche struct {
	ID           int
	Titre        string
	Description  string
	Lieu         string
	Statut       string
	Date         sql.NullTime
	Capacite     int
	Participants int
	Prix         float64
}

func (InscriptionRepo) FicheEvenement(q Querier, idEvenement int) (EvenementFiche, error) {
	var f EvenementFiche
	err := q.QueryRow(
		`SELECT e.Id_Evenements, e.Titre, e.Description, e.Lieu, e.Statut, e.Date_,
		        e.Capacite, COALESCE(e.Prix,0),
		        (SELECT COUNT(*) FROM Participer_evenements pe WHERE pe.Id_Evenements = e.Id_Evenements)
		 FROM Evenements e WHERE e.Id_Evenements = ? AND e.Statut_validation = 'valide'`,
		idEvenement,
	).Scan(&f.ID, &f.Titre, &f.Description, &f.Lieu, &f.Statut, &f.Date, &f.Capacite, &f.Prix, &f.Participants)
	return f, err
}

func (InscriptionRepo) EvenementPourMAJ(q Querier, idEvenement int) (domain.EvenementSnapshot, error) {
	var s domain.EvenementSnapshot
	var date sql.NullTime
	err := q.QueryRow(
		"SELECT Statut, Date_, Capacite, COALESCE(Prix,0) FROM Evenements WHERE Id_Evenements = ? FOR UPDATE",
		idEvenement,
	).Scan(&s.Statut, &date, &s.Capacite, &s.Prix)
	if err != nil {
		return s, err
	}
	s.Date = date.Time
	return s, nil
}

func (InscriptionRepo) CompterParticipantsEvenement(q Querier, idEvenement int) (int, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Participer_evenements WHERE Id_Evenements = ?",
		idEvenement,
	).Scan(&n)
	return n, err
}

func (InscriptionRepo) EstInscritEvenement(q Querier, idParticulier, idEvenement int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?",
		idParticulier, idEvenement,
	).Scan(&n)
	return n > 0, err
}

func (InscriptionRepo) InsererParticipationEvenement(q Querier, idParticulier, idEvenement int) error {
	_, err := q.Exec(
		"INSERT INTO Participer_evenements (Id_Particuliers, Id_Evenements) VALUES (?, ?)",
		idParticulier, idEvenement,
	)
	return err
}

func (InscriptionRepo) SupprimerParticipationEvenement(q Querier, idParticulier, idEvenement int) (int64, error) {
	res, err := q.Exec(
		"DELETE FROM Participer_evenements WHERE Id_Particuliers = ? AND Id_Evenements = ?",
		idParticulier, idEvenement,
	)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

type FormationFiche struct {
	ID           int
	Titre        string
	Description  string
	Prix         float64
	Duree        int
	Statut       string
	Date         sql.NullTime
	DateFin      sql.NullTime
	PlacesTotal  int
	PlacesDispo  int
	Localisation string
	Categorie    string
}

func (InscriptionRepo) FicheFormation(q Querier, idFormation int) (FormationFiche, error) {
	var f FormationFiche
	err := q.QueryRow(
		`SELECT Id_Formations, Titre, Description, COALESCE(Prix,0), COALESCE(Duree,0), Statut,
		        Date_formation, Date_fin, COALESCE(Places_total,0), COALESCE(Places_dispo,0),
		        COALESCE(Localisation,''), COALESCE(Categorie,'')
		 FROM Formations WHERE Id_Formations = ? AND Statut_validation = 'valide'`,
		idFormation,
	).Scan(&f.ID, &f.Titre, &f.Description, &f.Prix, &f.Duree, &f.Statut, &f.Date, &f.DateFin,
		&f.PlacesTotal, &f.PlacesDispo, &f.Localisation, &f.Categorie)
	return f, err
}

func (InscriptionRepo) FormationPourMAJ(q Querier, idFormation int) (domain.FormationSnapshot, error) {
	var s domain.FormationSnapshot
	var date sql.NullTime
	err := q.QueryRow(
		`SELECT Statut, Date_formation, COALESCE(Places_dispo,0), COALESCE(Places_total,0), COALESCE(Prix,0)
		 FROM Formations WHERE Id_Formations = ? FOR UPDATE`,
		idFormation,
	).Scan(&s.Statut, &date, &s.PlacesDispo, &s.PlacesTotal, &s.Prix)
	if err != nil {
		return s, err
	}
	s.Date = date.Time
	return s, nil
}

func (InscriptionRepo) EstInscritFormation(q Querier, idParticulier, idFormation int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Reserver_formation WHERE Id_Particuliers = ? AND Id_Formations = ?",
		idParticulier, idFormation,
	).Scan(&n)
	return n > 0, err
}

func (InscriptionRepo) InsererReservationFormation(q Querier, idParticulier, idFormation int) error {
	_, err := q.Exec(
		"INSERT INTO Reserver_formation (Id_Particuliers, Id_Formations, Date_reservation) VALUES (?, ?, NOW())",
		idParticulier, idFormation,
	)
	return err
}

func (InscriptionRepo) SupprimerReservationFormation(q Querier, idParticulier, idFormation int) (int64, error) {
	res, err := q.Exec(
		"DELETE FROM Reserver_formation WHERE Id_Particuliers = ? AND Id_Formations = ?",
		idParticulier, idFormation,
	)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

func (InscriptionRepo) DecrementerPlacesFormation(q Querier, idFormation int) error {
	_, err := q.Exec(
		"UPDATE Formations SET Places_dispo = Places_dispo - 1 WHERE Id_Formations = ?",
		idFormation,
	)
	return err
}

func (InscriptionRepo) IncrementerPlacesFormation(q Querier, idFormation int) error {
	_, err := q.Exec(
		"UPDATE Formations SET Places_dispo = LEAST(Places_dispo + 1, Places_total) WHERE Id_Formations = ?",
		idFormation,
	)
	return err
}
