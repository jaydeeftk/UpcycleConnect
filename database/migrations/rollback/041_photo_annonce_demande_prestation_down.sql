SET @drop_annonce := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Annonces' AND COLUMN_NAME = 'Photo_url') = 1,
    'ALTER TABLE Annonces DROP COLUMN Photo_url',
    'DO 0');
PREPARE stmt FROM @drop_annonce; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @drop_demande := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_prestations' AND COLUMN_NAME = 'Photo_url') = 1,
    'ALTER TABLE Demandes_prestations DROP COLUMN Photo_url',
    'DO 0');
PREPARE stmt FROM @drop_demande; EXECUTE stmt; DEALLOCATE PREPARE stmt;
