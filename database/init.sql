-- =============================================
-- UpcycleConnect - Script de création BDD MySQL
-- =============================================

-- Force l'encodage de la session de chargement : le fichier est en UTF-8.
-- Sans cela, une session cliente en latin1 double-encode les accents
-- (« Journée » stocké comme « JournÃ©e »), illisible côté API utf8mb4.
SET NAMES utf8mb4;

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
INSERT INTO Langue (Nom) VALUES ('Français'), ('English'), ('Deutsch'), ('Español');

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
   Token_confirmation VARCHAR(64),
   Date_Inscription DATETIME,
   Date_naissance DATETIME,
   Id_Langue INT NOT NULL DEFAULT 1,
   Tutoriel_vu INT DEFAULT 0,
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
   Prix DECIMAL(10,2) DEFAULT 0,
   Id_Salaries INT NULL,
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
   Date_formation DATETIME,
   Places_total INT DEFAULT 20,
   Places_dispo INT DEFAULT 20,
   Localisation VARCHAR(200),
   Categorie VARCHAR(100),
   Id_Salaries INT,
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
   Titre VARCHAR(150),
   Contenu VARCHAR(255),
   Categorie VARCHAR(50),
   Tags VARCHAR(255),
   Statut VARCHAR(50) DEFAULT 'valide',
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
   Contenu TEXT,
   Categorie VARCHAR(100) DEFAULT 'general',
   Statut VARCHAR(100) DEFAULT 'ouvert',
   Vues INT DEFAULT 0,
   PRIMARY KEY(Id_Sujets),
   FOREIGN KEY(Id_Forum) REFERENCES Forum(Id_Forum),
   FOREIGN KEY(Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);

CREATE TABLE IF NOT EXISTS Reponses(
   Id_Reponses INT AUTO_INCREMENT,
   Contenu VARCHAR(255),
   Date_ DATETIME,
   Id_Sujets INT NOT NULL,
   Id_Professionnels INT NULL,
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
   Contenu VARCHAR(500),
   Date_envoi DATETIME,
   Id_Professionnels INT,
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



CREATE TABLE IF NOT EXISTS Demandes_conteneurs (
  Id_Demandes_conteneurs INT AUTO_INCREMENT PRIMARY KEY,
  Type_objet VARCHAR(100),
  Description TEXT,
  Etat_usure VARCHAR(50),
  Id_Conteneurs INT,
  Date_depot DATETIME,
  Destination VARCHAR(50),
  Prix_vente DECIMAL(10,2) DEFAULT 0,
  Photo_url VARCHAR(255),
  Statut VARCHAR(50) DEFAULT 'en_attente',
  Code_acces VARCHAR(20),
  Date_demande DATETIME,
  Id_Particuliers INT NOT NULL,
  FOREIGN KEY (Id_Particuliers) REFERENCES Particuliers(Id_Particuliers)
);


ALTER TABLE Annonces ADD COLUMN Titre VARCHAR(100);
ALTER TABLE Annonces ADD COLUMN Description TEXT;
ALTER TABLE Annonces ADD COLUMN Categorie VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Etat VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Type_annonce VARCHAR(50);
ALTER TABLE Annonces ADD COLUMN Prix DECIMAL(10,2) DEFAULT 0;
ALTER TABLE Annonces ADD COLUMN Ville VARCHAR(100);
ALTER TABLE Annonces ADD COLUMN Code_postal VARCHAR(10);
ALTER TABLE Evenements MODIFY COLUMN Id_Salaries INT NULL;

CREATE TABLE IF NOT EXISTS Visites (
    Id_Visites INT AUTO_INCREMENT,
    Page VARCHAR(255) NOT NULL DEFAULT '/',
    Ip VARCHAR(45),
    User_agent VARCHAR(500),
    Date_visite DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(Id_Visites)
);

ALTER TABLE Messages MODIFY COLUMN Id_Particuliers INT NULL;
ALTER TABLE Messages ADD COLUMN Id_Utilisateurs INT NULL;




INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu)
VALUES ('Dupont', 'Marie', 'salarie@upcycleconnect.fr', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'actif', NOW(), 1, 1);

INSERT INTO Salaries (Poste, Responsable, Date_Debut_Contrat, Id_Utilisateurs)
VALUES ('Animateur', 'Direction', NOW(), LAST_INSERT_ID());


INSERT INTO Evenements (Date_, Titre, Description, Lieu, Capacite, Statut, Prix, Id_Salaries)
VALUES 
(DATE_ADD(CONCAT(CURDATE(), ' 14:00:00'), INTERVAL 12 DAY), 'Atelier Upcycling Textile', 'Apprenez à transformer vos vieux vêtements en créations uniques.', 'Paris 10ème', 20, 'a_venir', 0.00, 1),
(DATE_ADD(CONCAT(CURDATE(), ' 10:00:00'), INTERVAL 21 DAY), 'Workshop Mobilier Recyclé', 'Créez des meubles à partir de matériaux récupérés.', 'Paris 11ème', 15, 'a_venir', 25.00, 1),
(DATE_ADD(CONCAT(CURDATE(), ' 09:30:00'), INTERVAL 33 DAY), 'Journée Zéro Déchet', 'Une journée pour découvrir les pratiques zéro déchet.', 'Paris 13ème', 50, 'a_venir', 0.00, 1);


INSERT INTO Formations (Titre, Description, Prix, Duree, Statut, Date_formation, Places_total, Places_dispo, Localisation, Categorie, Id_Salaries)
VALUES
('Formation Upcycling Débutant', 'Découvrez les bases de l upcycling et créez vos premières créations.', 49.00, 3, 'actif', DATE_ADD(CONCAT(CURDATE(), ' 09:00:00'), INTERVAL 15 DAY), 20, 20, 'Paris 10ème', 'Débutant', 1),
('Formation Couture Créative', 'Apprenez à coudre et transformer vos tissus.', 79.00, 6, 'actif', DATE_ADD(CONCAT(CURDATE(), ' 09:00:00'), INTERVAL 27 DAY), 15, 15, 'Paris 11ème', 'Couture', 1),
('Formation Menuiserie Recyclée', 'Travaillez le bois de récupération pour créer des meubles.', 99.00, 8, 'actif', DATE_ADD(CONCAT(CURDATE(), ' 14:00:00'), INTERVAL 40 DAY), 10, 10, 'Montreuil', 'Menuiserie', 1);


INSERT INTO Services (Titre, Description, Prix, Duree, Categorie, Id_Salaries)
VALUES
('Conseil en Upcycling', 'Consultation personnalisée pour vos projets d upcycling.', 30.00, 1, 'Conseil', 1),
('Atelier Privé', 'Session privée avec un expert en upcycling.', 80.00, 2, 'Atelier', 1),
('Formation sur mesure', 'Formation adaptée à vos besoins spécifiques.', 150.00, 4, 'Formation', 1);

INSERT INTO Forum (Id_Forum) VALUES (1);

-- Administrateur de test
INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu)
VALUES ('Admin', 'System', 'admin@upcycleconnect.fr', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'actif', NOW(), 1, 1);

INSERT INTO Administrateurs (Grade, Id_Utilisateurs) VALUES ('Super Admin', LAST_INSERT_ID());

-- Comptes de demonstration (professionnel + particulier) - mots de passe existants conserves
INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu)
VALUES ('Demo', 'Pro', 'pro@demo.test', '$2a$10$i018bQgbtgYZmIAM3MtvtObm85F6F5zNbVKH48zPmlitCuXTnIMUC', 'actif', NOW(), 1, 1);
INSERT INTO Professionnels_artisans (Nom_Entreprise, Type, Id_Utilisateurs)
VALUES ('Atelier Demo', 'artisan', LAST_INSERT_ID());

INSERT INTO Utilisateurs (Nom, Prenom, Email, Mot_de_passe, Statut, Date_Inscription, Id_Langue, Tutoriel_vu)
VALUES ('Demo', 'Particulier', 'part@demo.test', '$2a$10$lWyuQ0lXGoWJtjmNnYvGSOyBpnKcoc8kMxrYIGFRmz5i8/lJqGlHW', 'actif', NOW(), 1, 1);
INSERT INTO Particuliers (Score, Id_Utilisateurs)
VALUES (0, LAST_INSERT_ID());

-- Conteneurs
INSERT INTO Conteneurs (Localisation, Capacite, Statut, Id_Administrateurs)
VALUES 
('Paris 10ème - 174 rue La Fayette', 20, 'disponible', 1),
('Paris 11ème - Centre communautaire', 15, 'disponible', 1),
('Paris 13ème - Espace upcycling', 25, 'disponible', 1),
('Montreuil - Entrepôt principal', 50, 'disponible', 1);


-- =============================================
-- Migrations rétro-portées (Phase 3 — schéma cible)
-- Un `docker compose up` sur volume NEUF construit déjà le schéma final.
-- Les fichiers database/migrations/00*.sql appliquent les MÊMES changements de
-- schéma, à chaud et de façon idempotente, sur une base EXISTANTE. La
-- normalisation de données et le backfill restent dans les migrations : une
-- base neuve part de données propres.
-- =============================================

-- 001 — Conteneur 1—N Box (occupation dérivée, opposée à Box.Capacite côté service)
CREATE TABLE IF NOT EXISTS Box(
   Id_Box INT AUTO_INCREMENT,
   Reference VARCHAR(50) NOT NULL,
   Capacite INT NOT NULL DEFAULT 1,
   Statut VARCHAR(50) NOT NULL DEFAULT 'disponible',
   Id_Conteneurs INT NOT NULL,
   PRIMARY KEY(Id_Box),
   UNIQUE KEY uq_box_reference (Reference),
   CONSTRAINT fk_box_conteneur FOREIGN KEY(Id_Conteneurs) REFERENCES Conteneurs(Id_Conteneurs),
   CONSTRAINT chk_box_capacite CHECK (Capacite >= 0),
   CONSTRAINT chk_box_statut CHECK (Statut IN ('disponible','pleine','maintenance','hors_service'))
);
ALTER TABLE Objets ADD COLUMN Id_Box INT NULL;
ALTER TABLE Objets ADD CONSTRAINT fk_objets_box FOREIGN KEY (Id_Box) REFERENCES Box(Id_Box);
ALTER TABLE Objets ADD COLUMN Id_Demandes_conteneurs INT NULL;
ALTER TABLE Objets ADD CONSTRAINT fk_objets_demande FOREIGN KEY (Id_Demandes_conteneurs) REFERENCES Demandes_conteneurs(Id_Demandes_conteneurs);

-- 005 — une Box par conteneur (occupation dérivée = COUNT Objets 'en_stock' / Box.Capacite)
INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs)
SELECT CONCAT('BOX-C', c.Id_Conteneurs),
       GREATEST(COALESCE(NULLIF(CAST(c.Capacite AS UNSIGNED), 0), 1), 1),
       'disponible',
       c.Id_Conteneurs
FROM Conteneurs c
WHERE NOT EXISTS (SELECT 1 FROM Box b WHERE b.Id_Conteneurs = c.Id_Conteneurs);

-- 002 — unicité des codes (dernière ligne de défense anti-collision)
ALTER TABLE Demandes_conteneurs ADD UNIQUE KEY uq_demande_code_acces (Code_acces);
ALTER TABLE Codes_Barres ADD UNIQUE KEY uq_codebarres_code (Code);

-- 003 — vocabulaires de statut bornés (CHECK)
ALTER TABLE Contrats ADD COLUMN Statut VARCHAR(50) NOT NULL DEFAULT 'actif';
ALTER TABLE Annonces            ADD CONSTRAINT chk_annonces_statut   CHECK (Statut IN ('en_attente','validee','refusee','retiree','vendue'));
ALTER TABLE Evenements          ADD CONSTRAINT chk_evenements_statut CHECK (Statut IN ('brouillon','a_venir','en_cours','termine','annule'));
ALTER TABLE Formations          ADD CONSTRAINT chk_formations_statut CHECK (Statut IN ('en_attente','actif','rejete','cloturee'));
ALTER TABLE Contrats            ADD CONSTRAINT chk_contrats_statut   CHECK (Statut IN ('brouillon','actif','suspendu','resilie','expire'));
ALTER TABLE Demandes_conteneurs ADD CONSTRAINT chk_demandes_statut   CHECK (Statut IN ('en_attente','validee','refusee','deposee'));
ALTER TABLE Objets              ADD CONSTRAINT chk_objets_statut      CHECK (Statut IS NULL OR Statut IN ('en_stock','reserve_pro','recupere'));

-- 004 — identité forum unifiée (auteur = Utilisateur ; réconcilie la dérive de schéma)
ALTER TABLE Sujets   ADD COLUMN Id_Utilisateurs INT NULL;
ALTER TABLE Reponses ADD COLUMN Id_Utilisateurs INT NULL;
ALTER TABLE Reponses ADD COLUMN Est_Solution TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE Sujets   MODIFY COLUMN Id_Particuliers INT NULL;
ALTER TABLE Sujets   ADD CONSTRAINT fk_sujets_utilisateur   FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs);
ALTER TABLE Reponses ADD CONSTRAINT fk_reponses_utilisateur FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs);

-- 006 — vocabulaires de statut/méthode du vertical facturation (CHECK)
-- Dernière ligne de défense, miroir des constantes domaine (domain/facturation.go).
ALTER TABLE Factures    ADD CONSTRAINT chk_factures_statut    CHECK (Statut IN ('brouillon','emise','payee','annulee'));
ALTER TABLE Paiements   ADD CONSTRAINT chk_paiements_statut   CHECK (Statut IS NULL OR Statut IN ('en_attente','paye','echoue','rembourse'));
ALTER TABLE Paiements   ADD CONSTRAINT chk_paiements_methode  CHECK (Methode IS NULL OR Methode IN ('carte','virement','especes','cheque'));
ALTER TABLE Abonnement  ADD CONSTRAINT chk_abonnement_statut  CHECK (Statut IS NULL OR Statut IN ('actif','suspendu','resilie','expire'));

-- 007 — vocabulaire de statut du vertical forum (CHECK)
-- Miroir des constantes domaine (domain/forum.go) : un sujet est ouvert, resolu
-- ou ferme. Statut nullable -> NULL toléré (MySQL : CHECK passe sur UNKNOWN).
ALTER TABLE Sujets ADD CONSTRAINT chk_sujets_statut CHECK (Statut IS NULL OR Statut IN ('ouvert','resolu','ferme'));

-- 008 — vocabulaire de statut du vertical code-barres (CHECK)
-- Miroir des constantes domaine (domain/codebarres.go) : un code-barres est
-- 'active' (l'objet est récupérable) ou 'utilise' (consommé à la récupération,
-- terminal). Statut nullable -> NULL toléré (MySQL : CHECK passe sur UNKNOWN).
ALTER TABLE Codes_Barres ADD CONSTRAINT chk_codes_barres_statut CHECK (Statut IS NULL OR Statut IN ('active','utilise'));

-- 009 — vocabulaire de statut du vertical projet upcycling (CHECK)
-- Miroir des constantes domaine (domain/projet.go) : un projet est 'en_cours',
-- 'pause' ou 'termine'. Avant ce vertical, Statut était un VARCHAR libre. Statut
-- nullable -> NULL toléré (MySQL : CHECK passe sur UNKNOWN).
ALTER TABLE Projets ADD CONSTRAINT chk_projets_statut CHECK (Statut IS NULL OR Statut IN ('en_cours','pause','termine'));

-- ============================================================
-- Jeu de demonstration metier (soutenance) - donnees coherentes
-- Particulier = Id_Particuliers 1 (part@demo.test) ; Pro = Id_Professionnels 1 (pro@demo.test)
-- Conteneurs + Box semes plus haut (Id 1..3).
-- ============================================================

-- Annonces du particulier (dont une 'en_attente' pour la demo de validation admin)
INSERT INTO Annonces (Date_publication, Contenu, Statut, Id_Particuliers, Titre, Description, Categorie, Etat, Type_annonce, Prix, Ville, Code_postal) VALUES
(NOW(), 'Don',   'validee',    1, 'Vieille chaise en bois', 'Chaise a retaper, structure solide.',      'Mobilier', 'usage', 'don',   0.00,  'Paris', '75011'),
(NOW(), 'Vente', 'validee',    1, 'Lampe vintage',          'Lampe des annees 70, fonctionnelle.',      'Deco',     'bon',   'vente', 15.00, 'Paris', '75010'),
(NOW(), 'Don',   'en_attente', 1, 'Lot de bocaux en verre', 'Bocaux propres, ideals pour upcycling.',   'Divers',   'bon',   'don',   0.00,  'Paris', '75013');

-- Objets recuperes par le pro (alimentent le bilan d'impact ecologique)
INSERT INTO Objets (Type, Poids, Statut, Id_Conteneurs, Id_Professionnels, Id_Particuliers, Id_Box) VALUES
('Velo',    '12 kg', 'recupere', 1, 1, 1, 1),
('Etagere', '8 kg',  'recupere', 1, 1, 1, 1);

-- Depot particulier valide -> objet en stock -> code-barres (demo du QR cote particulier)
INSERT INTO Demandes_conteneurs (Type_objet, Description, Etat_usure, Id_Conteneurs, Destination, Prix_vente, Statut, Code_acces, Date_demande, Id_Particuliers)
VALUES ('Petit meuble', 'Table de chevet en bois', 'bon', 1, 'reemploi', 0.00, 'validee', 'UC-DEMO0001', NOW(), 1);
SET @dem := LAST_INSERT_ID();
INSERT INTO Objets (Type, Poids, Statut, Id_Conteneurs, Id_Particuliers, Id_Box, Id_Demandes_conteneurs)
VALUES ('Table de chevet', '6 kg', 'en_stock', 1, 1, 1, @dem);
SET @obj := LAST_INSERT_ID();
INSERT INTO Codes_Barres (Code, Date_generation, Statut, Id_Objets)
VALUES ('UCB-DEMO0001', NOW(), 'active', @obj);

-- Projets d'upcycling du pro (dashboard + impact)
INSERT INTO Projets (Titre, Description, Date_Debut, Statut, Id_Professionnels) VALUES
('Table basse',     'Table basse en bois de recuperation.',            NOW(), 'termine',  1),
('Lampe bouteille', 'Lampe d ambiance a partir de bouteilles en verre.', NOW(), 'pause',   1),
('Chaise palette',  'Upcycling d une palette en chaise de jardin.',     NOW(), 'en_cours', 1);

-- Contrats du pro (demo de resiliation cote pro)
INSERT INTO Contrats (Date_signature, Date_debut, Date_fin, Type, Id_Professionnels, Statut) VALUES
(NOW(),                 '2026-01-05', '2026-12-31', 'Premium',  1, 'actif'),
('2025-02-01 09:00:00', '2025-02-01', '2026-01-31', 'Standard', 1, 'expire');

-- Notifications (pro = Utilisateur 3, particulier = Utilisateur 4)
INSERT INTO Notifications (Contenu, Date_Envoi, Statut, Id_Administrateurs, Id_Utilisateurs) VALUES
('Une nouvelle annonce correspond a votre activite : palette en bois a recuperer.',   NOW(), 0, 1, 3),
('Votre contrat Premium a bien ete enregistre.',                                      NOW(), 1, 1, 3),
('Votre demande de depot a ete validee : code d acces disponible dans Mes demandes.', NOW(), 0, 1, 4),
('Bienvenue sur UpcycleConnect ! Completez votre profil pour gagner des points.',     NOW(), 1, 1, 4);

-- ============================================================
-- Jeu de demonstration : conseils + forum (soutenance)
-- Salarie = Id_Salaries 1 ; auteurs forum = Utilisateurs (pro=3, particulier=4)
-- ============================================================
INSERT INTO Conseils (Date_d_ajout, Titre, Contenu, Categorie, Tags, Statut, Id_Salaries) VALUES
(NOW(), 'Bien trier ses dechets electroniques', 'Demontez les appareils, separez batteries et cartes, puis deposez-les en point de collecte agree.', 'recyclage', 'electronique,tri', 'valide', 1),
(NOW(), 'Transformer une palette en table basse', 'Poncez, vissez deux palettes et ajoutez des roulettes pour une table basse robuste et deco.', 'upcycling', 'bois,palette,deco', 'valide', 1),
(NOW(), 'Entretenir le cuir naturellement', 'Nettoyez avec un chiffon humide puis nourrissez le cuir avec un peu d''huile de lin.', 'entretien', 'cuir,entretien', 'valide', 1),
(NOW(), 'Reparer plutot que jeter', 'Avant de remplacer un objet, verifiez s''il peut etre repare : c''est souvent simple et economique.', 'bricolage', 'reparation,economie', 'valide', 1);

INSERT INTO Sujets (Titre, Date_Creation, Id_Forum, Contenu, Categorie, Statut, Vues, Id_Utilisateurs) VALUES
('Quelle peinture pour repeindre un meuble en bois ?', NOW(), 1, 'Je veux relooker une vieille commode, quelle peinture tient le mieux ?', 'general', 'ouvert', 12, 4),
('Astuce pour poncer sans poussiere', NOW(), 1, 'Des conseils pour limiter la poussiere quand on ponce a la maison ?', 'general', 'ouvert', 7, 3),
('Ou trouver des palettes gratuites ?', NOW(), 1, 'Je cherche des palettes en bon etat pres de Paris, des bons plans ?', 'general', 'resolu', 23, 4);
SET @sujet1 := LAST_INSERT_ID();

INSERT INTO Reponses (Contenu, Date_, Id_Sujets, Id_Utilisateurs, Est_Solution) VALUES
('Une peinture acrylique multi-supports avec sous-couche tient tres bien sur le bois.', NOW(), @sujet1, 3, 1),
('Pense a depoussierer et degraisser avant, sinon ca n''accroche pas.', NOW(), @sujet1, 4, 0);

-- Demandes de prestation : mise en relation particulier <-> prestataire (mission 1)
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

INSERT INTO Demandes_prestations (Nom_objet, Categorie, Type_objet, Etat, Description, Localisation, Budget, Statut, Date_creation, Id_Utilisateurs)
SELECT 'Velo de ville', 'Reparation', 'Velo', 'Endommage', 'Freins a regler et chambre a air a changer.', 'Paris 11e', '40 EUR', 'ouverte', NOW(), Id_Utilisateurs
FROM Utilisateurs WHERE Email = 'part@demo.test' LIMIT 1;

INSERT INTO Demandes_prestations (Nom_objet, Categorie, Type_objet, Etat, Description, Localisation, Budget, Statut, Date_creation, Id_Utilisateurs)
SELECT 'Commode ancienne', 'Transformation', 'Mobilier', 'A transformer', 'Transformer en meuble TV, finition bois clair.', 'Lyon', 'a discuter', 'en_cours', NOW(), Id_Utilisateurs
FROM Utilisateurs WHERE Email = 'part@demo.test' LIMIT 1;
