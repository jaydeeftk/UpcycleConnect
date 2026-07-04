-- Migration 031 : la description d'une formation doit pouvoir expliquer
-- en detail le contenu du cours (255 caracteres etaient trop courts).

SET @is_short := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Formations'
      AND COLUMN_NAME = 'Description' AND DATA_TYPE = 'varchar'
);
SET @alter_description := IF(@is_short > 0,
    'ALTER TABLE Formations MODIFY COLUMN Description TEXT',
    'DO 0');
PREPARE stmt FROM @alter_description; EXECUTE stmt; DEALLOCATE PREPARE stmt;
