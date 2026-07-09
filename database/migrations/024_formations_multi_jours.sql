
SET @add_date_fin := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Formations' AND COLUMN_NAME = 'Date_fin') = 0,
    'ALTER TABLE Formations ADD COLUMN Date_fin DATE NULL AFTER Date_formation',
    'DO 0');
PREPARE stmt FROM @add_date_fin; EXECUTE stmt; DEALLOCATE PREPARE stmt;
