CREATE TABLE IF NOT EXISTS Tickets(
   Id_Tickets INT AUTO_INCREMENT,
   Id_Particulier INT NOT NULL,
   Id_Admin_Assigne INT NULL,
   Statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
   Date_creation DATETIME,
   Date_cloture DATETIME NULL,
   PRIMARY KEY(Id_Tickets),
   FOREIGN KEY(Id_Particulier) REFERENCES Utilisateurs(Id_Utilisateurs),
   FOREIGN KEY(Id_Admin_Assigne) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Messages_Ticket(
   Id_Messages_Ticket INT AUTO_INCREMENT,
   Id_Tickets INT NOT NULL,
   Id_Expediteur INT NOT NULL,
   Contenu VARCHAR(1000) NOT NULL,
   Date_envoi DATETIME,
   PRIMARY KEY(Id_Messages_Ticket),
   FOREIGN KEY(Id_Tickets) REFERENCES Tickets(Id_Tickets),
   FOREIGN KEY(Id_Expediteur) REFERENCES Utilisateurs(Id_Utilisateurs)
);
