SET @add_origine := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Tickets' AND COLUMN_NAME = 'Origine') = 0,
    "ALTER TABLE Tickets ADD COLUMN Origine VARCHAR(10) NOT NULL DEFAULT 'client'",
    'DO 0');
PREPARE stmt FROM @add_origine; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_chk_origine := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Tickets' AND CONSTRAINT_NAME = 'chk_tickets_origine') = 0,
    "ALTER TABLE Tickets ADD CONSTRAINT chk_tickets_origine CHECK (Origine IN ('client','admin'))",
    'DO 0');
PREPARE stmt FROM @add_chk_origine; EXECUTE stmt; DEALLOCATE PREPARE stmt;
