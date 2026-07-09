
SET @add_hauteur := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conteneurs' AND COLUMN_NAME = 'Hauteur') = 0,
    'ALTER TABLE Conteneurs ADD COLUMN Hauteur DECIMAL(6,2) NULL',
    'DO 0');
PREPARE stmt FROM @add_hauteur; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_largeur := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conteneurs' AND COLUMN_NAME = 'Largeur') = 0,
    'ALTER TABLE Conteneurs ADD COLUMN Largeur DECIMAL(6,2) NULL',
    'DO 0');
PREPARE stmt FROM @add_largeur; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_longueur := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Conteneurs' AND COLUMN_NAME = 'Longueur') = 0,
    'ALTER TABLE Conteneurs ADD COLUMN Longueur DECIMAL(6,2) NULL',
    'DO 0');
PREPARE stmt FROM @add_longueur; EXECUTE stmt; DEALLOCATE PREPARE stmt;
