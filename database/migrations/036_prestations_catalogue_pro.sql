SET @services_sal_notnull := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Services'
      AND COLUMN_NAME = 'Id_Salaries' AND IS_NULLABLE = 'NO'
);
SET @modify_services := IF(@services_sal_notnull > 0,
    'ALTER TABLE Services MODIFY Id_Salaries INT NULL',
    'DO 0');
PREPARE stmt FROM @modify_services; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_services_pro := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Services' AND COLUMN_NAME = 'Id_Professionnels') = 0,
    'ALTER TABLE Services ADD COLUMN Id_Professionnels INT NULL',
    'DO 0');
PREPARE stmt FROM @add_services_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_services_pro := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Services' AND CONSTRAINT_NAME = 'fk_services_pro') = 0,
    'ALTER TABLE Services ADD CONSTRAINT fk_services_pro FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)',
    'DO 0');
PREPARE stmt FROM @add_fk_services_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS Commandes_Services(
   Id_Commandes_Services INT AUTO_INCREMENT,
   Id_Services INT NOT NULL,
   Id_Utilisateurs INT NOT NULL,
   Nom_Objet VARCHAR(150) NOT NULL,
   Categorie_Objet VARCHAR(50),
   Description_Objet TEXT,
   Prix DECIMAL(15,2) NOT NULL,
   Statut VARCHAR(20) NOT NULL DEFAULT 'en_attente_paiement',
   Date_creation DATETIME,
   Reference_Stripe VARCHAR(255) NULL UNIQUE,
   PRIMARY KEY(Id_Commandes_Services),
   CONSTRAINT chk_commande_service_statut CHECK (Statut IN ('en_attente_paiement','payee','en_cours','terminee')),
   FOREIGN KEY(Id_Services) REFERENCES Services(Id_Services),
   FOREIGN KEY(Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)
);

SET @add_projet_commande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Projets' AND COLUMN_NAME = 'Id_Commandes_Services') = 0,
    'ALTER TABLE Projets ADD COLUMN Id_Commandes_Services INT NULL',
    'DO 0');
PREPARE stmt FROM @add_projet_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_projet_commande := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Projets' AND CONSTRAINT_NAME = 'fk_projet_commande_service') = 0,
    'ALTER TABLE Projets ADD CONSTRAINT fk_projet_commande_service FOREIGN KEY (Id_Commandes_Services) REFERENCES Commandes_Services(Id_Commandes_Services)',
    'DO 0');
PREPARE stmt FROM @add_fk_projet_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_conv_commande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND COLUMN_NAME = 'Id_Commandes_Services') = 0,
    'ALTER TABLE Conversations ADD COLUMN Id_Commandes_Services INT NULL',
    'DO 0');
PREPARE stmt FROM @add_conv_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_conv_commande := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND CONSTRAINT_NAME = 'fk_conv_commande_service') = 0,
    'ALTER TABLE Conversations ADD CONSTRAINT fk_conv_commande_service FOREIGN KEY (Id_Commandes_Services) REFERENCES Commandes_Services(Id_Commandes_Services)',
    'DO 0');
PREPARE stmt FROM @add_fk_conv_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_uniq_conv_commande := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND INDEX_NAME = 'uniq_conversation_commande_acheteur') = 0,
    'ALTER TABLE Conversations ADD CONSTRAINT uniq_conversation_commande_acheteur UNIQUE (Id_Commandes_Services, Id_Acheteur)',
    'DO 0');
PREPARE stmt FROM @add_uniq_conv_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_commission_commande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commissions' AND COLUMN_NAME = 'Id_Commandes_Services') = 0,
    'ALTER TABLE Commissions ADD COLUMN Id_Commandes_Services INT NULL',
    'DO 0');
PREPARE stmt FROM @add_commission_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_commission_commande := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commissions' AND CONSTRAINT_NAME = 'fk_commission_commande_service') = 0,
    'ALTER TABLE Commissions ADD CONSTRAINT fk_commission_commande_service FOREIGN KEY (Id_Commandes_Services) REFERENCES Commandes_Services(Id_Commandes_Services)',
    'DO 0');
PREPARE stmt FROM @add_fk_commission_commande; EXECUTE stmt; DEALLOCATE PREPARE stmt;
