package repository

type ConversationLigne struct {
	ID             int
	IdAnnonce      int
	TitreAnnonce   string
	IdAcheteur     int
	IdVendeur      int
	AutreNom       string
	DernierMessage string
	DateDernierMsg string
	NonLus         int
	DernierEstAuto bool
	DernierTypeEvt string
}

type MessageConversationLigne struct {
	ID             int
	IdExpediteur   int
	Contenu        string
	DateEnvoi      string
	EstAutomatique bool
	TypeEvenement  string
}

type ConversationRepo struct{}

func (ConversationRepo) VendeurDeAnnonce(q Querier, idAnnonce int) (int, error) {
	var idVendeur int
	err := q.QueryRow(
		`SELECT COALESCE(p.Id_Utilisateurs, pa.Id_Utilisateurs)
		 FROM Annonces a
		 LEFT JOIN Particuliers p ON p.Id_Particuliers = a.Id_Particuliers
		 LEFT JOIN Professionnels_artisans pa ON pa.Id_Professionnels = a.Id_Professionnels
		 WHERE a.Id_Annonces = ?`,
		idAnnonce,
	).Scan(&idVendeur)
	return idVendeur, err
}

func (ConversationRepo) TrouverOuCreer(q Querier, idAnnonce, idAcheteur, idVendeur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Conversations FROM Conversations WHERE Id_Annonces = ? AND Id_Acheteur = ?",
		idAnnonce, idAcheteur,
	).Scan(&id)
	if err == nil {
		return id, nil
	}
	res, err := q.Exec(
		"INSERT INTO Conversations (Id_Annonces, Id_Acheteur, Id_Vendeur, Date_creation) VALUES (?,?,?,NOW())",
		idAnnonce, idAcheteur, idVendeur,
	)
	if err != nil {
		return 0, err
	}
	newID, err := res.LastInsertId()
	return int(newID), err
}

func (ConversationRepo) TrouverOuCreerPourPrestation(q Querier, idDemande, idAcheteur, idVendeur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Conversations FROM Conversations WHERE Id_Demandes_prestations = ? AND Id_Acheteur = ?",
		idDemande, idAcheteur,
	).Scan(&id)
	if err == nil {
		return id, nil
	}
	res, err := q.Exec(
		"INSERT INTO Conversations (Id_Demandes_prestations, Id_Acheteur, Id_Vendeur, Date_creation) VALUES (?,?,?,NOW())",
		idDemande, idAcheteur, idVendeur,
	)
	if err != nil {
		return 0, err
	}
	newID, err := res.LastInsertId()
	return int(newID), err
}

func (ConversationRepo) TrouverOuCreerPourCommandeService(q Querier, idCommande, idAcheteur, idVendeur int) (int, error) {
	var id int
	err := q.QueryRow(
		"SELECT Id_Conversations FROM Conversations WHERE Id_Commandes_Services = ? AND Id_Acheteur = ?",
		idCommande, idAcheteur,
	).Scan(&id)
	if err == nil {
		return id, nil
	}
	res, err := q.Exec(
		"INSERT INTO Conversations (Id_Commandes_Services, Id_Acheteur, Id_Vendeur, Date_creation) VALUES (?,?,?,NOW())",
		idCommande, idAcheteur, idVendeur,
	)
	if err != nil {
		return 0, err
	}
	newID, err := res.LastInsertId()
	return int(newID), err
}

func (ConversationRepo) ListerPourUtilisateur(q Querier, idUtilisateur int) ([]ConversationLigne, error) {
	rows, err := q.Query(
		`SELECT c.Id_Conversations, COALESCE(c.Id_Annonces,0), COALESCE(a.Titre, dp.Nom_objet, srv.Titre, ''), c.Id_Acheteur, c.Id_Vendeur,
			TRIM(CONCAT(COALESCE(u.Prenom,''),' ',COALESCE(u.Nom,''))),
			COALESCE(dernier.Contenu,''), COALESCE(DATE_FORMAT(dernier.Date_envoi, '%Y-%m-%d %H:%i:%s'),''),
			COALESCE((SELECT COUNT(*) FROM Messages_Conversation mc
				WHERE mc.Id_Conversations = c.Id_Conversations AND mc.Lu = 0 AND mc.Id_Expediteur != ?), 0),
			COALESCE(dernier.Est_Automatique,0), COALESCE(dernier.Type_Evenement,'')
		FROM Conversations c
		LEFT JOIN Annonces a ON a.Id_Annonces = c.Id_Annonces
		LEFT JOIN Demandes_prestations dp ON dp.Id_Demandes_prestations = c.Id_Demandes_prestations
		LEFT JOIN Commandes_Services cs ON cs.Id_Commandes_Services = c.Id_Commandes_Services
		LEFT JOIN Services srv ON srv.Id_Services = cs.Id_Services
		JOIN Utilisateurs u ON u.Id_Utilisateurs = IF(c.Id_Acheteur = ?, c.Id_Vendeur, c.Id_Acheteur)
		LEFT JOIN Messages_Conversation dernier ON dernier.Id_Messages_Conversation = (
			SELECT mc2.Id_Messages_Conversation FROM Messages_Conversation mc2
			WHERE mc2.Id_Conversations = c.Id_Conversations
			ORDER BY mc2.Id_Messages_Conversation DESC LIMIT 1
		)
		LEFT JOIN Conversations_Masquees cm ON cm.Id_Conversations = c.Id_Conversations AND cm.Id_Utilisateurs = ?
		WHERE (c.Id_Acheteur = ? OR c.Id_Vendeur = ?)
		  AND (cm.Date_masquage IS NULL OR dernier.Date_envoi IS NULL OR dernier.Date_envoi > cm.Date_masquage)
		ORDER BY dernier.Date_envoi DESC, c.Date_creation DESC`,
		idUtilisateur, idUtilisateur, idUtilisateur, idUtilisateur, idUtilisateur,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []ConversationLigne{}
	for rows.Next() {
		var c ConversationLigne
		if err := rows.Scan(&c.ID, &c.IdAnnonce, &c.TitreAnnonce, &c.IdAcheteur, &c.IdVendeur,
			&c.AutreNom, &c.DernierMessage, &c.DateDernierMsg, &c.NonLus,
			&c.DernierEstAuto, &c.DernierTypeEvt); err != nil {
			return nil, err
		}
		out = append(out, c)
	}
	return out, rows.Err()
}

func (ConversationRepo) AppartientAUtilisateur(q Querier, idConversation, idUtilisateur int) (bool, error) {
	var n int
	err := q.QueryRow(
		"SELECT COUNT(*) FROM Conversations WHERE Id_Conversations = ? AND (Id_Acheteur = ? OR Id_Vendeur = ?)",
		idConversation, idUtilisateur, idUtilisateur,
	).Scan(&n)
	return n > 0, err
}

func (ConversationRepo) MasquerPourUtilisateur(q Querier, idConversation, idUtilisateur int) error {
	_, err := q.Exec(
		`INSERT INTO Conversations_Masquees (Id_Conversations, Id_Utilisateurs, Date_masquage)
		 VALUES (?, ?, NOW())
		 ON DUPLICATE KEY UPDATE Date_masquage = NOW()`,
		idConversation, idUtilisateur,
	)
	return err
}

func (ConversationRepo) ListerMessages(q Querier, idConversation int) ([]MessageConversationLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Messages_Conversation, Id_Expediteur, Contenu, COALESCE(DATE_FORMAT(Date_envoi, '%Y-%m-%d %H:%i:%s'),''),
			COALESCE(Est_Automatique,0), COALESCE(Type_Evenement,'')
		 FROM Messages_Conversation WHERE Id_Conversations = ? ORDER BY Id_Messages_Conversation ASC`,
		idConversation,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []MessageConversationLigne{}
	for rows.Next() {
		var m MessageConversationLigne
		if err := rows.Scan(&m.ID, &m.IdExpediteur, &m.Contenu, &m.DateEnvoi, &m.EstAutomatique, &m.TypeEvenement); err != nil {
			return nil, err
		}
		out = append(out, m)
	}
	return out, rows.Err()
}

func (ConversationRepo) CreerMessage(q Querier, idConversation, idExpediteur int, contenu string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Messages_Conversation (Id_Conversations, Id_Expediteur, Contenu, Date_envoi, Lu) VALUES (?,?,?,NOW(),0)",
		idConversation, idExpediteur, contenu,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ConversationRepo) CreerMessageAutomatique(q Querier, idConversation, idExpediteur int, contenu, typeEvenement string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Messages_Conversation (Id_Conversations, Id_Expediteur, Contenu, Date_envoi, Lu, Est_Automatique, Type_Evenement) VALUES (?,?,?,NOW(),0,1,?)",
		idConversation, idExpediteur, contenu, typeEvenement,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (ConversationRepo) Parties(q Querier, idConversation int) (idAcheteur, idVendeur int, nomAcheteur, nomVendeur string, err error) {
	err = q.QueryRow(
		`SELECT c.Id_Acheteur, c.Id_Vendeur,
			TRIM(CONCAT(COALESCE(ua.Prenom,''),' ',COALESCE(ua.Nom,''))),
			TRIM(CONCAT(COALESCE(uv.Prenom,''),' ',COALESCE(uv.Nom,'')))
		 FROM Conversations c
		 JOIN Utilisateurs ua ON ua.Id_Utilisateurs = c.Id_Acheteur
		 JOIN Utilisateurs uv ON uv.Id_Utilisateurs = c.Id_Vendeur
		 WHERE c.Id_Conversations = ?`,
		idConversation,
	).Scan(&idAcheteur, &idVendeur, &nomAcheteur, &nomVendeur)
	return
}

func (ConversationRepo) InfoConversation(q Querier, idConversation int) (idAnnonce int, titreAnnonce string, err error) {
	err = q.QueryRow(
		`SELECT COALESCE(c.Id_Annonces,0), COALESCE(a.Titre,'')
		 FROM Conversations c LEFT JOIN Annonces a ON a.Id_Annonces = c.Id_Annonces
		 WHERE c.Id_Conversations = ?`,
		idConversation,
	).Scan(&idAnnonce, &titreAnnonce)
	return
}

func (ConversationRepo) MarquerLu(q Querier, idConversation, idUtilisateur int) error {
	_, err := q.Exec(
		"UPDATE Messages_Conversation SET Lu = 1 WHERE Id_Conversations = ? AND Id_Expediteur != ?",
		idConversation, idUtilisateur,
	)
	return err
}
