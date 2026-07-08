SET @add_annonce := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Annonces' AND COLUMN_NAME = 'Photo_url') = 0,
    'ALTER TABLE Annonces ADD COLUMN Photo_url VARCHAR(255) NULL',
    'DO 0');
PREPARE stmt FROM @add_annonce; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_demande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_prestations' AND COLUMN_NAME = 'Photo_url') = 0,
    'ALTER TABLE Demandes_prestations ADD COLUMN Photo_url VARCHAR(255) NULL',
    'DO 0');
PREPARE stmt FROM @add_demande; EXECUTE stmt; DEALLOCATE PREPARE stmt;
