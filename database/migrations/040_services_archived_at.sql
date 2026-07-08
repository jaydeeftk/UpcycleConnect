SET @add_archived := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Services' AND COLUMN_NAME = 'archived_at') = 0,
    'ALTER TABLE Services ADD COLUMN archived_at DATETIME NULL',
    'DO 0');
PREPARE stmt FROM @add_archived; EXECUTE stmt; DEALLOCATE PREPARE stmt;
