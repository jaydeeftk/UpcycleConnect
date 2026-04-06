-- =============================================
-- UpcycleConnect - Script de création BDD MySQL
-- =============================================

CREATE TABLE IF NOT EXISTS Forum(
   Id_Forum INT AUTO_INCREMENT,
   PRIMARY KEY(Id_Forum)
);

CREATE TABLE IF NOT EXISTS Badges(
   Id_Badges INT AUTO_INCREMENT,
   Nom VARCHAR(50),
   Date_obtention DATETIME,
   PRIMARY KEY(Id_Badges)
);

CREATE TABLE IF NOT EXISTS Langue(
   Id_Langue INT AUTO_INCREMENT,
   Nom VARCHAR(50),
   PRIMARY KEY(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Abonnement(
   Id_Abonnement VARCHAR(50),
   Type VARCHAR(50),
   Prix DECIMAL(15,2),
   Date_Debut DATE,
   Date_Fin DATE,
   Statut VARCHAR(50),
   PRIMARY KEY(Id_Abonnement)
);

CREATE TABLE IF NOT EXISTS Utilisateurs(
   Id_Utilisateurs INT AUTO_INCREMENT,
   Nom VARCHAR(50),
   Prenom VARCHAR(50),
   Telephone VARCHAR(50),
   Statut VARCHAR(50),
   Adresse VARCHAR(50),
   Mot_de_passe VARCHAR(255),
   Email VARCHAR(100),
   Date_Inscription DATETIME,
   Date_naissance DATETIME,
   Id_Langue INT NOT NULL,
   PRIMARY KEY(Id_Utilisateurs),
   FOREIGN KEY(Id_Langue) REFERENCES Langue(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Score(
   Id_Score INT AUTO_INCREMENT,
   Appreciation VARCHAR(50),
   Valeur DECIMAL(15,2),
   Commentaires VARCHAR(255),
   Ressources_economisees VARCHAR(100),
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Score),
   UNIQUE(Id_Utilisateurs),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Particuliers(
   Id_Particuliers INT AUTO_INCREMENT,
   Score INT,
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Particuliers),
   UNIQUE(Id_Utilisateurs),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Salaries(
   Id_Salaries INT AUTO_INCREMENT,
   Poste VARCHAR(50),
   Responsable VARCHAR(50),
   Date_Debut_Contrat DATETIME,
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Salaries),
   UNIQUE(Id_Utilisateurs),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Administrateurs(
   Id_Administrateurs INT AUTO_INCREMENT,
   Grade VARCHAR(50),
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Administrateurs),
   UNIQUE(Id_Utilisateurs),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Professionnels_artisans(
   Id_Professionnels INT AUTO_INCREMENT,
   Siret BIGINT,
   Nom_Entreprise VARCHAR(100),
   Type VARCHAR(50),
   Id_Abonnement VARCHAR(50),
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Professionnels),
   UNIQUE(Id_Utilisateurs),
   FOREIGN KEY(Id_Abonnement) REFERENCES Abonnement(Id_Abonnement),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Notifications(
   Id_Notifications INT AUTO_INCREMENT,
   Contenu VARCHAR(255),
   Date_Envoi DATETIME,
   Statut TINYINT(1),
   Id_Administrateurs INT NOT NULL,
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Notifications),
   FOREIGN KEY(Id_Administrateurs) REFERENCES Administrateurs(Id_Administrateurs),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Conteneurs(
   Id_Conteneurs INT AUTO_INCREMENT,
   Localisation VARCHAR(100),
   Capacite VARCHAR(50),
   Statut VARCHAR(50),
   Id_Administrateurs INT NOT NULL,
   PRIMARY KEY(Id_Conteneurs),
   FOREIGN KEY(Id_Administrateurs) REFERENCES Administrateurs(Id_Administrateurs)
);

CREATE TABLE IF NOT EXISTS Evenements(
   Id_Evenements INT AUTO_INCREMENT,
   Date_ DATETIME,
   Titre VARCHAR(100),
   Description VARCHAR(255),
   Lieu VARCHAR(100),
   Capacite INT,
   Statut VARCHAR(50),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Evenements),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Formations(
   Id_Formations INT AUTO_INCREMENT,
   Titre VARCHAR(100),
   Description VARCHAR(255),
   Prix DECIMAL(15,2),
   Duree INT,
   Statut VARCHAR(50),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Formations),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Services(
   Id_Services INT AUTO_INCREMENT,
   Titre VARCHAR(100),
   Description VARCHAR(255),
   Prix DECIMAL(15,2),
   Duree INT,
   Categorie VARCHAR(50),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Services),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Annonces(
   Id_Annonces INT AUTO_INCREMENT,
   Date_publication DATETIME,
   Contenu VARCHAR(255),
   Statut VARCHAR(50),
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Annonces),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Projets(
   Id_Projets INT AUTO_INCREMENT,
   Titre VARCHAR(100),
   Description VARCHAR(255),
   Date_Debut DATETIME,
   Statut VARCHAR(50),
   Id_Professionnels INT NOT NULL,
   PRIMARY KEY(Id_Projets),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)
);

CREATE TABLE IF NOT EXISTS Conseils(
   Id_Conseils INT AUTO_INCREMENT,
   Date_d_ajout DATETIME,
   Contenu VARCHAR(255),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Conseils),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Contrats(
   Id_Contrats INT AUTO_INCREMENT,
   Date_signature DATETIME,
   Date_debut DATE,
   Date_fin DATE,
   Type VARCHAR(50),
   Id_Professionnels INT NOT NULL,
   PRIMARY KEY(Id_Contrats),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)
);

CREATE TABLE IF NOT EXISTS Catalogue(
   Id_Catalogue INT AUTO_INCREMENT,
   Description VARCHAR(255),
   Illustration VARCHAR(255),
   Id_Administrateurs INT NOT NULL,
   PRIMARY KEY(Id_Catalogue),
   FOREIGN KEY(Id_Administrateurs) REFERENCES Administrateurs(Id_Administrateurs)
);

CREATE TABLE IF NOT EXISTS Planning(
   Id_Planning INT AUTO_INCREMENT,
   Date_debut DATETIME,
   Date_fin DATETIME,
   Periode VARCHAR(50),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Planning),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Objets(
   Id_Objets INT AUTO_INCREMENT,
   Type VARCHAR(50),
   Poids VARCHAR(50),
   Statut VARCHAR(50),
   Id_Conteneurs INT NOT NULL,
   Id_Professionnels INT NULL,
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Objets),
   FOREIGN KEY(Id_Conteneurs) REFERENCES Conteneurs(Id_Conteneurs),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Sujets(
   Id_Sujets INT AUTO_INCREMENT,
   Titre VARCHAR(100),
   Date_Creation DATETIME,
   Id_Forum INT,
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Sujets),
   FOREIGN KEY(Id_Forum) REFERENCES Forum(Id_Forum),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Reponses(
   Id_Reponses INT AUTO_INCREMENT,
   Contenu VARCHAR(255),
   Date_ DATETIME,
   Id_Sujets INT NOT NULL,
   Id_Professionnels INT NOT NULL,
   PRIMARY KEY(Id_Reponses),
   FOREIGN KEY(Id_Sujets) REFERENCES Sujets(Id_Sujets),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)
);

CREATE TABLE IF NOT EXISTS Medias(
   Id_Medias INT AUTO_INCREMENT,
   Date_Ajout DATETIME,
   URL VARCHAR(255),
   Id_Annonces INT NOT NULL,
   PRIMARY KEY(Id_Medias),
   FOREIGN KEY(Id_Annonces) REFERENCES Annonces(Id_Annonces)
);

CREATE TABLE IF NOT EXISTS Messages(
   Id_Messages INT AUTO_INCREMENT,
   Contenu VARCHAR(255),
   Date_envoi DATETIME,
   Id_Professionnels INT NOT NULL,
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Messages),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Historique(
   Id_Historique INT AUTO_INCREMENT,
   Date_Depot DATETIME,
   Statut_depot VARCHAR(50),
   Observations VARCHAR(255),
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Historique),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Planning_personnel(
   Id_Planning_personnel INT AUTO_INCREMENT,
   Date_debut DATETIME,
   Date_fin DATETIME,
   Id_Particuliers INT NOT NULL,
   PRIMARY KEY(Id_Planning_personnel),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Avis(
   Id_Avis INT AUTO_INCREMENT,
   Date_du_post DATETIME,
   Contenu VARCHAR(255),
   Id_Particuliers INT NOT NULL,
   Id_Professionnels INT NULL,
   Id_Evenements INT NULL,
   Id_Formations INT NULL,
   PRIMARY KEY(Id_Avis),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations),
   CONSTRAINT chk_avis_cible CHECK (
      (Id_Professionnels IS NOT NULL AND Id_Evenements IS NULL     AND Id_Formations IS NULL) OR
      (Id_Professionnels IS NULL     AND Id_Evenements IS NOT NULL AND Id_Formations IS NULL) OR
      (Id_Professionnels IS NULL     AND Id_Evenements IS NULL     AND Id_Formations IS NOT NULL) OR
      (Id_Professionnels IS NULL     AND Id_Evenements IS NULL     AND Id_Formations IS NULL)
   )
);

CREATE TABLE IF NOT EXISTS Publicites(
   Id_Publicites VARCHAR(50),
   Type VARCHAR(50),
   Prix DECIMAL(15,2),
   Date_Debut DATE,
   Date_Fin DATE,
   Statut VARCHAR(50),
   Description VARCHAR(255),
   Illustration VARCHAR(255),
   Id_Professionnels INT NOT NULL,
   PRIMARY KEY(Id_Publicites),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)
);

CREATE TABLE IF NOT EXISTS Etapes(
   Id_Etapes INT AUTO_INCREMENT,
   Nom VARCHAR(100),
   Description VARCHAR(255),
   Visuel VARCHAR(255),
   Id_Projets INT NOT NULL,
   PRIMARY KEY(Id_Etapes),
   FOREIGN KEY(Id_Projets) REFERENCES Projets(Id_Projets)
);

CREATE TABLE IF NOT EXISTS Atelier(
   Id_Atelier INT AUTO_INCREMENT,
   Theme VARCHAR(100),
   Date_creation DATETIME,
   Createur VARCHAR(100),
   Date_atelier DATETIME,
   Lieu VARCHAR(100),
   Statut VARCHAR(50),
   Id_Salaries INT NOT NULL,
   PRIMARY KEY(Id_Atelier),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries)
);

CREATE TABLE IF NOT EXISTS Codes_Barres(
   Id_Codes_Barres INT AUTO_INCREMENT,
   Code VARCHAR(100) NOT NULL,
   Date_generation DATETIME,
   Statut VARCHAR(50),
   Id_Objets INT NOT NULL,
   PRIMARY KEY(Id_Codes_Barres),
   FOREIGN KEY(Id_Objets) REFERENCES Objets(Id_Objets)
);

-- =============================================
-- FACTURATION
-- =============================================

CREATE TABLE IF NOT EXISTS Factures(
   Id_Facture INT AUTO_INCREMENT,
   Numero_facture VARCHAR(50) NOT NULL UNIQUE,
   Date_emission DATETIME NOT NULL,
   Date_echeance DATETIME,
   Montant_HT DECIMAL(15,2) NOT NULL,
   TVA DECIMAL(5,2) DEFAULT 20.00,
   Montant_TTC DECIMAL(15,2) NOT NULL,
   Statut VARCHAR(50) NOT NULL,
   Type VARCHAR(50) NOT NULL,
   PDF_URL VARCHAR(255),
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Facture),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Paiements(
   Id_Paiements INT AUTO_INCREMENT,
   Date_ DATETIME,
   Montant DECIMAL(15,2),
   Statut VARCHAR(50),
   Methode VARCHAR(50),
   Reference_stripe VARCHAR(100),
   Id_Facture INT NOT NULL,
   Id_Utilisateurs INT NOT NULL,
   PRIMARY KEY(Id_Paiements),
   FOREIGN KEY(Id_Facture) REFERENCES Factures(Id_Facture),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

CREATE TABLE IF NOT EXISTS Commissions(
   Id_Commission INT AUTO_INCREMENT,
   Taux DECIMAL(5,2) NOT NULL,
   Montant DECIMAL(15,2) NOT NULL,
   Date_ DATETIME NOT NULL,
   Id_Annonces INT NOT NULL,
   Id_Facture INT NOT NULL,
   PRIMARY KEY(Id_Commission),
   FOREIGN KEY(Id_Annonces) REFERENCES Annonces(Id_Annonces),
   FOREIGN KEY(Id_Facture) REFERENCES Factures(Id_Facture)
);

CREATE TABLE IF NOT EXISTS Lignes_Facture(
   Id_Ligne INT AUTO_INCREMENT,
   Description VARCHAR(255) NOT NULL,
   Quantite INT DEFAULT 1,
   Prix_unitaire_HT DECIMAL(15,2) NOT NULL,
   Total_HT DECIMAL(15,2) NOT NULL,
   Id_Facture INT NOT NULL,
   Id_Formations INT NULL,
   Id_Evenements INT NULL,
   Id_Services INT NULL,
   PRIMARY KEY(Id_Ligne),
   FOREIGN KEY(Id_Facture) REFERENCES Factures(Id_Facture),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements),
   FOREIGN KEY(Id_Services) REFERENCES Services(Id_Services)
);

-- =============================================
-- ASSOCIATIONS
-- =============================================

CREATE TABLE IF NOT EXISTS Gagner(
   Id_Particuliers INT,
   Id_Badges INT,
   PRIMARY KEY(Id_Particuliers, Id_Badges),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Badges) REFERENCES Badges(Id_Badges)
);

CREATE TABLE IF NOT EXISTS Acceder(
   Id_Particuliers INT,
   Id_Conseils INT,
   PRIMARY KEY(Id_Particuliers, Id_Conseils),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Conseils) REFERENCES Conseils(Id_Conseils)
);

CREATE TABLE IF NOT EXISTS Interagir(
   Id_Particuliers INT,
   Id_Catalogue INT,
   PRIMARY KEY(Id_Particuliers, Id_Catalogue),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Catalogue) REFERENCES Catalogue(Id_Catalogue)
);

CREATE TABLE IF NOT EXISTS Consulter(
   Id_Professionnels INT,
   Id_Annonces INT,
   PRIMARY KEY(Id_Professionnels, Id_Annonces),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels),
   FOREIGN KEY(Id_Annonces) REFERENCES Annonces(Id_Annonces)
);

CREATE TABLE IF NOT EXISTS Moderer(
   Id_Salaries INT,
   Id_Forum INT,
   PRIMARY KEY(Id_Salaries, Id_Forum),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries),
   FOREIGN KEY(Id_Forum) REFERENCES Forum(Id_Forum)
);

CREATE TABLE IF NOT EXISTS Participer(
   Id_Particuliers INT,
   Id_Formations INT,
   PRIMARY KEY(Id_Particuliers, Id_Formations),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations)
);

CREATE TABLE IF NOT EXISTS Administrer(
   Id_Administrateurs INT,
   Id_Evenements INT,
   PRIMARY KEY(Id_Administrateurs, Id_Evenements),
   FOREIGN KEY(Id_Administrateurs) REFERENCES Administrateurs(Id_Administrateurs),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements)
);

CREATE TABLE IF NOT EXISTS Appuyer(
   Id_Conseils INT,
   Id_Medias INT,
   PRIMARY KEY(Id_Conseils, Id_Medias),
   FOREIGN KEY(Id_Conseils) REFERENCES Conseils(Id_Conseils),
   FOREIGN KEY(Id_Medias) REFERENCES Medias(Id_Medias)
);

CREATE TABLE IF NOT EXISTS Completer(
   Id_Projets INT,
   Id_Medias INT,
   PRIMARY KEY(Id_Projets, Id_Medias),
   FOREIGN KEY(Id_Projets) REFERENCES Projets(Id_Projets),
   FOREIGN KEY(Id_Medias) REFERENCES Medias(Id_Medias)
);

CREATE TABLE IF NOT EXISTS Traduire_Notifications(
   Id_Notifications INT,
   Id_Langue INT,
   PRIMARY KEY(Id_Notifications, Id_Langue),
   FOREIGN KEY(Id_Notifications) REFERENCES Notifications(Id_Notifications),
   FOREIGN KEY(Id_Langue) REFERENCES Langue(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Traduire_Contrats(
   Id_Contrats INT,
   Id_Langue INT,
   PRIMARY KEY(Id_Contrats, Id_Langue),
   FOREIGN KEY(Id_Contrats) REFERENCES Contrats(Id_Contrats),
   FOREIGN KEY(Id_Langue) REFERENCES Langue(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Traduire_Services(
   Id_Services INT,
   Id_Langue INT,
   PRIMARY KEY(Id_Services, Id_Langue),
   FOREIGN KEY(Id_Services) REFERENCES Services(Id_Services),
   FOREIGN KEY(Id_Langue) REFERENCES Langue(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Traduire_Conseils(
   Id_Conseils INT,
   Id_Langue INT,
   PRIMARY KEY(Id_Conseils, Id_Langue),
   FOREIGN KEY(Id_Conseils) REFERENCES Conseils(Id_Conseils),
   FOREIGN KEY(Id_Langue) REFERENCES Langue(Id_Langue)
);

CREATE TABLE IF NOT EXISTS Favoris(
   Id_Professionnels INT,
   Id_Annonces INT,
   PRIMARY KEY(Id_Professionnels, Id_Annonces),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels),
   FOREIGN KEY(Id_Annonces) REFERENCES Annonces(Id_Annonces)
);

CREATE TABLE IF NOT EXISTS Animer(
   Id_Salaries INT,
   Id_Evenements INT,
   PRIMARY KEY(Id_Salaries, Id_Evenements),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements)
);

CREATE TABLE IF NOT EXISTS Animer_atelier(
   Id_Salaries INT,
   Id_Atelier INT,
   PRIMARY KEY(Id_Salaries, Id_Atelier),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries),
   FOREIGN KEY(Id_Atelier) REFERENCES Atelier(Id_Atelier)
);

CREATE TABLE IF NOT EXISTS Reserver_formation(
   Id_Particuliers INT,
   Id_Formations INT,
   Date_reservation DATETIME,
   PRIMARY KEY(Id_Particuliers, Id_Formations),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations)
);

CREATE TABLE IF NOT EXISTS Animer_formation(
   Id_Salaries INT,
   Id_Formations INT,
   PRIMARY KEY(Id_Salaries, Id_Formations),
   FOREIGN KEY(Id_Salaries) REFERENCES Salaries(Id_Salaries),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations)
);

CREATE TABLE IF NOT EXISTS Contenir_atelier(
   Id_Planning_personnel INT,
   Id_Atelier INT,
   PRIMARY KEY(Id_Planning_personnel, Id_Atelier),
   FOREIGN KEY(Id_Planning_personnel) REFERENCES Planning_personnel(Id_Planning_personnel),
   FOREIGN KEY(Id_Atelier) REFERENCES Atelier(Id_Atelier)
);

CREATE TABLE IF NOT EXISTS Participer_atelier(
   Id_Particuliers INT,
   Id_Atelier INT,
   PRIMARY KEY(Id_Particuliers, Id_Atelier),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Atelier) REFERENCES Atelier(Id_Atelier)
);

CREATE TABLE IF NOT EXISTS Contenir_formations(
   Id_Formations INT,
   Id_Planning_personnel INT,
   PRIMARY KEY(Id_Formations, Id_Planning_personnel),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations),
   FOREIGN KEY(Id_Planning_personnel) REFERENCES Planning_personnel(Id_Planning_personnel)
);

CREATE TABLE IF NOT EXISTS Participer_evenements(
   Id_Particuliers INT,
   Id_Evenements INT,
   PRIMARY KEY(Id_Particuliers, Id_Evenements),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements)
);

CREATE TABLE IF NOT EXISTS Contenir_evenements(
   Id_Evenements INT,
   Id_Planning_personnel INT,
   PRIMARY KEY(Id_Evenements, Id_Planning_personnel),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements),
   FOREIGN KEY(Id_Planning_personnel) REFERENCES Planning_personnel(Id_Planning_personnel)
);

CREATE TABLE IF NOT EXISTS Planifier_evenements(
   Id_Evenements INT,
   Id_Planning INT,
   PRIMARY KEY(Id_Evenements, Id_Planning),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements),
   FOREIGN KEY(Id_Planning) REFERENCES Planning(Id_Planning)
);

CREATE TABLE IF NOT EXISTS Planifier_formation(
   Id_Formations INT,
   Id_Planning INT,
   PRIMARY KEY(Id_Formations, Id_Planning),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations),
   FOREIGN KEY(Id_Planning) REFERENCES Planning(Id_Planning)
);

CREATE TABLE IF NOT EXISTS Historique_objets(
   Id_Objets INT,
   Id_Historique INT,
   PRIMARY KEY(Id_Objets, Id_Historique),
   FOREIGN KEY(Id_Objets) REFERENCES Objets(Id_Objets),
   FOREIGN KEY(Id_Historique) REFERENCES Historique(Id_Historique)
);

CREATE TABLE IF NOT EXISTS Historique_conteneur(
   Id_Conteneurs INT,
   Id_Historique INT,
   PRIMARY KEY(Id_Conteneurs, Id_Historique),
   FOREIGN KEY(Id_Conteneurs) REFERENCES Conteneurs(Id_Conteneurs),
   FOREIGN KEY(Id_Historique) REFERENCES Historique(Id_Historique)
);

CREATE TABLE IF NOT EXISTS Inclure_publicites(
   Id_Contrats INT,
   Id_Publicites VARCHAR(50),
   PRIMARY KEY(Id_Contrats, Id_Publicites),
   FOREIGN KEY(Id_Contrats) REFERENCES Contrats(Id_Contrats),
   FOREIGN KEY(Id_Publicites) REFERENCES Publicites(Id_Publicites)
);

CREATE TABLE IF NOT EXISTS Inclure_abonnements(
   Id_Contrats INT,
   Id_Abonnement VARCHAR(50),
   PRIMARY KEY(Id_Contrats, Id_Abonnement),
   FOREIGN KEY(Id_Contrats) REFERENCES Contrats(Id_Contrats),
   FOREIGN KEY(Id_Abonnement) REFERENCES Abonnement(Id_Abonnement)
);

CREATE TABLE IF NOT EXISTS Catalogue_Formation(
   Id_Catalogue INT,
   Id_Formations INT,
   PRIMARY KEY(Id_Catalogue, Id_Formations),
   FOREIGN KEY(Id_Catalogue) REFERENCES Catalogue(Id_Catalogue),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations)
);

CREATE TABLE IF NOT EXISTS Catalogue_Service(
   Id_Catalogue INT,
   Id_Services INT,
   PRIMARY KEY(Id_Catalogue, Id_Services),
   FOREIGN KEY(Id_Catalogue) REFERENCES Catalogue(Id_Catalogue),
   FOREIGN KEY(Id_Services) REFERENCES Services(Id_Services)
);

CREATE TABLE IF NOT EXISTS Catalogue_Evenement(
   Id_Catalogue INT,
   Id_Evenements INT,
   PRIMARY KEY(Id_Catalogue, Id_Evenements),
   FOREIGN KEY(Id_Catalogue) REFERENCES Catalogue(Id_Catalogue),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements)
);

ALTER TABLE Objets MODIFY COLUMN Id_Professionnels INT NULL;

ALTER TABLE Avis MODIFY COLUMN Id_Professionnels INT NULL;
ALTER TABLE Avis MODIFY COLUMN Id_Evenements INT NULL;

ALTER TABLE Utilisateurs ADD COLUMN IF NOT EXISTS Tutoriel_vu INT DEFAULT 0;
ALTER TABLE Annonces ADD COLUMN IF NOT EXISTS Prix DECIMAL(10,2) DEFAULT 0;

CREATE TABLE IF NOT EXISTS Demandes_conteneurs (
  Id_Demande INT AUTO_INCREMENT PRIMARY KEY,
  Type_objet VARCHAR(100),
  Description TEXT,
  Etat_usure VARCHAR(50),
  Id_Conteneur INT,
  Date_depot DATETIME,
  Destination VARCHAR(50),
  Prix_vente DECIMAL(10,2) DEFAULT 0,
  Statut VARCHAR(50) DEFAULT 'en_attente',
  Date_demande DATETIME,
  Id_Particuliers INT NOT NULL,
  FOREIGN KEY (Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

ALTER TABLE Utilisateurs ADD COLUMN Tutoriel_vu INT DEFAULT 0;
ALTER TABLE Annonces ADD COLUMN Titre VARCHAR(100);
ALTER TABLE Annonces ADD COLUMN Description TEXT;
ALTER TABLE Annonces ADD COLUMN Categorie VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Etat VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Type_annonce VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Prix DECIMAL(10,2) DEFAULT 0;
ALTER TABLE Annonces ADD COLUMN Ville VARCHAR(100);
ALTER TABLE Annonces ADD COLUMN Code_postal VARCHAR(10);

INSERT INTO Langue (Nom) VALUES ('Français'), ('English'), ('Deutsch'), ('Español');