SET @add_annonce_demande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_conteneurs' AND COLUMN_NAME = 'Id_Annonces') = 0,
    'ALTER TABLE Demandes_conteneurs ADD COLUMN Id_Annonces INT NULL',
    'DO 0');
PREPARE stmt FROM @add_annonce_demande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_annonce_demande := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_conteneurs' AND CONSTRAINT_NAME = 'fk_demande_annonce') = 0,
    'ALTER TABLE Demandes_conteneurs ADD CONSTRAINT fk_demande_annonce FOREIGN KEY (Id_Annonces) REFERENCES Annonces(Id_Annonces)',
    'DO 0');
PREPARE stmt FROM @add_fk_annonce_demande; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_acheteur_annonce := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Annonces' AND COLUMN_NAME = 'Id_Acheteur_Utilisateur') = 0,
    'ALTER TABLE Annonces ADD COLUMN Id_Acheteur_Utilisateur INT NULL',
    'DO 0');
PREPARE stmt FROM @add_acheteur_annonce; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_acheteur_annonce := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Annonces' AND CONSTRAINT_NAME = 'fk_annonce_acheteur') = 0,
    'ALTER TABLE Annonces ADD CONSTRAINT fk_annonce_acheteur FOREIGN KEY (Id_Acheteur_Utilisateur) REFERENCES Utilisateurs(Id_Utilisateurs)',
    'DO 0');
PREPARE stmt FROM @add_fk_acheteur_annonce; EXECUTE stmt; DEALLOCATE PREPARE stmt;
