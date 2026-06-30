package repository

import "upcycleconnect/internal/domain"

type AnnonceRepo struct{}

// ─── Résolution du propriétaire ──────────────────────────────────────────────

func (AnnonceRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

func (AnnonceRepo) IdProfessionnel(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Professionnels FROM Professionnels_artisans WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

// ─── Création ────────────────────────────────────────────────────────────────

type AnnonceCreation struct {
	Titre           string
	Description     string
	Categorie       string
	Etat            string
	Type            string
	Prix            float64
	Ville           string
	CodePostal      string
	IdParticulier   int // 0 si l'annonce appartient à un professionnel
	IdProfessionnel int // 0 si l'annonce appartient à un particulier
}

func (AnnonceRepo) Creer(q Querier, a AnnonceCreation) (int64, error) {
	var idPart, idPro interface{}
	if a.IdParticulier != 0 {
		idPart = a.IdParticulier
	}
	if a.IdProfessionnel != 0 {
		idPro = a.IdProfessionnel
	}
	res, err := q.Exec(
		`INSERT INTO Annonces
		   (Titre, Description, Categorie, Etat, Type_annonce, Prix, Ville, Code_postal,
		    Statut, Date_publication, Id_Particuliers, Id_Professionnels)
		 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?, ?)`,
		a.Titre, a.Description, a.Categorie, a.Etat, a.Type, a.Prix,
		a.Ville, a.CodePostal, idPart, idPro,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

// ─── Fiche détail ─────────────────────────────────────────────────────────────
// La colonne Proprietaire contient Id_Particuliers OU Id_Professionnels selon
// qui est le propriétaire. On unifie via un CASE pour que le service puisse
// comparer avec la valeur renvoyée par resoudreProprietaire.

type AnnonceFiche struct {
	ID              int
	Titre           string
	Description     string
	Categorie       string
	Etat            string
	Type            string
	Prix            float64
	Ville           string
	CodePostal      string
	Statut          string
	Date            string
	Auteur          string
	Email           string
	Proprietaire    int  // Id_Particuliers ou Id_Professionnels selon EstPro
	EstPro          bool // true si appartient à un professionnel
}

func (AnnonceRepo) Fiche(q Querier, idAnnonce int) (AnnonceFiche, error) {
	var f AnnonceFiche
	err := q.QueryRow(
		`SELECT a.Id_Annonces,
		        COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Etat,''), COALESCE(a.Type_annonce,''), COALESCE(a.Prix,0),
		        COALESCE(a.Ville,''), COALESCE(a.Code_postal,''), COALESCE(a.Statut,''),
		        COALESCE(a.Date_publication,''),
		        CASE WHEN a.Id_Particuliers IS NOT NULL THEN a.Id_Particuliers
		             ELSE a.Id_Professionnels END,
		        CASE WHEN a.Id_Particuliers IS NOT NULL THEN 0 ELSE 1 END,
		        TRIM(CONCAT(COALESCE(u.Nom,''),' ',COALESCE(u.Prenom,''))),
		        COALESCE(u.Email,'')
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs)
		 WHERE a.Id_Annonces = ?`,
		idAnnonce,
	).Scan(
		&f.ID, &f.Titre, &f.Description, &f.Categorie, &f.Etat, &f.Type, &f.Prix,
		&f.Ville, &f.CodePostal, &f.Statut, &f.Date,
		&f.Proprietaire, &f.EstPro,
		&f.Auteur, &f.Email,
	)
	return f, err
}

// ─── Lecture pour mise à jour (avec verrou) ───────────────────────────────────

func (AnnonceRepo) PourMAJ(q Querier, idAnnonce int) (domain.AnnonceSnapshot, error) {
	var s domain.AnnonceSnapshot
	var estPro bool
	err := q.QueryRow(
		`SELECT COALESCE(Statut,''), COALESCE(Type_annonce,''), COALESCE(Prix,0),
		        CASE WHEN Id_Particuliers IS NOT NULL THEN Id_Particuliers
		             ELSE Id_Professionnels END,
		        CASE WHEN Id_Particuliers IS NOT NULL THEN 0 ELSE 1 END
		 FROM Annonces WHERE Id_Annonces = ? FOR UPDATE`,
		idAnnonce,
	).Scan(&s.Statut, &s.Type, &s.Prix, &s.Proprietaire, &estPro)
	if err != nil {
		return s, err
	}
	s.EstPro = estPro
	return s, nil
}

// ─── Mise à jour du statut ────────────────────────────────────────────────────

func (AnnonceRepo) MettreStatut(q Querier, idAnnonce int, statut string) error {
	_, err := q.Exec("UPDATE Annonces SET Statut = ? WHERE Id_Annonces = ?", statut, idAnnonce)
	return err
}

// ─── Suppression (admin) ──────────────────────────────────────────────────────

func (AnnonceRepo) Supprimer(q Querier, idAnnonce int) (int64, error) {
	res, err := q.Exec("DELETE FROM Annonces WHERE Id_Annonces = ?", idAnnonce)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

// ─── Liste ────────────────────────────────────────────────────────────────────

type AnnonceListe struct {
	ID          int
	Titre       string
	Description string
	Categorie   string
	Etat        string
	Type        string
	Prix        float64
	Ville       string
	CodePostal  string
	Statut      string
	Date        string
	Auteur      string
}

func (AnnonceRepo) ListerPubliees(q Querier) ([]AnnonceListe, error) {
	rows, err := q.Query(
		`SELECT a.Id_Annonces,
		        COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Etat,''), COALESCE(a.Type_annonce,''), COALESCE(a.Prix,0),
		        COALESCE(a.Ville,''), COALESCE(a.Code_postal,''), COALESCE(a.Statut,''),
		        COALESCE(a.Date_publication,''),
		        TRIM(CONCAT(COALESCE(u.Nom,''),' ',COALESCE(u.Prenom,'')))
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs)
		 WHERE a.Statut = 'validee'
		 ORDER BY a.Date_publication DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []AnnonceListe{}
	for rows.Next() {
		var a AnnonceListe
		if err := rows.Scan(&a.ID, &a.Titre, &a.Description, &a.Categorie, &a.Etat, &a.Type,
			&a.Prix, &a.Ville, &a.CodePostal, &a.Statut, &a.Date, &a.Auteur); err != nil {
			return nil, err
		}
		liste = append(liste, a)
	}
	return liste, rows.Err()
}

// ListerParUtilisateur retourne toutes les annonces d'un utilisateur,
// qu'il soit particulier ou professionnel.
func (AnnonceRepo) ListerParUtilisateur(q Querier, idUtilisateur int) ([]AnnonceListe, error) {
	rows, err := q.Query(
		`SELECT a.Id_Annonces,
		        COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Etat,''), COALESCE(a.Type_annonce,''), COALESCE(a.Prix,0),
		        COALESCE(a.Ville,''), COALESCE(a.Code_postal,''),
		        COALESCE(a.Statut,'en_attente'), COALESCE(a.Date_publication,'')
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 WHERE COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs) = ?
		 ORDER BY a.Id_Annonces DESC`,
		idUtilisateur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	liste := []AnnonceListe{}
	for rows.Next() {
		var a AnnonceListe
		if err := rows.Scan(&a.ID, &a.Titre, &a.Description, &a.Categorie, &a.Etat, &a.Type,
			&a.Prix, &a.Ville, &a.CodePostal, &a.Statut, &a.Date); err != nil {
			return nil, err
		}
		liste = append(liste, a)
	}
	return liste, rows.Err()
}

// ListerAdmin retourne toutes les annonces pour la vue admin (LEFT JOIN pour
// inclure aussi les annonces de professionnels).
func (AnnonceRepo) ListerAdmin(q Querier) ([]map[string]interface{}, error) {
	rows, err := q.Query(
		`SELECT a.Id_Annonces, COALESCE(a.Titre,''), COALESCE(a.Statut,'en_attente'),
		        COALESCE(a.Date_publication,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Description,''), COALESCE(a.Prix,0), COALESCE(a.Ville,''),
		        COALESCE(u.Nom,''), COALESCE(u.Prenom,''), COALESCE(u.Email,'')
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs)
		 ORDER BY a.Id_Annonces DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	annonces := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var prix float64
		var titre, statut, date, categorie, desc, ville, nom, prenom, email string
		if err := rows.Scan(&id, &titre, &statut, &date, &categorie, &desc, &prix, &ville, &nom, &prenom, &email); err != nil {
			return nil, err
		}
		annonces = append(annonces, map[string]interface{}{
			"id": id, "titre": titre, "statut": statut, "date_publication": date,
			"categorie": categorie, "description": desc, "prix": prix, "ville": ville,
			"nom": nom, "prenom": prenom, "email": email,
		})
	}
	return annonces, rows.Err()
}

