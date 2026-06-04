-- =============================================================
--  Application RGPD — base de données isolée
--  Gestion des données personnelles des adhérents transités
--  par la Suisse (node "Application RGPD" + site "SUISSE" du
--  schéma réseau). Base distincte de l'application principale :
--  cloisonnement, minimisation et accès uniquement via le
--  bastion Teleport (RGPD remote access).
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- -------------------------------------------------------------
-- Adhérents : données personnelles des personnes ayant transité
-- par la Suisse (pays tiers couvert par décision d'adéquation).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Adherents (
    Id_Adherent           INT AUTO_INCREMENT PRIMARY KEY,
    Civilite              VARCHAR(10),
    Nom                   VARCHAR(100) NOT NULL,
    Prenom                VARCHAR(100) NOT NULL,
    Email                 VARCHAR(190) NOT NULL UNIQUE,
    Telephone             VARCHAR(30),
    Pays_Residence        VARCHAR(80)  DEFAULT 'France',
    Date_Transit_Suisse   DATE,
    Canton_Suisse         VARCHAR(80),
    Motif_Transfert       VARCHAR(255),
    Base_Legale_Transfert VARCHAR(120) DEFAULT 'Décision d''adéquation (Suisse)',
    Statut                ENUM('actif','anonymise','supprime') NOT NULL DEFAULT 'actif',
    Date_Creation         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Date_Maj              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_adherents_statut (Statut),
    INDEX idx_adherents_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Consentements : finalité par finalité (art. 6 & 7 RGPD).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Consentements (
    Id_Consentement    INT AUTO_INCREMENT PRIMARY KEY,
    Id_Adherent        INT NOT NULL,
    Finalite           VARCHAR(150) NOT NULL,
    Base_Legale        VARCHAR(120) NOT NULL DEFAULT 'Consentement',
    Consenti           TINYINT(1) NOT NULL DEFAULT 0,
    Date_Consentement  DATETIME,
    Date_Retrait       DATETIME NULL,
    Version_Politique  VARCHAR(20),
    CONSTRAINT fk_consent_adherent FOREIGN KEY (Id_Adherent)
        REFERENCES Adherents (Id_Adherent) ON DELETE CASCADE,
    INDEX idx_consent_adherent (Id_Adherent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Registre des traitements (art. 30 RGPD).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Registre_Traitements (
    Id_Traitement       INT AUTO_INCREMENT PRIMARY KEY,
    Nom                 VARCHAR(150) NOT NULL,
    Finalite            VARCHAR(255) NOT NULL,
    Base_Legale         VARCHAR(120) NOT NULL,
    Categories_Donnees  VARCHAR(255),
    Destinataires       VARCHAR(255),
    Transfert_Hors_UE   TINYINT(1) NOT NULL DEFAULT 0,
    Pays_Destinataire   VARCHAR(80),
    Garantie_Transfert  VARCHAR(150),
    Duree_Conservation  VARCHAR(120),
    Mesures_Securite    VARCHAR(255),
    Date_Creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Demandes d'exercice des droits (art. 15 à 21 RGPD).
-- Échéance légale par défaut : J+30.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Demandes_Droits (
    Id_Demande      INT AUTO_INCREMENT PRIMARY KEY,
    Id_Adherent     INT NOT NULL,
    Type_Droit      ENUM('acces','rectification','effacement','portabilite','limitation','opposition') NOT NULL,
    Statut          ENUM('recue','en_cours','traitee','refusee') NOT NULL DEFAULT 'recue',
    Date_Demande    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Date_Echeance   DATE,
    Date_Traitement DATETIME NULL,
    Traite_Par      VARCHAR(120),
    Commentaire     VARCHAR(500),
    CONSTRAINT fk_demande_adherent FOREIGN KEY (Id_Adherent)
        REFERENCES Adherents (Id_Adherent) ON DELETE CASCADE,
    INDEX idx_demande_statut (Statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Journal d'accès (art. 5.2 responsabilité & art. 32 sécurité).
-- Trace chaque consultation / modification, attribuée à
-- l'identité fournie par le bastion Teleport.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Journal_Acces (
    Id_Journal       BIGINT AUTO_INCREMENT PRIMARY KEY,
    Acteur           VARCHAR(120) NOT NULL,
    Identite_Teleport VARCHAR(120),
    Action           VARCHAR(80) NOT NULL,
    Cible_Type       VARCHAR(60),
    Cible_Id         VARCHAR(60),
    Details          VARCHAR(500),
    Adresse_IP       VARCHAR(64),
    Horodatage       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_journal_horodatage (Horodatage),
    INDEX idx_journal_acteur (Acteur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Données de référence : entrée du registre des traitements
-- pour ce traitement précis (transfert via la Suisse).
-- -------------------------------------------------------------
INSERT INTO Registre_Traitements
    (Nom, Finalite, Base_Legale, Categories_Donnees, Destinataires,
     Transfert_Hors_UE, Pays_Destinataire, Garantie_Transfert,
     Duree_Conservation, Mesures_Securite)
SELECT
    'Gestion des adhérents transités par la Suisse',
    'Suivi des adhérents dont les données transitent par le site suisse',
    'Intérêt légitime / Consentement',
    'Identité, coordonnées, données de transit',
    'Service RGPD interne (DPO)',
    1, 'Suisse', 'Décision d''adéquation de la Commission européenne',
    '3 ans après le dernier contact',
    'Cloisonnement réseau, accès via bastion Teleport, chiffrement en transit, journalisation'
WHERE NOT EXISTS (SELECT 1 FROM Registre_Traitements);

-- Adhérents de démonstration (données fictives).
INSERT INTO Adherents (Civilite, Nom, Prenom, Email, Telephone, Pays_Residence, Date_Transit_Suisse, Canton_Suisse, Motif_Transfert)
SELECT 'M.', 'Rochat', 'Camille', 'camille.rochat@example.ch', '+41 21 000 00 00', 'Suisse', '2025-09-12', 'Vaud', 'Atelier upcycling Lausanne'
WHERE NOT EXISTS (SELECT 1 FROM Adherents WHERE Email = 'camille.rochat@example.ch');

INSERT INTO Adherents (Civilite, Nom, Prenom, Email, Telephone, Pays_Residence, Date_Transit_Suisse, Canton_Suisse, Motif_Transfert)
SELECT 'Mme', 'Favre', 'Inès', 'ines.favre@example.ch', '+41 22 000 00 00', 'France', '2025-10-03', 'Genève', 'Collecte transfrontalière'
WHERE NOT EXISTS (SELECT 1 FROM Adherents WHERE Email = 'ines.favre@example.ch');
