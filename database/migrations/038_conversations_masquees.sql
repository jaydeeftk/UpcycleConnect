CREATE TABLE IF NOT EXISTS Conversations_Masquees (
   Id_Conversations INT NOT NULL,
   Id_Utilisateurs INT NOT NULL,
   Date_masquage DATETIME,
   PRIMARY KEY (Id_Conversations, Id_Utilisateurs),
   FOREIGN KEY (Id_Conversations) REFERENCES Conversations(Id_Conversations),
   FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);
