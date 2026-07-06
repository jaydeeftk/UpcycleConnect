package repository

type DevisLigne struct {
	ID           int
	IdDemande    int
	IdPro        int
	Prix         float64
	Message      string
	Statut       string
	DateCreation string
	NomPro       string
}

type DemandePrestaLigne struct {
	ID               int
	NomObjet         string
	Categorie        string
	TypeObjet        string
	Etat             string
	Description      string
	Localisation     string
	Budget           string
	Statut           string
	DateCreation     string
	IdUtilisateurs   int
	IdProfessionnels int
	MonDevisID       int
	MonDevisStatut   string
	MonDevisPrix     float64
}

type DevisRepo struct{}

func (DevisRepo) TrouverExistant(q Querier, idDemande, idPro int) (id int, statut string, err error) {
	err = q.QueryRow(
		"SELECT Id_Devis, Statut FROM Devis WHERE Id_Demandes_prestations = ? AND Id_Professionnels = ?",
		idDemande, idPro,
	).Scan(&id, &statut)
	return
}

func (DevisRepo) Creer(q Querier, idDemande, idPro int, prix float64, message string) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Devis (Id_Demandes_prestations, Id_Professionnels, Prix, Message, Statut, Date_creation)
		 VALUES (?, ?, ?, ?, 'propose', NOW())`,
		idDemande, idPro, prix, message,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (DevisRepo) Modifier(q Querier, idDevis int, prix float64, message string) error {
	_, err := q.Exec("UPDATE Devis SET Prix = ?, Message = ? WHERE Id_Devis = ?", prix, message, idDevis)
	return err
}

func (DevisRepo) Retirer(q Querier, idDevis int) error {
	_, err := q.Exec("UPDATE Devis SET Statut = 'retire' WHERE Id_Devis = ?", idDevis)
	return err
}

func (DevisRepo) DevisPourMAJ(q Querier, idDevis int) (idDemande, idPro int, prix float64, statut string, err error) {
	err = q.QueryRow(
		"SELECT Id_Demandes_prestations, Id_Professionnels, Prix, Statut FROM Devis WHERE Id_Devis = ? FOR UPDATE",
		idDevis,
	).Scan(&idDemande, &idPro, &prix, &statut)
	return
}

func (DevisRepo) MajStatut(q Querier, idDevis int, statut string) error {
	_, err := q.Exec("UPDATE Devis SET Statut = ? WHERE Id_Devis = ?", statut, idDevis)
	return err
}

func (DevisRepo) MarquerAccepteAvecReference(q Querier, idDevis int, referenceStripe string) error {
	_, err := q.Exec("UPDATE Devis SET Statut = 'accepte', Reference_Stripe = ? WHERE Id_Devis = ?", referenceStripe, idDevis)
	return err
}

func (DevisRepo) RefuserAutres(q Querier, idDemande int, idDevisAccepte int) error {
	_, err := q.Exec(
		"UPDATE Devis SET Statut = 'refuse' WHERE Id_Demandes_prestations = ? AND Id_Devis != ? AND Statut = 'propose'",
		idDemande, idDevisAccepte,
	)
	return err
}

func (DevisRepo) ListerParDemande(q Querier, idDemande int) ([]DevisLigne, error) {
	rows, err := q.Query(
		`SELECT d.Id_Devis, d.Id_Demandes_prestations, d.Id_Professionnels, d.Prix, COALESCE(d.Message,''),
			d.Statut, COALESCE(DATE_FORMAT(d.Date_creation,'%d/%m/%Y %H:%i'),''),
			TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,'')))
		 FROM Devis d
		 JOIN Professionnels_artisans pa ON pa.Id_Professionnels = d.Id_Professionnels
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = pa.Id_Utilisateurs
		 WHERE d.Id_Demandes_prestations = ?
		 ORDER BY d.Prix ASC`,
		idDemande,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []DevisLigne{}
	for rows.Next() {
		var d DevisLigne
		if err := rows.Scan(&d.ID, &d.IdDemande, &d.IdPro, &d.Prix, &d.Message, &d.Statut, &d.DateCreation, &d.NomPro); err != nil {
			return nil, err
		}
		out = append(out, d)
	}
	return out, rows.Err()
}

func (DevisRepo) DemandePourMAJ(q Querier, idDemande int) (statut string, idUtilisateur int, err error) {
	err = q.QueryRow(
		"SELECT Statut, Id_Utilisateurs FROM Demandes_prestations WHERE Id_Demandes_prestations = ? FOR UPDATE",
		idDemande,
	).Scan(&statut, &idUtilisateur)
	return
}

func (DevisRepo) AssignerProDemande(q Querier, idDemande, idPro int) error {
	_, err := q.Exec("UPDATE Demandes_prestations SET Statut = 'en_cours', Id_Professionnels = ? WHERE Id_Demandes_prestations = ?", idPro, idDemande)
	return err
}

func (DevisRepo) AnnulerDemande(q Querier, idDemande int) error {
	_, err := q.Exec("UPDATE Demandes_prestations SET Statut = 'annulee' WHERE Id_Demandes_prestations = ?", idDemande)
	return err
}

func (DevisRepo) MarquerDemandeTraitee(q Querier, idDemande int) error {
	_, err := q.Exec("UPDATE Demandes_prestations SET Statut = 'traitee' WHERE Id_Demandes_prestations = ?", idDemande)
	return err
}

func (DevisRepo) DemandeAppartientA(q Querier, idDemande, idUtilisateur int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Demandes_prestations WHERE Id_Demandes_prestations = ? AND Id_Utilisateurs = ?",
		idDemande, idUtilisateur,
	).Scan(&n)
	return n > 0, err
}

func (DevisRepo) NomEtObjetDemande(q Querier, idDemande int) (nomObjet string, err error) {
	err = q.QueryRow("SELECT Nom_objet FROM Demandes_prestations WHERE Id_Demandes_prestations = ?", idDemande).Scan(&nomObjet)
	return
}

func (DevisRepo) ListerDemandesOuvertes(q Querier, idPro int) ([]DemandePrestaLigne, error) {
	rows, err := q.Query(
		`SELECT dp.Id_Demandes_prestations, dp.Nom_objet, COALESCE(dp.Categorie,''), COALESCE(dp.Type_objet,''),
			COALESCE(dp.Etat,''), COALESCE(dp.Description,''), COALESCE(dp.Localisation,''), COALESCE(dp.Budget,''),
			dp.Statut, COALESCE(DATE_FORMAT(dp.Date_creation,'%d/%m/%Y'),''),
			COALESCE(mondevis.Id_Devis,0), COALESCE(mondevis.Statut,''), COALESCE(mondevis.Prix,0)
		 FROM Demandes_prestations dp
		 LEFT JOIN Devis mondevis ON mondevis.Id_Demandes_prestations = dp.Id_Demandes_prestations AND mondevis.Id_Professionnels = ?
		 WHERE dp.Statut = 'ouverte'
		 ORDER BY dp.Id_Demandes_prestations DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []DemandePrestaLigne{}
	for rows.Next() {
		var d DemandePrestaLigne
		if err := rows.Scan(&d.ID, &d.NomObjet, &d.Categorie, &d.TypeObjet, &d.Etat, &d.Description,
			&d.Localisation, &d.Budget, &d.Statut, &d.DateCreation,
			&d.MonDevisID, &d.MonDevisStatut, &d.MonDevisPrix); err != nil {
			return nil, err
		}
		out = append(out, d)
	}
	return out, rows.Err()
}
