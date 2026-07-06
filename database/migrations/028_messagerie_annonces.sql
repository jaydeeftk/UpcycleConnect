CREATE TABLE IF NOT EXISTS Conversations(
   Id_Conversations INT AUTO_INCREMENT,
   Id_Annonces INT NOT NULL,
   Id_Acheteur INT NOT NULL,
   Id_Vendeur INT NOT NULL,
   Date_creation DATETIME,
   PRIMARY KEY(Id_Conversations),
   UNIQUE KEY uniq_conversation_annonce_acheteur (Id_Annonces, Id_Acheteur),
   FOREIGN KEY(Id_Annonces) REFERENCES Annonces(Id_Annonces),
   FOREIGN KEY(Id_Acheteur) REFERENCES Utilisateurs(Id_Utilisateurs),
   FOREIGN KEY(Id_Vendeur) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Messages_Conversation(
   Id_Messages_Conversation INT AUTO_INCREMENT,
   Id_Conversations INT NOT NULL,
   Id_Expediteur INT NOT NULL,
   Contenu VARCHAR(1000) NOT NULL,
   Date_envoi DATETIME,
   Lu TINYINT(1) DEFAULT 0,
   PRIMARY KEY(Id_Messages_Conversation),
   FOREIGN KEY(Id_Conversations) REFERENCES Conversations(Id_Conversations),
   FOREIGN KEY(Id_Expediteur) REFERENCES Utilisateurs(Id_Utilisateurs)
);
