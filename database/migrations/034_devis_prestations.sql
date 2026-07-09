CREATE TABLE IF NOT EXISTS Devis(
   Id_Devis INT AUTO_INCREMENT,
   Id_Demandes_prestations INT NOT NULL,
   Id_Professionnels INT NOT NULL,
   Prix DECIMAL(15,2) NOT NULL,
   Message VARCHAR(500),
   Statut VARCHAR(20) NOT NULL DEFAULT 'propose',
   Date_creation DATETIME,
   Reference_Stripe VARCHAR(255) NULL UNIQUE,
   PRIMARY KEY(Id_Devis),
   UNIQUE KEY uniq_devis_demande_pro (Id_Demandes_prestations, Id_Professionnels),
   FOREIGN KEY(Id_Demandes_prestations) REFERENCES Demandes_prestations(Id_Demandes_prestations),
   FOREIGN KEY(Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)
);

SET @conv_annonce_nullable := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations'
      AND COLUMN_NAME = 'Id_Annonces' AND IS_NULLABLE = 'NO'
);

SET @modify_stmt := IF(@conv_annonce_nullable > 0,
    'ALTER TABLE Conversations MODIFY Id_Annonces INT NULL',
    'DO 0');
PREPARE stmt FROM @modify_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_conv_presta := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND COLUMN_NAME = 'Id_Demandes_prestations') = 0,
    'ALTER TABLE Conversations ADD COLUMN Id_Demandes_prestations INT NULL',
    'DO 0');
PREPARE stmt FROM @add_conv_presta; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_conv_presta := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND CONSTRAINT_NAME = 'fk_conv_demande_prestation') = 0,
    'ALTER TABLE Conversations ADD CONSTRAINT fk_conv_demande_prestation FOREIGN KEY (Id_Demandes_prestations) REFERENCES Demandes_prestations(Id_Demandes_prestations)',
    'DO 0');
PREPARE stmt FROM @add_fk_conv_presta; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_uniq_conv_presta := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conversations' AND INDEX_NAME = 'uniq_conversation_prestation_acheteur') = 0,
    'ALTER TABLE Conversations ADD CONSTRAINT uniq_conversation_prestation_acheteur UNIQUE (Id_Demandes_prestations, Id_Acheteur)',
    'DO 0');
PREPARE stmt FROM @add_uniq_conv_presta; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_projet_presta := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Projets' AND COLUMN_NAME = 'Id_Demandes_prestations') = 0,
    'ALTER TABLE Projets ADD COLUMN Id_Demandes_prestations INT NULL',
    'DO 0');
PREPARE stmt FROM @add_projet_presta; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_projet_presta := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Projets' AND CONSTRAINT_NAME = 'fk_projet_demande_prestation') = 0,
    'ALTER TABLE Projets ADD CONSTRAINT fk_projet_demande_prestation FOREIGN KEY (Id_Demandes_prestations) REFERENCES Demandes_prestations(Id_Demandes_prestations)',
    'DO 0');
PREPARE stmt FROM @add_fk_projet_presta; EXECUTE stmt; DEALLOCATE PREPARE stmt;
