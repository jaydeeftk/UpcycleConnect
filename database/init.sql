-- Adminer 5.4.2 MySQL 8.0.45 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

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

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `statut`, `id_langue`, `date_inscription`) VALUES
(6,	'',	'Jaydee',	'jaydee.phanoukoun@gmail.com',	'Azerty123@',	'actif',	1,	'2026-04-05 17:38:35');

-- 2026-04-05 17:46:46 UTC
