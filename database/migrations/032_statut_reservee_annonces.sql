SET @deja_ok := (
    SELECT COUNT(*) FROM information_schema.CHECK_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'chk_annonces_statut'
      AND CHECK_CLAUSE LIKE '%reservee%'
);

SET @drop_stmt := IF(@deja_ok = 0, 'ALTER TABLE Annonces DROP CHECK chk_annonces_statut', 'DO 0');
PREPARE stmt FROM @drop_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_stmt := IF(@deja_ok = 0,
    'ALTER TABLE Annonces ADD CONSTRAINT chk_annonces_statut CHECK (Statut IN (\'en_attente\',\'validee\',\'refusee\',\'retiree\',\'vendue\',\'reservee\'))',
    'DO 0');
PREPARE stmt FROM @add_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;
