SET @add_pro_abonnement := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND COLUMN_NAME = 'Id_Professionnels') = 0,
    'ALTER TABLE Abonnement ADD COLUMN Id_Professionnels INT NULL',
    'DO 0');
PREPARE stmt FROM @add_pro_abonnement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_pro_abonnement := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND CONSTRAINT_NAME = 'fk_abonnement_professionnel') = 0,
    'ALTER TABLE Abonnement ADD CONSTRAINT fk_abonnement_professionnel FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)',
    'DO 0');
PREPARE stmt FROM @add_fk_pro_abonnement; EXECUTE stmt; DEALLOCATE PREPARE stmt;
