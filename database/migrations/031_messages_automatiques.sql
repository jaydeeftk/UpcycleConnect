SET @add_auto_msg := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Messages_Conversation' AND COLUMN_NAME = 'Est_Automatique') = 0,
    'ALTER TABLE Messages_Conversation ADD COLUMN Est_Automatique TINYINT(1) DEFAULT 0',
    'DO 0');
PREPARE stmt FROM @add_auto_msg; EXECUTE stmt; DEALLOCATE PREPARE stmt;
