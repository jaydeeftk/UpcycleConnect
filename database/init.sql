SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `Forum` (
    `Id_Forum` INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE `Badges` (
    `Id_Badges` INT AUTO_INCREMENT PRIMARY KEY,
    `Nom` VARCHAR(100),
    `Date_obtention` DATETIME
);

CREATE TABLE `Langue` (
    `Id_Langue` INT AUTO_INCREMENT PRIMARY KEY,
    `Nom` VARCHAR(50)
);

CREATE TABLE `Abonnement` (
    `Id_Abonnement` VARCHAR(50) PRIMARY KEY,
    `Type` VARCHAR(50),
    `Prix` DECIMAL(15,2),
    `Date_Debut` DATE,
    `Date_Fin` DATE,
    `Statut` VARCHAR(50)
);

CREATE TABLE `Utilisateurs` (
    `Id_Utilisateurs` INT AUTO_INCREMENT PRIMARY KEY,
    `Nom` VARCHAR(100),
    `Prenom` VARCHAR(100),
    `Telephone` VARCHAR(20),
    `Statut` VARCHAR(50),
    `Adresse` VARCHAR(255),
    `Mot_de_passe` VARCHAR(255),
    `Email` VARCHAR(255) UNIQUE,
    `Date_Inscription` DATETIME,
    `Date_naissance` DATETIME,
    `Id_Langue` INT,
    FOREIGN KEY (`Id_Langue`) REFERENCES `Langue`(`Id_Langue`)
);

CREATE TABLE `Score` (
    `Id_Score` INT AUTO_INCREMENT PRIMARY KEY,
    `Appreciation` VARCHAR(50),
    `Valeur` DECIMAL(15,2),
    `Commentaires` TEXT,
    `Ressources_economisees` VARCHAR(100),
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Particuliers` (
    `Id_Particuliers` INT AUTO_INCREMENT PRIMARY KEY,
    `Score` INT,
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Salaries` (
    `Id_Salaries` INT AUTO_INCREMENT PRIMARY KEY,
    `Poste` VARCHAR(100),
    `Responsable` VARCHAR(100),
    `Date_Debut_Contrat` DATETIME,
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Administrateurs` (
    `Id_Administrateurs` INT AUTO_INCREMENT PRIMARY KEY,
    `Grade` VARCHAR(50),
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Professionnels_artisans` (
    `Id_Professionnels` INT AUTO_INCREMENT PRIMARY KEY,
    `Siret` VARCHAR(14),
    `Nom_Entreprise` VARCHAR(100),
    `Type` VARCHAR(50),
    `Id_Abonnement` VARCHAR(50),
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Abonnement`) REFERENCES `Abonnement`(`Id_Abonnement`),
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Notifications` (
    `Id_Notifications` INT AUTO_INCREMENT PRIMARY KEY,
    `Contenu` TEXT,
    `Date_Envoi` DATETIME,
    `Statut` BOOLEAN,
    `Id_Administrateurs` INT,
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs`(`Id_Administrateurs`),
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Conteneurs` (
    `Id_Conteneurs` INT AUTO_INCREMENT PRIMARY KEY,
    `Localisation` VARCHAR(255),
    `Capacite` VARCHAR(50),
    `Statut` VARCHAR(50),
    `Id_Administrateurs` INT,
    FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs`(`Id_Administrateurs`)
);

CREATE TABLE `Paiements` (
    `Id_Paiements` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_Paiement` DATETIME,
    `Montant` DECIMAL(15,2),
    `Statut` BOOLEAN,
    `Id_Administrateurs` INT,
    `Id_Utilisateurs` INT,
    FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs`(`Id_Administrateurs`),
    FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs`(`Id_Utilisateurs`)
);

CREATE TABLE `Evenements` (
    `Id_Evenements` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_Evenement` DATETIME,
    `Titre` VARCHAR(100),
    `Description` TEXT,
    `Lieu` VARCHAR(255),
    `Capacite` INT,
    `Id_Salaries` INT,
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`)
);

CREATE TABLE `Formations` (
    `Id_Formations` INT AUTO_INCREMENT PRIMARY KEY,
    `Titre` VARCHAR(100),
    `Description` TEXT,
    `Prix` DECIMAL(15,3),
    `Duree` INT,
    `Statut` VARCHAR(50),
    `Id_Salaries` INT,
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`)
);

CREATE TABLE `Services` (
    `Id_Services` INT AUTO_INCREMENT PRIMARY KEY,
    `Titre` VARCHAR(100),
    `Description` TEXT,
    `Prix` DECIMAL(15,2),
    `Duree` INT,
    `Categorie` VARCHAR(50),
    `Id_Salaries` INT,
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`)
);

CREATE TABLE `Annonces` (
    `Id_Annonces` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_publication` DATETIME,
    `Contenu` TEXT,
    `Statut` VARCHAR(50),
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Projets` (
    `Id_Projets` INT AUTO_INCREMENT PRIMARY KEY,
    `Titre` VARCHAR(100),
    `Description` TEXT,
    `Date_Debut` DATETIME,
    `Statut` VARCHAR(50),
    `Id_Professionnels` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`)
);

CREATE TABLE `Conseils` (
    `Id_Conseils` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_d_ajout` DATETIME,
    `Contenu` TEXT,
    `Id_Salaries` INT,
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`)
);

CREATE TABLE `Contrats` (
    `Id_Contrats` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_signature` DATETIME,
    `Date_debut` DATETIME,
    `Date_fin` DATETIME,
    `Id_Professionnels` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`)
);

CREATE TABLE `Catalogue` (
    `Id_Catalogue` INT AUTO_INCREMENT PRIMARY KEY,
    `Description` TEXT,
    `Illustration` VARCHAR(255),
    `Id_Administrateurs` INT,
    FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs`(`Id_Administrateurs`)
);

CREATE TABLE `Planning` (
    `Id_Planning` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_debut` DATETIME,
    `Date_fin` DATETIME,
    `Periode` VARCHAR(50),
    `Id_Salaries` INT,
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`)
);

CREATE TABLE `Objets` (
    `Id_Objets` INT AUTO_INCREMENT PRIMARY KEY,
    `Type` VARCHAR(50),
    `Poids` VARCHAR(50),
    `Statut` VARCHAR(50),
    `Id_Conteneurs` INT,
    `Id_Professionnels` INT,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs`(`Id_Conteneurs`),
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Sujets` (
    `Id_Sujets` INT AUTO_INCREMENT PRIMARY KEY,
    `Titre` VARCHAR(100),
    `Date_Creation` DATETIME,
    `Id_Forum` INT,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Forum`) REFERENCES `Forum`(`Id_Forum`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Reponses` (
    `Id_Reponses` INT AUTO_INCREMENT PRIMARY KEY,
    `Contenu` TEXT,
    `Date_Reponse` DATETIME,
    `Id_Sujets` INT,
    `Id_Professionnels` INT,
    FOREIGN KEY (`Id_Sujets`) REFERENCES `Sujets`(`Id_Sujets`),
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`)
);

CREATE TABLE `Medias` (
    `Id_Medias` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_Ajout` DATETIME,
    `URL` VARCHAR(255),
    `Id_Annonces` INT,
    FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces`(`Id_Annonces`)
);

CREATE TABLE `Messages` (
    `Id_Messages` INT AUTO_INCREMENT PRIMARY KEY,
    `Contenu` TEXT,
    `Id_Professionnels` INT,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Historique` (
    `Id_Historique` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_Debut` DATETIME,
    `Statut_depot` VARCHAR(50),
    `Observations` TEXT,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Planning_personnel` (
    `Id_Planning_personnel` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_debut` DATETIME,
    `Date_fin` DATETIME,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Avis` (
    `Id_Avis` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_du_post` DATETIME,
    `Contenu` TEXT,
    `Id_Professionnels` INT,
    `Id_Evenements` INT,
    `Id_Particuliers` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`)
);

CREATE TABLE `Publicites` (
    `Id_Publicites` VARCHAR(50) PRIMARY KEY,
    `Type` VARCHAR(50),
    `Prix` INT,
    `Date_Debut` DATE,
    `Date_Fin` DATE,
    `Statut` VARCHAR(50),
    `Description` TEXT,
    `Illustration` VARCHAR(255),
    `Id_Professionnels` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`)
);

CREATE TABLE `Etapes` (
    `Id_Etapes` INT AUTO_INCREMENT PRIMARY KEY,
    `Nom` VARCHAR(100),
    `Description` TEXT,
    `Visuel` VARCHAR(255),
    `Id_Projets` INT,
    FOREIGN KEY (`Id_Projets`) REFERENCES `Projets`(`Id_Projets`)
);

CREATE TABLE `Atelier` (
    `Id_Atelier` INT AUTO_INCREMENT PRIMARY KEY,
    `Theme` VARCHAR(100),
    `Date_creation` DATETIME,
    `Createur` VARCHAR(100),
    `Date_atelier` DATETIME,
    `Lieu` VARCHAR(255),
    `Id_Professionnels` INT,
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`)
);

CREATE TABLE `Codes_Barres` (
    `Id_Codes_Barres` INT AUTO_INCREMENT PRIMARY KEY,
    `Date_generation` DATETIME,
    `Id_Conteneurs` INT,
    FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs`(`Id_Conteneurs`)
);

-- TABLES D'ASSOCIATIONS
CREATE TABLE `Gagner` (
    `Id_Particuliers` INT,
    `Id_Badges` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Badges`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Badges`) REFERENCES `Badges`(`Id_Badges`)
);

CREATE TABLE `Acceder` (
    `Id_Particuliers` INT,
    `Id_Conseils` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Conseils`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils`(`Id_Conseils`)
);

CREATE TABLE `Interagir` (
    `Id_Particuliers` INT,
    `Id_Catalogue` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Catalogue`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Catalogue`) REFERENCES `Catalogue`(`Id_Catalogue`)
);

CREATE TABLE `Consulter` (
    `Id_Professionnels` INT,
    `Id_Annonces` INT,
    PRIMARY KEY (`Id_Professionnels`, `Id_Annonces`),
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces`(`Id_Annonces`)
);

CREATE TABLE `Moderer` (
    `Id_Salaries` INT,
    `Id_Forum` INT,
    PRIMARY KEY (`Id_Salaries`, `Id_Forum`),
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`),
    FOREIGN KEY (`Id_Forum`) REFERENCES `Forum`(`Id_Forum`)
);

CREATE TABLE `Participer` (
    `Id_Particuliers` INT,
    `Id_Formations` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Formations`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Formations`) REFERENCES `Formations`(`Id_Formations`)
);

CREATE TABLE `Administrer` (
    `Id_Administrateurs` INT,
    `Id_Evenements` INT,
    PRIMARY KEY (`Id_Administrateurs`, `Id_Evenements`),
    FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs`(`Id_Administrateurs`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`)
);

CREATE TABLE `Appuyer` (
    `Id_Conseils` INT,
    `Id_Medias` INT,
    PRIMARY KEY (`Id_Conseils`, `Id_Medias`),
    FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils`(`Id_Conseils`),
    FOREIGN KEY (`Id_Medias`) REFERENCES `Medias`(`Id_Medias`)
);

CREATE TABLE `Completer` (
    `Id_Projets` INT,
    `Id_Medias` INT,
    PRIMARY KEY (`Id_Projets`, `Id_Medias`),
    FOREIGN KEY (`Id_Projets`) REFERENCES `Projets`(`Id_Projets`),
    FOREIGN KEY (`Id_Medias`) REFERENCES `Medias`(`Id_Medias`)
);

CREATE TABLE `Traduire_Notifications` (
    `Id_Notifications` INT,
    `Id_Langue` INT,
    PRIMARY KEY (`Id_Notifications`, `Id_Langue`),
    FOREIGN KEY (`Id_Notifications`) REFERENCES `Notifications`(`Id_Notifications`),
    FOREIGN KEY (`Id_Langue`) REFERENCES `Langue`(`Id_Langue`)
);

CREATE TABLE `Traduire_Contrats` (
    `Id_Contrats` INT,
    `Id_Langue` INT,
    PRIMARY KEY (`Id_Contrats`, `Id_Langue`),
    FOREIGN KEY (`Id_Contrats`) REFERENCES `Contrats`(`Id_Contrats`),
    FOREIGN KEY (`Id_Langue`) REFERENCES `Langue`(`Id_Langue`)
);

CREATE TABLE `Traduire_Services` (
    `Id_Services` INT,
    `Id_Langue` INT,
    PRIMARY KEY (`Id_Services`, `Id_Langue`),
    FOREIGN KEY (`Id_Services`) REFERENCES `Services`(`Id_Services`),
    FOREIGN KEY (`Id_Langue`) REFERENCES `Langue`(`Id_Langue`)
);

CREATE TABLE `Traduire_Conseils` (
    `Id_Conseils` INT,
    `Id_Langue` INT,
    PRIMARY KEY (`Id_Conseils`, `Id_Langue`),
    FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils`(`Id_Conseils`),
    FOREIGN KEY (`Id_Langue`) REFERENCES `Langue`(`Id_Langue`)
);

CREATE TABLE `Favoris` (
    `Id_Professionnels` INT,
    `Id_Annonces` INT,
    PRIMARY KEY (`Id_Professionnels`, `Id_Annonces`),
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces`(`Id_Annonces`)
);

CREATE TABLE `Animer` (
    `Id_Salaries` INT,
    `Id_Evenements` INT,
    PRIMARY KEY (`Id_Salaries`, `Id_Evenements`),
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`)
);

CREATE TABLE `Animer_atelier` (
    `Id_Professionnels` INT,
    `Id_Atelier` INT,
    PRIMARY KEY (`Id_Professionnels`, `Id_Atelier`),
    FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans`(`Id_Professionnels`),
    FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier`(`Id_Atelier`)
);

CREATE TABLE `Reserver_formation` (
    `Id_Particuliers` INT,
    `Id_Formations` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Formations`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Formations`) REFERENCES `Formations`(`Id_Formations`)
);

CREATE TABLE `Animer_formation` (
    `Id_Salaries` INT,
    `Id_Formations` INT,
    PRIMARY KEY (`Id_Salaries`, `Id_Formations`),
    FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries`(`Id_Salaries`),
    FOREIGN KEY (`Id_Formations`) REFERENCES `Formations`(`Id_Formations`)
);

CREATE TABLE `Contenir_atelier` (
    `Id_Planning_personnel` INT,
    `Id_Atelier` INT,
    PRIMARY KEY (`Id_Planning_personnel`, `Id_Atelier`),
    FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel`(`Id_Planning_personnel`),
    FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier`(`Id_Atelier`)
);

CREATE TABLE `Participer_atelier` (
    `Id_Particuliers` INT,
    `Id_Atelier` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Atelier`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier`(`Id_Atelier`)
);

CREATE TABLE `Contenir_formations` (
    `Id_Formations` INT,
    `Id_Planning_personnel` INT,
    PRIMARY KEY (`Id_Formations`, `Id_Planning_personnel`),
    FOREIGN KEY (`Id_Formations`) REFERENCES `Formations`(`Id_Formations`),
    FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel`(`Id_Planning_personnel`)
);

CREATE TABLE `Participer_evenements` (
    `Id_Particuliers` INT,
    `Id_Evenements` INT,
    PRIMARY KEY (`Id_Particuliers`, `Id_Evenements`),
    FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers`(`Id_Particuliers`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`)
);

CREATE TABLE `Contenir_evenements` (
    `Id_Evenements` INT,
    `Id_Planning_personnel` INT,
    PRIMARY KEY (`Id_Evenements`, `Id_Planning_personnel`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`),
    FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel`(`Id_Planning_personnel`)
);

CREATE TABLE `Planifier_evenements` (
    `Id_Evenements` INT,
    `Id_Planning` INT,
    PRIMARY KEY (`Id_Evenements`, `Id_Planning`),
    FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements`(`Id_Evenements`),
    FOREIGN KEY (`Id_Planning`) REFERENCES `Planning`(`Id_Planning`)
);

CREATE TABLE `Planifier_formation` (
    `Id_Formations` INT,
    `Id_Planning` INT,
    PRIMARY KEY (`Id_Formations`, `Id_Planning`),
    FOREIGN KEY (`Id_Formations`) REFERENCES `Formations`(`Id_Formations`),
    FOREIGN KEY (`Id_Planning`) REFERENCES `Planning`(`Id_Planning`)
);

CREATE TABLE `Historique_objets` (
    `Id_Objets` INT,
    `Id_Historique` INT,
    PRIMARY KEY (`Id_Objets`, `Id_Historique`),
    FOREIGN KEY (`Id_Objets`) REFERENCES `Objets`(`Id_Objets`),
    FOREIGN KEY (`Id_Historique`) REFERENCES `Historique`(`Id_Historique`)
);

CREATE TABLE `Historique_conteneur` (
    `Id_Conteneurs` INT,
    `Id_Historique` INT,
    PRIMARY KEY (`Id_Conteneurs`, `Id_Historique`),
    FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs`(`Id_Conteneurs`),
    FOREIGN KEY (`Id_Historique`) REFERENCES `Historique`(`Id_Historique`)
);

CREATE TABLE `Inclure_publicites` (
    `Id_Contrats` INT,
    `Id_Publicites` VARCHAR(50),
    PRIMARY KEY (`Id_Contrats`, `Id_Publicites`),
    FOREIGN KEY (`Id_Contrats`) REFERENCES `Contrats`(`Id_Contrats`),
    FOREIGN KEY (`Id_Publicites`) REFERENCES `Publicites`(`Id_Publicites`)
);

INSERT INTO `Langue` (`Nom`) VALUES ('Français'), ('English'), ('Deutsch'), ('Español');

SET FOREIGN_KEY_CHECKS = 1;