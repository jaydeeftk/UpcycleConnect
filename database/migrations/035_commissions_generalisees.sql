SET @annonces_not_null := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commissions'
      AND COLUMN_NAME = 'Id_Annonces' AND IS_NULLABLE = 'NO'
);
SET @modify_stmt := IF(@annonces_not_null > 0,
    'ALTER TABLE Commissions MODIFY Id_Annonces INT NULL',
    'DO 0');
PREPARE stmt FROM @modify_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_devis := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commissions' AND COLUMN_NAME = 'Id_Devis') = 0,
    'ALTER TABLE Commissions ADD COLUMN Id_Devis INT NULL',
    'DO 0');
PREPARE stmt FROM @add_devis; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_fk_devis := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Commissions' AND CONSTRAINT_NAME = 'fk_commission_devis') = 0,
    'ALTER TABLE Commissions ADD CONSTRAINT fk_commission_devis FOREIGN KEY (Id_Devis) REFERENCES Devis(Id_Devis)',
    'DO 0');
PREPARE stmt FROM @add_fk_devis; EXECUTE stmt; DEALLOCATE PREPARE stmt;
