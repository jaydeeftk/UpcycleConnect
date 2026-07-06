SET @add_type_evt := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Messages_Conversation' AND COLUMN_NAME = 'Type_Evenement') = 0,
    'ALTER TABLE Messages_Conversation ADD COLUMN Type_Evenement VARCHAR(30) NULL',
    'DO 0');
PREPARE stmt FROM @add_type_evt; EXECUTE stmt; DEALLOCATE PREPARE stmt;
