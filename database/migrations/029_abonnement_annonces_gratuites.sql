-- Migration 029 : quota d'annonces gratuites inclus dans l'abonnement
-- Premium des professionnels.

SET @add_annonces_incluses := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND COLUMN_NAME = 'Annonces_Gratuites_Incluses') = 0,
    'ALTER TABLE Abonnement ADD COLUMN Annonces_Gratuites_Incluses INT NOT NULL DEFAULT 0',
    'DO 0');
PREPARE stmt FROM @add_annonces_incluses; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_annonces_utilisees := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Abonnement' AND COLUMN_NAME = 'Annonces_Gratuites_Utilisees') = 0,
    'ALTER TABLE Abonnement ADD COLUMN Annonces_Gratuites_Utilisees INT NOT NULL DEFAULT 0',
    'DO 0');
PREPARE stmt FROM @add_annonces_utilisees; EXECUTE stmt; DEALLOCATE PREPARE stmt;
