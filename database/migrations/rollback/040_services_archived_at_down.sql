SET @drop_archived := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Services' AND COLUMN_NAME = 'archived_at') = 1,
    'ALTER TABLE Services DROP COLUMN archived_at',
    'DO 0');
PREPARE stmt FROM @drop_archived; EXECUTE stmt; DEALLOCATE PREPARE stmt;
