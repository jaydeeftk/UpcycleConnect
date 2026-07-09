CREATE TABLE IF NOT EXISTS Categories (
  Id_Categories INT NOT NULL AUTO_INCREMENT,
  Nom VARCHAR(100) NOT NULL,
  Description VARCHAR(255) NULL,
  PRIMARY KEY (Id_Categories)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS Parametres (
  Cle VARCHAR(100) NOT NULL,
  Valeur TEXT NULL,
  PRIMARY KEY (Cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO Categories (Nom, Description) VALUES
 ('Mobilier', 'Meubles et rangements'),
 ('Textile', 'Vêtements et tissus'),
 ('Électroménager', 'Petit et gros électroménager'),
 ('Décoration', 'Objets de décoration'),
 ('Vélo', 'Cycles et pièces détachées'),
 ('Jouets', 'Jeux et jouets'),
 ('Livres', 'Livres et papeterie'),
 ('Vaisselle', 'Arts de la table');

INSERT IGNORE INTO Parametres (Cle, Valeur) VALUES
 ('nom_site', 'UpcycleConnect'),
 ('email_contact', 'contact@upcycleconnect.tech'),
 ('commission_taux', '10'),
 ('maintenance', '0');
