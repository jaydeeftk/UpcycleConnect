SET @add_photo := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commandes_Services' AND COLUMN_NAME = 'Photo_Url') = 0,
    'ALTER TABLE Commandes_Services ADD COLUMN Photo_Url VARCHAR(255) NULL',
    'DO 0');
PREPARE stmt FROM @add_photo; EXECUTE stmt; DEALLOCATE PREPARE stmt;
