
SET @add_anim_formations := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Formations' AND COLUMN_NAME = 'Id_Salarie_Animateur') = 0,
    'ALTER TABLE Formations ADD COLUMN Id_Salarie_Animateur INT NULL AFTER Id_Salaries',
    'DO 0');
PREPARE stmt FROM @add_anim_formations; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_anim_evenements := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Evenements' AND COLUMN_NAME = 'Id_Salarie_Animateur') = 0,
    'ALTER TABLE Evenements ADD COLUMN Id_Salarie_Animateur INT NULL AFTER Id_Salaries',
    'DO 0');
PREPARE stmt FROM @add_anim_evenements; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_anim_atelier := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Atelier' AND COLUMN_NAME = 'Id_Salarie_Animateur') = 0,
    'ALTER TABLE Atelier ADD COLUMN Id_Salarie_Animateur INT NULL AFTER Id_Salaries',
    'DO 0');
PREPARE stmt FROM @add_anim_atelier; EXECUTE stmt; DEALLOCATE PREPARE stmt;
