package repository

type ServiceCatalogueLigne struct {
	ID            int
	Titre         string
	Description   string
	Prix          float64
	Duree         int
	Categorie     string
	IdPro         int
	NomAuteur     string
	TypeAuteur    string
	Booste        bool
	AuteurPremium bool
}

type CommandeServiceLigne struct {
	ID               int
	IdService        int
	TitreService     string
	IdUtilisateur    int
	NomObjet         string
	PhotoURL         string
	DescriptionObjet string
	Prix             float64
	Statut           string
	DateCreation     string
	NomClient        string
	IdPro            int
}

type ServiceCatalogueRepo struct{}

func (ServiceCatalogueRepo) ListerCatalogue(q Querier) ([]ServiceCatalogueLigne, error) {
	rows, err := q.Query(
		`SELECT s.Id_Services, COALESCE(s.Titre,''), COALESCE(s.Description,''), COALESCE(s.Prix,0),
			COALESCE(s.Duree,0), COALESCE(s.Categorie,''), COALESCE(s.Id_Professionnels,0),
			TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,''))),
			CASE WHEN s.Id_Professionnels IS NOT NULL THEN 'pro' ELSE 'salarie' END,
			EXISTS(
				SELECT 1 FROM Publicites p
				WHERE p.Id_Services = s.Id_Services AND p.Statut = 'active'
				  AND p.Date_Debut <= CURDATE() AND (p.Date_Fin IS NULL OR p.Date_Fin >= CURDATE())
			),
			EXISTS(
				SELECT 1 FROM Abonnement ab
				WHERE ab.Id_Professionnels = s.Id_Professionnels AND ab.Statut = 'actif'
			)
		 FROM Services s
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = s.Id_Professionnels
		 LEFT JOIN Salaries sa ON sa.Id_Salaries = s.Id_Salaries
		 LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = COALESCE(pa.Id_Utilisateurs, sa.Id_Utilisateurs)
		 ORDER BY EXISTS(
			SELECT 1 FROM Publicites p2
			WHERE p2.Id_Services = s.Id_Services AND p2.Statut = 'active'
			  AND p2.Date_Debut <= CURDATE() AND (p2.Date_Fin IS NULL OR p2.Date_Fin >= CURDATE())
		 ) DESC, s.Id_Services DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ServiceCatalogueLigne{}
	for rows.Next() {
		var l ServiceCatalogueLigne
		if err := rows.Scan(&l.ID, &l.Titre, &l.Description, &l.Prix, &l.Duree, &l.Categorie, &l.IdPro, &l.NomAuteur, &l.TypeAuteur, &l.Booste, &l.AuteurPremium); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (ServiceCatalogueRepo) ListerParPro(q Querier, idPro int) ([]ServiceCatalogueLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Services, COALESCE(Titre,''), COALESCE(Description,''), COALESCE(Prix,0),
			COALESCE(Duree,0), COALESCE(Categorie,''), COALESCE(Id_Professionnels,0), '', 'pro'
		 FROM Services WHERE Id_Professionnels = ? ORDER BY Id_Services DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ServiceCatalogueLigne{}
	for rows.Next() {
		var l ServiceCatalogueLigne
		if err := rows.Scan(&l.ID, &l.Titre, &l.Description, &l.Prix, &l.Duree, &l.Categorie, &l.IdPro, &l.NomAuteur, &l.TypeAuteur); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (ServiceCatalogueRepo) Creer(q Querier, idPro int, titre, description string, prix float64, duree int, categorie string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Services (Titre, Description, Prix, Duree, Categorie, Id_Professionnels) VALUES (?,?,?,?,?,?)",
		titre, description, prix, duree, categorie, idPro,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ServiceCatalogueRepo) Modifier(q Querier, idService int, titre, description string, prix float64, duree int, categorie string) error {
	_, err := q.Exec(
		"UPDATE Services SET Titre=?, Description=?, Prix=?, Duree=?, Categorie=? WHERE Id_Services=?",
		titre, description, prix, duree, categorie, idService,
	)
	return err
}

func (ServiceCatalogueRepo) Supprimer(q Querier, idService int) error {
	_, err := q.Exec("DELETE FROM Services WHERE Id_Services=?", idService)
	return err
}

func (ServiceCatalogueRepo) IdProDuService(q Querier, idService int) (int, error) {
	var id int
	err := q.QueryRow("SELECT COALESCE(Id_Professionnels,0) FROM Services WHERE Id_Services = ?", idService).Scan(&id)
	return id, err
}

func (ServiceCatalogueRepo) ServicePourAchat(q Querier, idService int) (titre string, prix float64, idPro int, err error) {
	err = q.QueryRow(
		"SELECT COALESCE(Titre,''), COALESCE(Prix,0), COALESCE(Id_Professionnels,0) FROM Services WHERE Id_Services = ?",
		idService,
	).Scan(&titre, &prix, &idPro)
	return
}

func (ServiceCatalogueRepo) CreerCommande(q Querier, idService, idUtilisateur int, nomObjet, descriptionObjet, photoURL string, prix float64) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Commandes_Services (Id_Services, Id_Utilisateurs, Nom_Objet, Description_Objet, Photo_Url, Prix, Statut, Date_creation)
		 VALUES (?, ?, ?, ?, ?, ?, 'en_attente_paiement', NOW())`,
		idService, idUtilisateur, nomObjet, descriptionObjet, photoURL, prix,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ServiceCatalogueRepo) CommandePourMAJ(q Querier, idCommande int) (idService, idUtilisateur int, prix float64, statut string, err error) {
	err = q.QueryRow(
		"SELECT Id_Services, Id_Utilisateurs, Prix, Statut FROM Commandes_Services WHERE Id_Commandes_Services = ? FOR UPDATE",
		idCommande,
	).Scan(&idService, &idUtilisateur, &prix, &statut)
	return
}

func (ServiceCatalogueRepo) MarquerCommandePayee(q Querier, idCommande int, referenceStripe string) error {
	_, err := q.Exec(
		"UPDATE Commandes_Services SET Statut = 'payee', Reference_Stripe = ? WHERE Id_Commandes_Services = ?",
		referenceStripe, idCommande,
	)
	return err
}

func (ServiceCatalogueRepo) MarquerCommandeTerminee(q Querier, idCommande int) error {
	_, err := q.Exec("UPDATE Commandes_Services SET Statut = 'terminee' WHERE Id_Commandes_Services = ?", idCommande)
	return err
}

func (ServiceCatalogueRepo) NomObjetCommande(q Querier, idCommande int) (string, error) {
	var nom string
	err := q.QueryRow("SELECT Nom_Objet FROM Commandes_Services WHERE Id_Commandes_Services = ?", idCommande).Scan(&nom)
	return nom, err
}

func (ServiceCatalogueRepo) ListerCommandesParUtilisateur(q Querier, idUtilisateur int) ([]CommandeServiceLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Commandes_Services, c.Id_Services, COALESCE(s.Titre,''), c.Id_Utilisateurs,
			c.Nom_Objet, COALESCE(c.Photo_Url,''), COALESCE(c.Description_Objet,''),
			c.Prix, c.Statut, COALESCE(DATE_FORMAT(c.Date_creation,'%d/%m/%Y %H:%i'),''),
			TRIM(CONCAT(COALESCE(up.Prenom,''),' ',COALESCE(up.Nom,'')))
		 FROM Commandes_Services c
		 JOIN Services s ON s.Id_Services = c.Id_Services
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = s.Id_Professionnels
		 LEFT JOIN Utilisateurs up ON up.Id_Utilisateurs = pa.Id_Utilisateurs
		 WHERE c.Id_Utilisateurs = ? AND c.Statut != 'en_attente_paiement'
		 ORDER BY c.Id_Commandes_Services DESC`,
		idUtilisateur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []CommandeServiceLigne{}
	for rows.Next() {
		var l CommandeServiceLigne
		if err := rows.Scan(&l.ID, &l.IdService, &l.TitreService, &l.IdUtilisateur, &l.NomObjet,
			&l.PhotoURL, &l.DescriptionObjet, &l.Prix, &l.Statut, &l.DateCreation, &l.NomClient); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}

func (ServiceCatalogueRepo) ListerCommandesParPro(q Querier, idPro int) ([]CommandeServiceLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Commandes_Services, c.Id_Services, COALESCE(s.Titre,''), c.Id_Utilisateurs,
			c.Nom_Objet, COALESCE(c.Photo_Url,''), COALESCE(c.Description_Objet,''),
			c.Prix, c.Statut, COALESCE(DATE_FORMAT(c.Date_creation,'%d/%m/%Y %H:%i'),''),
			TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,'')))
		 FROM Commandes_Services c
		 JOIN Services s ON s.Id_Services = c.Id_Services
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = c.Id_Utilisateurs
		 WHERE s.Id_Professionnels = ? AND c.Statut != 'en_attente_paiement'
		 ORDER BY c.Id_Commandes_Services DESC`,
		idPro,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []CommandeServiceLigne{}
	for rows.Next() {
		var l CommandeServiceLigne
		if err := rows.Scan(&l.ID, &l.IdService, &l.TitreService, &l.IdUtilisateur, &l.NomObjet,
			&l.PhotoURL, &l.DescriptionObjet, &l.Prix, &l.Statut, &l.DateCreation, &l.NomClient); err != nil {
			return nil, err
		}
		out = append(out, l)
	}
	return out, rows.Err()
}
