package repository

import "upcycleconnect/internal/domain"

// AnnonceRepo : accès SQL pour les annonces. Sans état — chaque méthode reçoit le
// Querier (DB ou Tx) sur lequel exécuter, ce qui permet aux écritures de partager
// la transaction (et le verrou) ouverte par le service.
type AnnonceRepo struct{}

// IdParticulier résout l'Id_Particuliers de l'utilisateur AUTHENTIFIÉ. Renvoie
// sql.ErrNoRows si le compte n'est pas un particulier (admin/salarié/pro) : la
// couche service en fait un 403 métier.
func (AnnonceRepo) IdParticulier(q Querier, idUtilisateur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?",
		idUtilisateur,
	).Scan(&id)
	return id, err
}

// AnnonceCreation : données d'un dépôt, DÉJÀ validées par le domaine. Le statut
// initial (en_attente) et la date sont imposés par le repo, jamais par le client.
type AnnonceCreation struct {
	Titre         string
	Description   string
	Categorie     string
	Etat          string
	Type          string
	Prix          float64
	Ville         string
	CodePostal    string
	IdParticulier int
}

func (AnnonceRepo) Creer(q Querier, a AnnonceCreation) (int64, error) {
	res, err := q.Exec(
		`INSERT INTO Annonces
		   (Titre, Description, Categorie, Etat, Type_annonce, Prix, Ville, Code_postal, Statut, Date_publication, Id_Particuliers)
		 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), ?)`,
		a.Titre, a.Description, a.Categorie, a.Etat, a.Type, a.Prix, a.Ville, a.CodePostal, a.IdParticulier,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

// AnnonceFiche : vue de lecture pour AFFICHAGE. Email est rapatrié ici mais
// n'est JAMAIS exposé tel quel : le service décide de le révéler (visiteur
// authentifié) ou de le taire (anonyme). Proprietaire sert le contrôle de visibilité.
type AnnonceFiche struct {
	ID           int
	Titre        string
	Description  string
	Categorie    string
	Etat         string
	Type         string
	Prix         float64
	Ville        string
	CodePostal   string
	Statut       string
	Date         string
	Auteur       string
	Email        string
	Proprietaire int
}

func (AnnonceRepo) Fiche(q Querier, idAnnonce int) (AnnonceFiche, error) {
	var f AnnonceFiche
	err := q.QueryRow(
		`SELECT a.Id_Annonces, COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Etat,''), COALESCE(a.Type_annonce,''), COALESCE(a.Prix,0),
		        COALESCE(a.Ville,''), COALESCE(a.Code_postal,''), COALESCE(a.Statut,''),
		        COALESCE(a.Date_publication,''), a.Id_Particuliers,
		        TRIM(CONCAT(COALESCE(u.Nom,''),' ',COALESCE(u.Prenom,''))), COALESCE(u.Email,'')
		 FROM Annonces a
		 JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
		 WHERE a.Id_Annonces = ?`,
		idAnnonce,
	).Scan(&f.ID, &f.Titre, &f.Description, &f.Categorie, &f.Etat, &f.Type, &f.Prix,
		&f.Ville, &f.CodePostal, &f.Statut, &f.Date, &f.Proprietaire, &f.Auteur, &f.Email)
	return f, err
}

// PourMAJ verrouille la ligne (FOR UPDATE) et renvoie le snapshot métier minimal
// (statut + type + prix + propriétaire) nécessaire à la décision de transition.
func (AnnonceRepo) PourMAJ(q Querier, idAnnonce int) (domain.AnnonceSnapshot, error) {
	var s domain.AnnonceSnapshot
	err := q.QueryRow(
		`SELECT COALESCE(Statut,''), COALESCE(Type_annonce,''), COALESCE(Prix,0), Id_Particuliers
		 FROM Annonces WHERE Id_Annonces = ? FOR UPDATE`,
		idAnnonce,
	).Scan(&s.Statut, &s.Type, &s.Prix, &s.Proprietaire)
	return s, err
}

// MettreStatut applique une transition d'état déjà décidée par le domaine.
func (AnnonceRepo) MettreStatut(q Querier, idAnnonce int, statut string) error {
	_, err := q.Exec("UPDATE Annonces SET Statut = ? WHERE Id_Annonces = ?", statut, idAnnonce)
	return err
}

// Supprimer : suppression dure réservée à l'admin. Renvoie le nombre de lignes
// pour distinguer « supprimée » de « introuvable » (404).
func (AnnonceRepo) Supprimer(q Querier, idAnnonce int) (int64, error) {
	res, err := q.Exec("DELETE FROM Annonces WHERE Id_Annonces = ?", idAnnonce)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

// AnnonceListe : projection de liste (sans PII). Auteur n'est renseigné que pour
// la place de marché publique ; il reste vide pour la liste privée du propriétaire.
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

// ListerPubliees : place de marché publique — uniquement les annonces publiées,
// les plus récentes d'abord. Aucune PII (pas d'email).
func (AnnonceRepo) ListerPubliees(q Querier) ([]AnnonceListe, error) {
	rows, err := q.Query(
		`SELECT a.Id_Annonces, COALESCE(a.Titre,''), COALESCE(a.Description,''), COALESCE(a.Categorie,''),
		        COALESCE(a.Etat,''), COALESCE(a.Type_annonce,''), COALESCE(a.Prix,0),
		        COALESCE(a.Ville,''), COALESCE(a.Code_postal,''), COALESCE(a.Statut,''),
		        COALESCE(a.Date_publication,''),
		        TRIM(CONCAT(COALESCE(u.Nom,''),' ',COALESCE(u.Prenom,'')))
		 FROM Annonces a
		 JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 JOIN Utilisateurs u ON u.Id_Utilisateurs = p.Id_Utilisateurs
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

// ListerParProprietaire : toutes les annonces du propriétaire, tous statuts
// confondus (il a le droit de voir ses brouillons / refus). Pas d'auteur (c'est lui).
func (AnnonceRepo) ListerParProprietaire(q Querier, idParticulier int) ([]AnnonceListe, error) {
	rows, err := q.Query(
		`SELECT Id_Annonces, COALESCE(Titre,''), COALESCE(Description,''), COALESCE(Categorie,''),
		        COALESCE(Etat,''), COALESCE(Type_annonce,''), COALESCE(Prix,0),
		        COALESCE(Ville,''), COALESCE(Code_postal,''), COALESCE(Statut,'en_attente'),
		        COALESCE(Date_publication,'')
		 FROM Annonces WHERE Id_Particuliers = ? ORDER BY Id_Annonces DESC`,
		idParticulier,
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
