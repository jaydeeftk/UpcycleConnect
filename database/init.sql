-- Adminer 5.4.2 MySQL 8.0.45 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `Abonnement`;
CREATE TABLE `Abonnement` (
  `Id_Abonnement` varchar(50) NOT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Prix` decimal(15,2) DEFAULT NULL,
  `Date_Debut` date DEFAULT NULL,
  `Date_Fin` date DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id_Abonnement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Acceder`;
CREATE TABLE `Acceder` (
  `Id_Particuliers` int NOT NULL,
  `Id_Conseils` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Conseils`),
  KEY `Id_Conseils` (`Id_Conseils`),
  CONSTRAINT `Acceder_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Acceder_ibfk_2` FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils` (`Id_Conseils`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Administrateurs`;
CREATE TABLE `Administrateurs` (
  `Id_Administrateurs` int NOT NULL AUTO_INCREMENT,
  `Grade` varchar(50) DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Administrateurs`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Administrateurs_ibfk_1` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Administrer`;
CREATE TABLE `Administrer` (
  `Id_Administrateurs` int NOT NULL,
  `Id_Evenements` int NOT NULL,
  PRIMARY KEY (`Id_Administrateurs`,`Id_Evenements`),
  KEY `Id_Evenements` (`Id_Evenements`),
  CONSTRAINT `Administrer_ibfk_1` FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs` (`Id_Administrateurs`),
  CONSTRAINT `Administrer_ibfk_2` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Animer`;
CREATE TABLE `Animer` (
  `Id_Salaries` int NOT NULL,
  `Id_Evenements` int NOT NULL,
  PRIMARY KEY (`Id_Salaries`,`Id_Evenements`),
  KEY `Id_Evenements` (`Id_Evenements`),
  CONSTRAINT `Animer_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`),
  CONSTRAINT `Animer_ibfk_2` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Animer_atelier`;
CREATE TABLE `Animer_atelier` (
  `Id_Professionnels` int NOT NULL,
  `Id_Atelier` int NOT NULL,
  PRIMARY KEY (`Id_Professionnels`,`Id_Atelier`),
  KEY `Id_Atelier` (`Id_Atelier`),
  CONSTRAINT `Animer_atelier_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`),
  CONSTRAINT `Animer_atelier_ibfk_2` FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier` (`Id_Atelier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Animer_formation`;
CREATE TABLE `Animer_formation` (
  `Id_Salaries` int NOT NULL,
  `Id_Formations` int NOT NULL,
  PRIMARY KEY (`Id_Salaries`,`Id_Formations`),
  KEY `Id_Formations` (`Id_Formations`),
  CONSTRAINT `Animer_formation_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`),
  CONSTRAINT `Animer_formation_ibfk_2` FOREIGN KEY (`Id_Formations`) REFERENCES `Formations` (`Id_Formations`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Appuyer`;
CREATE TABLE `Appuyer` (
  `Id_Conseils` int NOT NULL,
  `Id_Medias` int NOT NULL,
  PRIMARY KEY (`Id_Conseils`,`Id_Medias`),
  KEY `Id_Medias` (`Id_Medias`),
  CONSTRAINT `Appuyer_ibfk_1` FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils` (`Id_Conseils`),
  CONSTRAINT `Appuyer_ibfk_2` FOREIGN KEY (`Id_Medias`) REFERENCES `Medias` (`Id_Medias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Atelier`;
CREATE TABLE `Atelier` (
  `Id_Atelier` int NOT NULL AUTO_INCREMENT,
  `Theme` varchar(100) DEFAULT NULL,
  `Date_creation` datetime DEFAULT NULL,
  `Createur` varchar(100) DEFAULT NULL,
  `Date_atelier` datetime DEFAULT NULL,
  `Lieu` varchar(255) DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  PRIMARY KEY (`Id_Atelier`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  CONSTRAINT `Atelier_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Avis`;
CREATE TABLE `Avis` (
  `Id_Avis` int NOT NULL AUTO_INCREMENT,
  `Date_du_post` datetime DEFAULT NULL,
  `Contenu` text,
  `Id_Professionnels` int DEFAULT NULL,
  `Id_Evenements` int DEFAULT NULL,
  `Id_Particuliers` int DEFAULT NULL,
  PRIMARY KEY (`Id_Avis`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  KEY `Id_Evenements` (`Id_Evenements`),
  KEY `Id_Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Avis_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`),
  CONSTRAINT `Avis_ibfk_2` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`),
  CONSTRAINT `Avis_ibfk_3` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Badges`;
CREATE TABLE `Badges` (
  `Id_Badges` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(100) DEFAULT NULL,
  `Date_obtention` datetime DEFAULT NULL,
  PRIMARY KEY (`Id_Badges`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Catalogue`;
CREATE TABLE `Catalogue` (
  `Id_Catalogue` int NOT NULL AUTO_INCREMENT,
  `Description` text,
  `Illustration` varchar(255) DEFAULT NULL,
  `Id_Administrateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Catalogue`),
  KEY `Id_Administrateurs` (`Id_Administrateurs`),
  CONSTRAINT `Catalogue_ibfk_1` FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs` (`Id_Administrateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Codes_Barres`;
CREATE TABLE `Codes_Barres` (
  `Id_Codes_Barres` int NOT NULL AUTO_INCREMENT,
  `Date_generation` datetime DEFAULT NULL,
  `Id_Conteneurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Codes_Barres`),
  KEY `Id_Conteneurs` (`Id_Conteneurs`),
  CONSTRAINT `Codes_Barres_ibfk_1` FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs` (`Id_Conteneurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Completer`;
CREATE TABLE `Completer` (
  `Id_Projets` int NOT NULL,
  `Id_Medias` int NOT NULL,
  PRIMARY KEY (`Id_Projets`,`Id_Medias`),
  KEY `Id_Medias` (`Id_Medias`),
  CONSTRAINT `Completer_ibfk_1` FOREIGN KEY (`Id_Projets`) REFERENCES `Projets` (`Id_Projets`),
  CONSTRAINT `Completer_ibfk_2` FOREIGN KEY (`Id_Medias`) REFERENCES `Medias` (`Id_Medias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Conseils`;
CREATE TABLE `Conseils` (
  `Id_Conseils` int NOT NULL AUTO_INCREMENT,
  `Date_d_ajout` datetime DEFAULT NULL,
  `Contenu` text,
  `Id_Salaries` int DEFAULT NULL,
  PRIMARY KEY (`Id_Conseils`),
  KEY `Id_Salaries` (`Id_Salaries`),
  CONSTRAINT `Conseils_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Consulter`;
CREATE TABLE `Consulter` (
  `Id_Professionnels` int NOT NULL,
  `Id_Annonces` int NOT NULL,
  PRIMARY KEY (`Id_Professionnels`,`Id_Annonces`),
  KEY `Id_Annonces` (`Id_Annonces`),
  CONSTRAINT `Consulter_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`),
  CONSTRAINT `Consulter_ibfk_2` FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces` (`Id_Annonces`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Conteneurs`;
CREATE TABLE `Conteneurs` (
  `Id_Conteneurs` int NOT NULL AUTO_INCREMENT,
  `Localisation` varchar(255) DEFAULT NULL,
  `Capacite` varchar(50) DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Id_Administrateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Conteneurs`),
  KEY `Id_Administrateurs` (`Id_Administrateurs`),
  CONSTRAINT `Conteneurs_ibfk_1` FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs` (`Id_Administrateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Contenir_atelier`;
CREATE TABLE `Contenir_atelier` (
  `Id_Planning_personnel` int NOT NULL,
  `Id_Atelier` int NOT NULL,
  PRIMARY KEY (`Id_Planning_personnel`,`Id_Atelier`),
  KEY `Id_Atelier` (`Id_Atelier`),
  CONSTRAINT `Contenir_atelier_ibfk_1` FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel` (`Id_Planning_personnel`),
  CONSTRAINT `Contenir_atelier_ibfk_2` FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier` (`Id_Atelier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Contenir_evenements`;
CREATE TABLE `Contenir_evenements` (
  `Id_Evenements` int NOT NULL,
  `Id_Planning_personnel` int NOT NULL,
  PRIMARY KEY (`Id_Evenements`,`Id_Planning_personnel`),
  KEY `Id_Planning_personnel` (`Id_Planning_personnel`),
  CONSTRAINT `Contenir_evenements_ibfk_1` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`),
  CONSTRAINT `Contenir_evenements_ibfk_2` FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel` (`Id_Planning_personnel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Contenir_formations`;
CREATE TABLE `Contenir_formations` (
  `Id_Formations` int NOT NULL,
  `Id_Planning_personnel` int NOT NULL,
  PRIMARY KEY (`Id_Formations`,`Id_Planning_personnel`),
  KEY `Id_Planning_personnel` (`Id_Planning_personnel`),
  CONSTRAINT `Contenir_formations_ibfk_1` FOREIGN KEY (`Id_Formations`) REFERENCES `Formations` (`Id_Formations`),
  CONSTRAINT `Contenir_formations_ibfk_2` FOREIGN KEY (`Id_Planning_personnel`) REFERENCES `Planning_personnel` (`Id_Planning_personnel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Contrats`;
CREATE TABLE `Contrats` (
  `Id_Contrats` int NOT NULL AUTO_INCREMENT,
  `Date_signature` datetime DEFAULT NULL,
  `Date_debut` datetime DEFAULT NULL,
  `Date_fin` datetime DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  PRIMARY KEY (`Id_Contrats`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  CONSTRAINT `Contrats_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Etapes`;
CREATE TABLE `Etapes` (
  `Id_Etapes` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(100) DEFAULT NULL,
  `Description` text,
  `Visuel` varchar(255) DEFAULT NULL,
  `Id_Projets` int DEFAULT NULL,
  PRIMARY KEY (`Id_Etapes`),
  KEY `Id_Projets` (`Id_Projets`),
  CONSTRAINT `Etapes_ibfk_1` FOREIGN KEY (`Id_Projets`) REFERENCES `Projets` (`Id_Projets`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Evenements`;
CREATE TABLE `Evenements` (
  `Id_Evenements` int NOT NULL AUTO_INCREMENT,
  `Date_Evenement` datetime DEFAULT NULL,
  `Titre` varchar(100) DEFAULT NULL,
  `Description` text,
  `Lieu` varchar(255) DEFAULT NULL,
  `Capacite` int DEFAULT NULL,
  `Id_Salaries` int DEFAULT NULL,
  PRIMARY KEY (`Id_Evenements`),
  KEY `Id_Salaries` (`Id_Salaries`),
  CONSTRAINT `Evenements_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Favoris`;
CREATE TABLE `Favoris` (
  `Id_Professionnels` int NOT NULL,
  `Id_Annonces` int NOT NULL,
  PRIMARY KEY (`Id_Professionnels`,`Id_Annonces`),
  KEY `Id_Annonces` (`Id_Annonces`),
  CONSTRAINT `Favoris_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`),
  CONSTRAINT `Favoris_ibfk_2` FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces` (`Id_Annonces`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Formations`;
CREATE TABLE `Formations` (
  `Id_Formations` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(100) DEFAULT NULL,
  `Description` text,
  `Prix` decimal(15,3) DEFAULT NULL,
  `Duree` int DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Id_Salaries` int DEFAULT NULL,
  PRIMARY KEY (`Id_Formations`),
  KEY `Id_Salaries` (`Id_Salaries`),
  CONSTRAINT `Formations_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Forum`;
CREATE TABLE `Forum` (
  `Id_Forum` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Id_Forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Gagner`;
CREATE TABLE `Gagner` (
  `Id_Particuliers` int NOT NULL,
  `Id_Badges` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Badges`),
  KEY `Id_Badges` (`Id_Badges`),
  CONSTRAINT `Gagner_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Gagner_ibfk_2` FOREIGN KEY (`Id_Badges`) REFERENCES `Badges` (`Id_Badges`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Historique`;
CREATE TABLE `Historique` (
  `Id_Historique` int NOT NULL AUTO_INCREMENT,
  `Date_Debut` datetime DEFAULT NULL,
  `Statut_depot` varchar(50) DEFAULT NULL,
  `Observations` text,
  `Id_Particuliers` int DEFAULT NULL,
  PRIMARY KEY (`Id_Historique`),
  KEY `Id_Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Historique_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Historique_conteneur`;
CREATE TABLE `Historique_conteneur` (
  `Id_Conteneurs` int NOT NULL,
  `Id_Historique` int NOT NULL,
  PRIMARY KEY (`Id_Conteneurs`,`Id_Historique`),
  KEY `Id_Historique` (`Id_Historique`),
  CONSTRAINT `Historique_conteneur_ibfk_1` FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs` (`Id_Conteneurs`),
  CONSTRAINT `Historique_conteneur_ibfk_2` FOREIGN KEY (`Id_Historique`) REFERENCES `Historique` (`Id_Historique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Historique_objets`;
CREATE TABLE `Historique_objets` (
  `Id_Objets` int NOT NULL,
  `Id_Historique` int NOT NULL,
  PRIMARY KEY (`Id_Objets`,`Id_Historique`),
  KEY `Id_Historique` (`Id_Historique`),
  CONSTRAINT `Historique_objets_ibfk_1` FOREIGN KEY (`Id_Objets`) REFERENCES `Objets` (`Id_Objets`),
  CONSTRAINT `Historique_objets_ibfk_2` FOREIGN KEY (`Id_Historique`) REFERENCES `Historique` (`Id_Historique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Inclure_publicites`;
CREATE TABLE `Inclure_publicites` (
  `Id_Contrats` int NOT NULL,
  `Id_Publicites` varchar(50) NOT NULL,
  PRIMARY KEY (`Id_Contrats`,`Id_Publicites`),
  KEY `Id_Publicites` (`Id_Publicites`),
  CONSTRAINT `Inclure_publicites_ibfk_1` FOREIGN KEY (`Id_Contrats`) REFERENCES `Contrats` (`Id_Contrats`),
  CONSTRAINT `Inclure_publicites_ibfk_2` FOREIGN KEY (`Id_Publicites`) REFERENCES `Publicites` (`Id_Publicites`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Interagir`;
CREATE TABLE `Interagir` (
  `Id_Particuliers` int NOT NULL,
  `Id_Catalogue` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Catalogue`),
  KEY `Id_Catalogue` (`Id_Catalogue`),
  CONSTRAINT `Interagir_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Interagir_ibfk_2` FOREIGN KEY (`Id_Catalogue`) REFERENCES `Catalogue` (`Id_Catalogue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Langue`;
CREATE TABLE `Langue` (
  `Id_Langue` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Medias`;
CREATE TABLE `Medias` (
  `Id_Medias` int NOT NULL AUTO_INCREMENT,
  `Date_Ajout` datetime DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `Id_Annonces` int DEFAULT NULL,
  PRIMARY KEY (`Id_Medias`),
  KEY `Id_Annonces` (`Id_Annonces`),
  CONSTRAINT `Medias_ibfk_1` FOREIGN KEY (`Id_Annonces`) REFERENCES `Annonces` (`Id_Annonces`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Moderer`;
CREATE TABLE `Moderer` (
  `Id_Salaries` int NOT NULL,
  `Id_Forum` int NOT NULL,
  PRIMARY KEY (`Id_Salaries`,`Id_Forum`),
  KEY `Id_Forum` (`Id_Forum`),
  CONSTRAINT `Moderer_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`),
  CONSTRAINT `Moderer_ibfk_2` FOREIGN KEY (`Id_Forum`) REFERENCES `Forum` (`Id_Forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Notifications`;
CREATE TABLE `Notifications` (
  `Id_Notifications` int NOT NULL AUTO_INCREMENT,
  `Contenu` text,
  `Date_Envoi` datetime DEFAULT NULL,
  `Statut` tinyint(1) DEFAULT NULL,
  `Id_Administrateurs` int DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Notifications`),
  KEY `Id_Administrateurs` (`Id_Administrateurs`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Notifications_ibfk_1` FOREIGN KEY (`Id_Administrateurs`) REFERENCES `Administrateurs` (`Id_Administrateurs`),
  CONSTRAINT `Notifications_ibfk_2` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Objets`;
CREATE TABLE `Objets` (
  `Id_Objets` int NOT NULL AUTO_INCREMENT,
  `Type` varchar(50) DEFAULT NULL,
  `Poids` varchar(50) DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Id_Conteneurs` int DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  `Id_Particuliers` int DEFAULT NULL,
  PRIMARY KEY (`Id_Objets`),
  KEY `Id_Conteneurs` (`Id_Conteneurs`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  KEY `Id_Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Objets_ibfk_1` FOREIGN KEY (`Id_Conteneurs`) REFERENCES `Conteneurs` (`Id_Conteneurs`),
  CONSTRAINT `Objets_ibfk_2` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`),
  CONSTRAINT `Objets_ibfk_3` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Participer`;
CREATE TABLE `Participer` (
  `Id_Particuliers` int NOT NULL,
  `Id_Formations` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Formations`),
  KEY `Id_Formations` (`Id_Formations`),
  CONSTRAINT `Participer_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Participer_ibfk_2` FOREIGN KEY (`Id_Formations`) REFERENCES `Formations` (`Id_Formations`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Participer_atelier`;
CREATE TABLE `Participer_atelier` (
  `Id_Particuliers` int NOT NULL,
  `Id_Atelier` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Atelier`),
  KEY `Id_Atelier` (`Id_Atelier`),
  CONSTRAINT `Participer_atelier_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Participer_atelier_ibfk_2` FOREIGN KEY (`Id_Atelier`) REFERENCES `Atelier` (`Id_Atelier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Participer_evenements`;
CREATE TABLE `Participer_evenements` (
  `Id_Particuliers` int NOT NULL,
  `Id_Evenements` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Evenements`),
  KEY `Id_Evenements` (`Id_Evenements`),
  CONSTRAINT `Participer_evenements_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Participer_evenements_ibfk_2` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Particuliers`;
CREATE TABLE `Particuliers` (
  `Id_Particuliers` int NOT NULL AUTO_INCREMENT,
  `Score` int DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Particuliers`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Particuliers_ibfk_1` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Planifier_evenements`;
CREATE TABLE `Planifier_evenements` (
  `Id_Evenements` int NOT NULL,
  `Id_Planning` int NOT NULL,
  PRIMARY KEY (`Id_Evenements`,`Id_Planning`),
  KEY `Id_Planning` (`Id_Planning`),
  CONSTRAINT `Planifier_evenements_ibfk_1` FOREIGN KEY (`Id_Evenements`) REFERENCES `Evenements` (`Id_Evenements`),
  CONSTRAINT `Planifier_evenements_ibfk_2` FOREIGN KEY (`Id_Planning`) REFERENCES `Planning` (`Id_Planning`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Planifier_formation`;
CREATE TABLE `Planifier_formation` (
  `Id_Formations` int NOT NULL,
  `Id_Planning` int NOT NULL,
  PRIMARY KEY (`Id_Formations`,`Id_Planning`),
  KEY `Id_Planning` (`Id_Planning`),
  CONSTRAINT `Planifier_formation_ibfk_1` FOREIGN KEY (`Id_Formations`) REFERENCES `Formations` (`Id_Formations`),
  CONSTRAINT `Planifier_formation_ibfk_2` FOREIGN KEY (`Id_Planning`) REFERENCES `Planning` (`Id_Planning`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Planning`;
CREATE TABLE `Planning` (
  `Id_Planning` int NOT NULL AUTO_INCREMENT,
  `Date_debut` datetime DEFAULT NULL,
  `Date_fin` datetime DEFAULT NULL,
  `Periode` varchar(50) DEFAULT NULL,
  `Id_Salaries` int DEFAULT NULL,
  PRIMARY KEY (`Id_Planning`),
  KEY `Id_Salaries` (`Id_Salaries`),
  CONSTRAINT `Planning_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Planning_personnel`;
CREATE TABLE `Planning_personnel` (
  `Id_Planning_personnel` int NOT NULL AUTO_INCREMENT,
  `Date_debut` datetime DEFAULT NULL,
  `Date_fin` datetime DEFAULT NULL,
  `Id_Particuliers` int DEFAULT NULL,
  PRIMARY KEY (`Id_Planning_personnel`),
  KEY `Id_Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Planning_personnel_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Professionnels_artisans`;
CREATE TABLE `Professionnels_artisans` (
  `Id_Professionnels` int NOT NULL AUTO_INCREMENT,
  `Siret` varchar(14) DEFAULT NULL,
  `Nom_Entreprise` varchar(100) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Id_Abonnement` varchar(50) DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Professionnels`),
  KEY `Id_Abonnement` (`Id_Abonnement`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Professionnels_artisans_ibfk_1` FOREIGN KEY (`Id_Abonnement`) REFERENCES `Abonnement` (`Id_Abonnement`),
  CONSTRAINT `Professionnels_artisans_ibfk_2` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Projets`;
CREATE TABLE `Projets` (
  `Id_Projets` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(100) DEFAULT NULL,
  `Description` text,
  `Date_Debut` datetime DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  PRIMARY KEY (`Id_Projets`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  CONSTRAINT `Projets_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Publicites`;
CREATE TABLE `Publicites` (
  `Id_Publicites` varchar(50) NOT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Prix` int DEFAULT NULL,
  `Date_Debut` date DEFAULT NULL,
  `Date_Fin` date DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Description` text,
  `Illustration` varchar(255) DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  PRIMARY KEY (`Id_Publicites`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  CONSTRAINT `Publicites_ibfk_1` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Reponses`;
CREATE TABLE `Reponses` (
  `Id_Reponses` int NOT NULL AUTO_INCREMENT,
  `Contenu` text,
  `Date_Reponse` datetime DEFAULT NULL,
  `Id_Sujets` int DEFAULT NULL,
  `Id_Professionnels` int DEFAULT NULL,
  PRIMARY KEY (`Id_Reponses`),
  KEY `Id_Sujets` (`Id_Sujets`),
  KEY `Id_Professionnels` (`Id_Professionnels`),
  CONSTRAINT `Reponses_ibfk_1` FOREIGN KEY (`Id_Sujets`) REFERENCES `Sujets` (`Id_Sujets`),
  CONSTRAINT `Reponses_ibfk_2` FOREIGN KEY (`Id_Professionnels`) REFERENCES `Professionnels_artisans` (`Id_Professionnels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Reserver_formation`;
CREATE TABLE `Reserver_formation` (
  `Id_Particuliers` int NOT NULL,
  `Id_Formations` int NOT NULL,
  PRIMARY KEY (`Id_Particuliers`,`Id_Formations`),
  KEY `Id_Formations` (`Id_Formations`),
  CONSTRAINT `Reserver_formation_ibfk_1` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Reserver_formation_ibfk_2` FOREIGN KEY (`Id_Formations`) REFERENCES `Formations` (`Id_Formations`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Salaries`;
CREATE TABLE `Salaries` (
  `Id_Salaries` int NOT NULL AUTO_INCREMENT,
  `Poste` varchar(100) DEFAULT NULL,
  `Responsable` varchar(100) DEFAULT NULL,
  `Date_Debut_Contrat` datetime DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Salaries`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Salaries_ibfk_1` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Score`;
CREATE TABLE `Score` (
  `Id_Score` int NOT NULL AUTO_INCREMENT,
  `Appreciation` varchar(50) DEFAULT NULL,
  `Valeur` decimal(15,2) DEFAULT NULL,
  `Commentaires` text,
  `Ressources_economisees` varchar(100) DEFAULT NULL,
  `Id_Utilisateurs` int DEFAULT NULL,
  PRIMARY KEY (`Id_Score`),
  KEY `Id_Utilisateurs` (`Id_Utilisateurs`),
  CONSTRAINT `Score_ibfk_1` FOREIGN KEY (`Id_Utilisateurs`) REFERENCES `Utilisateurs` (`Id_Utilisateurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Services`;
CREATE TABLE `Services` (
  `Id_Services` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(100) DEFAULT NULL,
  `Description` text,
  `Prix` decimal(15,2) DEFAULT NULL,
  `Duree` int DEFAULT NULL,
  `Categorie` varchar(50) DEFAULT NULL,
  `Id_Salaries` int DEFAULT NULL,
  PRIMARY KEY (`Id_Services`),
  KEY `Id_Salaries` (`Id_Salaries`),
  CONSTRAINT `Services_ibfk_1` FOREIGN KEY (`Id_Salaries`) REFERENCES `Salaries` (`Id_Salaries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Sujets`;
CREATE TABLE `Sujets` (
  `Id_Sujets` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(100) DEFAULT NULL,
  `Date_Creation` datetime DEFAULT NULL,
  `Id_Forum` int DEFAULT NULL,
  `Id_Particuliers` int DEFAULT NULL,
  PRIMARY KEY (`Id_Sujets`),
  KEY `Id_Forum` (`Id_Forum`),
  KEY `Id_Particuliers` (`Id_Particuliers`),
  CONSTRAINT `Sujets_ibfk_1` FOREIGN KEY (`Id_Forum`) REFERENCES `Forum` (`Id_Forum`),
  CONSTRAINT `Sujets_ibfk_2` FOREIGN KEY (`Id_Particuliers`) REFERENCES `Particuliers` (`Id_Particuliers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Traduire_Conseils`;
CREATE TABLE `Traduire_Conseils` (
  `Id_Conseils` int NOT NULL,
  `Id_Langue` int NOT NULL,
  PRIMARY KEY (`Id_Conseils`,`Id_Langue`),
  KEY `Id_Langue` (`Id_Langue`),
  CONSTRAINT `Traduire_Conseils_ibfk_1` FOREIGN KEY (`Id_Conseils`) REFERENCES `Conseils` (`Id_Conseils`),
  CONSTRAINT `Traduire_Conseils_ibfk_2` FOREIGN KEY (`Id_Langue`) REFERENCES `Langue` (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Traduire_Contrats`;
CREATE TABLE `Traduire_Contrats` (
  `Id_Contrats` int NOT NULL,
  `Id_Langue` int NOT NULL,
  PRIMARY KEY (`Id_Contrats`,`Id_Langue`),
  KEY `Id_Langue` (`Id_Langue`),
  CONSTRAINT `Traduire_Contrats_ibfk_1` FOREIGN KEY (`Id_Contrats`) REFERENCES `Contrats` (`Id_Contrats`),
  CONSTRAINT `Traduire_Contrats_ibfk_2` FOREIGN KEY (`Id_Langue`) REFERENCES `Langue` (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Traduire_Notifications`;
CREATE TABLE `Traduire_Notifications` (
  `Id_Notifications` int NOT NULL,
  `Id_Langue` int NOT NULL,
  PRIMARY KEY (`Id_Notifications`,`Id_Langue`),
  KEY `Id_Langue` (`Id_Langue`),
  CONSTRAINT `Traduire_Notifications_ibfk_1` FOREIGN KEY (`Id_Notifications`) REFERENCES `Notifications` (`Id_Notifications`),
  CONSTRAINT `Traduire_Notifications_ibfk_2` FOREIGN KEY (`Id_Langue`) REFERENCES `Langue` (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Traduire_Services`;
CREATE TABLE `Traduire_Services` (
  `Id_Services` int NOT NULL,
  `Id_Langue` int NOT NULL,
  PRIMARY KEY (`Id_Services`,`Id_Langue`),
  KEY `Id_Langue` (`Id_Langue`),
  CONSTRAINT `Traduire_Services_ibfk_1` FOREIGN KEY (`Id_Services`) REFERENCES `Services` (`Id_Services`),
  CONSTRAINT `Traduire_Services_ibfk_2` FOREIGN KEY (`Id_Langue`) REFERENCES `Langue` (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `Utilisateurs`;
CREATE TABLE `Utilisateurs` (
  `Id_Utilisateurs` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(100) DEFAULT NULL,
  `Prenom` varchar(100) DEFAULT NULL,
  `Telephone` varchar(20) DEFAULT NULL,
  `Statut` varchar(50) DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL,
  `Mot_de_passe` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Date_Inscription` datetime DEFAULT NULL,
  `Date_naissance` datetime DEFAULT NULL,
  `Id_Langue` int DEFAULT NULL,
  PRIMARY KEY (`Id_Utilisateurs`),
  UNIQUE KEY `Email` (`Email`),
  KEY `Id_Langue` (`Id_Langue`),
  CONSTRAINT `Utilisateurs_ibfk_1` FOREIGN KEY (`Id_Langue`) REFERENCES `Langue` (`Id_Langue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `annonces`;
CREATE TABLE `annonces` (
  `id_annonce` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_publication` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_particulier` int DEFAULT NULL,
  PRIMARY KEY (`id_annonce`),
  KEY `id_particulier` (`id_particulier`),
  CONSTRAINT `annonces_ibfk_1` FOREIGN KEY (`id_particulier`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text,
  `icone` varchar(100) DEFAULT NULL,
  `statut` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `evenements`;
CREATE TABLE `evenements` (
  `id_evenement` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `lieu` varchar(255) DEFAULT NULL,
  `date_evenement` datetime DEFAULT NULL,
  `id_salarie` int DEFAULT NULL,
  PRIMARY KEY (`id_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `id_particulier` int DEFAULT NULL,
  PRIMARY KEY (`id_message`),
  KEY `id_particulier` (`id_particulier`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_particulier`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `paiements`;
CREATE TABLE `paiements` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(10,2) NOT NULL,
  `statut` tinyint NOT NULL DEFAULT '0',
  `date_paiement` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_utilisateur` int DEFAULT NULL,
  PRIMARY KEY (`id_paiement`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL DEFAULT '0.00',
  `categorie` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE `utilisateurs` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `statut` enum('actif','inactif','banni') NOT NULL DEFAULT 'actif',
  `id_langue` int NOT NULL DEFAULT '1',
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- 2026-04-05 16:34:40 UTC
