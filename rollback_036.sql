SET @drop_fk_objet_pro := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Objets' AND CONSTRAINT_NAME = 'fk_objet_pro_depositaire') > 0,
    'ALTER TABLE Objets DROP FOREIGN KEY fk_objet_pro_depositaire',
    'DO 0');
PREPARE stmt FROM @drop_fk_objet_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @drop_col_objet_pro := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Objets' AND COLUMN_NAME = 'Id_Professionnels_Depositaire') > 0,
    'ALTER TABLE Objets DROP COLUMN Id_Professionnels_Depositaire',
    'DO 0');
PREPARE stmt FROM @drop_col_objet_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @drop_fk_demande_pro := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_conteneurs' AND CONSTRAINT_NAME = 'fk_demande_pro_depositaire') > 0,
    'ALTER TABLE Demandes_conteneurs DROP FOREIGN KEY fk_demande_pro_depositaire',
    'DO 0');
PREPARE stmt FROM @drop_fk_demande_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @drop_col_demande_pro := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Demandes_conteneurs' AND COLUMN_NAME = 'Id_Professionnels_Depositaire') > 0,
    'ALTER TABLE Demandes_conteneurs DROP COLUMN Id_Professionnels_Depositaire',
    'DO 0');
PREPARE stmt FROM @drop_col_demande_pro; EXECUTE stmt; DEALLOCATE PREPARE stmt;
