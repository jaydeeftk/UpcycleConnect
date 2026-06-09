-- Migration 011 : demandes de prestation (mise en relation particulier <-> prestataire).
-- Idempotente : peut etre rejouee sans risque sur une base existante.

CREATE TABLE IF NOT EXISTS Demandes_prestations (
  Id_Demandes_prestations INT AUTO_INCREMENT PRIMARY KEY,
  Nom_objet VARCHAR(150) NOT NULL,
  Categorie VARCHAR(50),
  Type_objet VARCHAR(50),
  Etat VARCHAR(50),
  Description TEXT,
  Localisation VARCHAR(150),
  Budget VARCHAR(50),
  Statut VARCHAR(30) NOT NULL DEFAULT 'ouverte',
  Date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  Id_Utilisateurs INT NOT NULL,
  Id_Professionnels INT NULL,
  CONSTRAINT chk_dempresta_statut CHECK (Statut IN ('ouverte','en_cours','traitee','annulee')),
  FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs) ON DELETE CASCADE
);
