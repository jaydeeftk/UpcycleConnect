SET @add_ref_abonnement := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND COLUMN_NAME = 'Reference_Stripe') = 0,
    'ALTER TABLE Abonnement ADD COLUMN Reference_Stripe VARCHAR(255) NULL',
    'DO 0');
PREPARE stmt FROM @add_ref_abonnement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_uniq_ref_abonnement := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND INDEX_NAME = 'uniq_abonnement_reference_stripe') = 0,
    'ALTER TABLE Abonnement ADD CONSTRAINT uniq_abonnement_reference_stripe UNIQUE (Reference_Stripe)',
    'DO 0');
PREPARE stmt FROM @add_uniq_ref_abonnement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_ref_publicites := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Publicites' AND COLUMN_NAME = 'Reference_Stripe') = 0,
    'ALTER TABLE Publicites ADD COLUMN Reference_Stripe VARCHAR(255) NULL',
    'DO 0');
PREPARE stmt FROM @add_ref_publicites; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_uniq_ref_publicites := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Publicites' AND INDEX_NAME = 'uniq_publicites_reference_stripe') = 0,
    'ALTER TABLE Publicites ADD CONSTRAINT uniq_publicites_reference_stripe UNIQUE (Reference_Stripe)',
    'DO 0');
PREPARE stmt FROM @add_uniq_ref_publicites; EXECUTE stmt; DEALLOCATE PREPARE stmt;
