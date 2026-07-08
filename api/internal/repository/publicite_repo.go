package repository

type PubliciteLigne struct {
	ID           string
	Type         string
	Prix         float64
	DateDebut    string
	DateFin      string
	Statut       string
	Description  string
	Illustration string
}

type PubliciteCreation struct {
	ID              string
	Type            string
	Prix            float64
	DateDebut       string
	DateFin         string
	Statut          string
	Description     string
	Illustration    string
	IdPro           int
	ReferenceStripe string
	IdService       int
}

type PubliciteRepo struct{}

func (PubliciteRepo) ListerParPro(q Querier, idPro int) ([]PubliciteLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Publicites, COALESCE(Type,''), COALESCE(Prix,0), COALESCE(Date_Debut,''), COALESCE(Date_Fin,''),
			COALESCE(Statut,''), COALESCE(Description,''), COALESCE(Illustration,'')
		FROM Publicites WHERE Id_Professionnels = ? ORDER BY Date_Debut DESC, Id_Publicites DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []PubliciteLigne{}
	for rows.Next() {
		var p PubliciteLigne
		if err := rows.Scan(&p.ID, &p.Type, &p.Prix, &p.DateDebut, &p.DateFin, &p.Statut, &p.Description, &p.Illustration); err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}

func (PubliciteRepo) Creer(q Querier, p PubliciteCreation) error {
	_, err := q.Exec(
		`INSERT INTO Publicites (Id_Publicites, Type, Prix, Date_Debut, Date_Fin, Statut, Description, Illustration, Id_Professionnels, Reference_Stripe, Id_Services)
		 VALUES (?, ?, ?, NULLIF(?,''), NULLIF(?,''), ?, ?, ?, ?, NULLIF(?,''), NULLIF(?,0))`,
		p.ID, p.Type, p.Prix, p.DateDebut, p.DateFin, p.Statut, p.Description, p.Illustration, p.IdPro, p.ReferenceStripe, p.IdService,
	)
	return err
}

func (PubliciteRepo) AppartientAuPro(q Querier, id string, idPro int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Publicites WHERE Id_Publicites = ? AND Id_Professionnels = ?", id, idPro,
	).Scan(&n)
	return n > 0, err
}

func (PubliciteRepo) MajStatut(q Querier, id, statut string) error {
	_, err := q.Exec("UPDATE Publicites SET Statut = ? WHERE Id_Publicites = ?", statut, id)
	return err
}

func (PubliciteRepo) StatutPourMAJ(q Querier, id string) (string, error) {
	var statut string
	err := q.QueryRow(
		"SELECT COALESCE(Statut,'') FROM Publicites WHERE Id_Publicites = ? FOR UPDATE", id,
	).Scan(&statut)
	return statut, err
}

type PubliciteLigneAdmin struct {
	ID         string
	Type       string
	Prix       float64
	DateDebut  string
	DateFin    string
	Statut     string
	Entreprise string
	Nom        string
	Prenom     string
}

func (PubliciteRepo) ListerTout(q Querier) ([]PubliciteLigneAdmin, error) {
	rows, err := q.Query(
		`SELECT p.Id_Publicites, COALESCE(p.Type,''), COALESCE(p.Prix,0), COALESCE(p.Date_Debut,''), COALESCE(p.Date_Fin,''),
			COALESCE(p.Statut,''), COALESCE(pa.Nom_Entreprise,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Publicites p
		LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = p.Id_Professionnels
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = pa.Id_Utilisateurs
		ORDER BY p.Date_Debut DESC, p.Id_Publicites DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []PubliciteLigneAdmin{}
	for rows.Next() {
		var p PubliciteLigneAdmin
		if err := rows.Scan(&p.ID, &p.Type, &p.Prix, &p.DateDebut, &p.DateFin, &p.Statut, &p.Entreprise, &p.Nom, &p.Prenom); err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}
