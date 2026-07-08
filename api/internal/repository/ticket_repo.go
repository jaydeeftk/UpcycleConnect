package repository

type TicketLigne struct {
	ID             int
	IdParticulier  int
	IdAdminAssigne *int
	Statut         string
	DateCreation   string
	NomParticulier string
	NomAdmin       string
	DernierMessage string
	DateDernierMsg string
}

type MessageTicketLigne struct {
	ID           int
	IdExpediteur int
	Contenu      string
	DateEnvoi    string
}

type TicketRepo struct{}

func (TicketRepo) TicketOuvertDuParticulier(q Querier, idParticulier int) (TicketLigne, error) {
	var t TicketLigne
	err := q.QueryRow(
		`SELECT Id_Tickets, Id_Particulier, Id_Admin_Assigne, Statut
		 FROM Tickets WHERE Id_Particulier = ? AND Origine = 'client' AND Statut != 'ferme'
		 ORDER BY Id_Tickets DESC LIMIT 1`,
		idParticulier,
	).Scan(&t.ID, &t.IdParticulier, &t.IdAdminAssigne, &t.Statut)
	return t, err
}

func (TicketRepo) TicketOuvertEntreAdminEtUtilisateur(q Querier, idUtilisateur, idAdmin int) (TicketLigne, error) {
	var t TicketLigne
	err := q.QueryRow(
		`SELECT Id_Tickets, Id_Particulier, Id_Admin_Assigne, Statut
		 FROM Tickets WHERE Id_Particulier = ? AND Id_Admin_Assigne = ? AND Origine = 'admin' AND Statut != 'ferme'
		 ORDER BY Id_Tickets DESC LIMIT 1`,
		idUtilisateur, idAdmin,
	).Scan(&t.ID, &t.IdParticulier, &t.IdAdminAssigne, &t.Statut)
	return t, err
}

func (TicketRepo) CreerParAdmin(q Querier, idUtilisateur, idAdmin int) (int, error) {
	res, err := q.Exec(
		"INSERT INTO Tickets (Id_Particulier, Id_Admin_Assigne, Statut, Origine, Date_creation) VALUES (?, ?, 'en_cours', 'admin', NOW())",
		idUtilisateur, idAdmin,
	)
	if err != nil {
		return 0, err
	}
	newID, err := res.LastInsertId()
	return int(newID), err
}

func (TicketRepo) Creer(q Querier, idParticulier int) (int, error) {
	res, err := q.Exec(
		"INSERT INTO Tickets (Id_Particulier, Statut, Date_creation) VALUES (?, 'en_attente', NOW())",
		idParticulier,
	)
	if err != nil {
		return 0, err
	}
	newID, err := res.LastInsertId()
	return int(newID), err
}

func (TicketRepo) ParID(q Querier, idTicket int) (TicketLigne, error) {
	var t TicketLigne
	err := q.QueryRow(
		"SELECT Id_Tickets, Id_Particulier, Id_Admin_Assigne, Statut FROM Tickets WHERE Id_Tickets = ?",
		idTicket,
	).Scan(&t.ID, &t.IdParticulier, &t.IdAdminAssigne, &t.Statut)
	return t, err
}

func (TicketRepo) ListerTous(q Querier) ([]TicketLigne, error) {
	rows, err := q.Query(
		`SELECT t.Id_Tickets, t.Id_Particulier, t.Id_Admin_Assigne, t.Statut,
			COALESCE(DATE_FORMAT(t.Date_creation, '%Y-%m-%d %H:%i:%s'),''),
			TRIM(CONCAT(COALESCE(up.Prenom,''),' ',COALESCE(up.Nom,''))),
			TRIM(CONCAT(COALESCE(ua.Prenom,''),' ',COALESCE(ua.Nom,''))),
			COALESCE(dernier.Contenu,''), COALESCE(DATE_FORMAT(dernier.Date_envoi, '%Y-%m-%d %H:%i:%s'),'')
		FROM Tickets t
		JOIN Utilisateurs up ON up.Id_Utilisateurs = t.Id_Particulier
		LEFT JOIN Utilisateurs ua ON ua.Id_Utilisateurs = t.Id_Admin_Assigne
		LEFT JOIN Messages_Ticket dernier ON dernier.Id_Messages_Ticket = (
			SELECT mt.Id_Messages_Ticket FROM Messages_Ticket mt
			WHERE mt.Id_Tickets = t.Id_Tickets ORDER BY mt.Id_Messages_Ticket DESC LIMIT 1
		)
		WHERE t.Origine = 'client'
		ORDER BY (t.Statut = 'en_attente') DESC, dernier.Date_envoi DESC, t.Date_creation DESC`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []TicketLigne{}
	for rows.Next() {
		var t TicketLigne
		if err := rows.Scan(&t.ID, &t.IdParticulier, &t.IdAdminAssigne, &t.Statut, &t.DateCreation,
			&t.NomParticulier, &t.NomAdmin, &t.DernierMessage, &t.DateDernierMsg); err != nil {
			return nil, err
		}
		out = append(out, t)
	}
	return out, rows.Err()
}

func (TicketRepo) ListerDuParticulier(q Querier, idParticulier int) ([]TicketLigne, error) {
	rows, err := q.Query(
		`SELECT t.Id_Tickets, t.Id_Particulier, t.Id_Admin_Assigne, t.Statut,
			COALESCE(DATE_FORMAT(t.Date_creation, '%Y-%m-%d %H:%i:%s'),''),
			'', TRIM(CONCAT(COALESCE(ua.Prenom,''),' ',COALESCE(ua.Nom,''))),
			COALESCE(dernier.Contenu,''), COALESCE(DATE_FORMAT(dernier.Date_envoi, '%Y-%m-%d %H:%i:%s'),'')
		FROM Tickets t
		LEFT JOIN Utilisateurs ua ON ua.Id_Utilisateurs = t.Id_Admin_Assigne
		LEFT JOIN Messages_Ticket dernier ON dernier.Id_Messages_Ticket = (
			SELECT mt.Id_Messages_Ticket FROM Messages_Ticket mt
			WHERE mt.Id_Tickets = t.Id_Tickets ORDER BY mt.Id_Messages_Ticket DESC LIMIT 1
		)
		WHERE t.Id_Particulier = ?
		ORDER BY t.Id_Tickets DESC`,
		idParticulier,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []TicketLigne{}
	for rows.Next() {
		var t TicketLigne
		if err := rows.Scan(&t.ID, &t.IdParticulier, &t.IdAdminAssigne, &t.Statut, &t.DateCreation,
			&t.NomParticulier, &t.NomAdmin, &t.DernierMessage, &t.DateDernierMsg); err != nil {
			return nil, err
		}
		out = append(out, t)
	}
	return out, rows.Err()
}

func (TicketRepo) Accepter(q Querier, idTicket, idAdmin int) (int64, error) {
	res, err := q.Exec(
		"UPDATE Tickets SET Id_Admin_Assigne = ?, Statut = 'en_cours' WHERE Id_Tickets = ? AND Statut = 'en_attente'",
		idAdmin, idTicket,
	)
	if err != nil {
		return 0, err
	}
	return res.RowsAffected()
}

func (TicketRepo) Fermer(q Querier, idTicket int) error {
	_, err := q.Exec("UPDATE Tickets SET Statut = 'ferme', Date_cloture = NOW() WHERE Id_Tickets = ?", idTicket)
	return err
}

func (TicketRepo) ListerMessages(q Querier, idTicket int) ([]MessageTicketLigne, error) {
	rows, err := q.Query(
		`SELECT Id_Messages_Ticket, Id_Expediteur, Contenu, COALESCE(DATE_FORMAT(Date_envoi, '%Y-%m-%d %H:%i:%s'),'')
		 FROM Messages_Ticket WHERE Id_Tickets = ? ORDER BY Id_Messages_Ticket ASC`,
		idTicket,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []MessageTicketLigne{}
	for rows.Next() {
		var m MessageTicketLigne
		if err := rows.Scan(&m.ID, &m.IdExpediteur, &m.Contenu, &m.DateEnvoi); err != nil {
			return nil, err
		}
		out = append(out, m)
	}
	return out, rows.Err()
}

func (TicketRepo) CreerMessage(q Querier, idTicket, idExpediteur int, contenu string) (int64, error) {
	res, err := q.Exec(
		"INSERT INTO Messages_Ticket (Id_Tickets, Id_Expediteur, Contenu, Date_envoi) VALUES (?,?,?,NOW())",
		idTicket, idExpediteur, contenu,
	)
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}
