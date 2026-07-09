
DROP PROCEDURE IF EXISTS _mig_add_col;
DELIMITER //
CREATE PROCEDURE _mig_add_col(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col) THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_col('Sujets',   'Id_Utilisateurs', 'Id_Utilisateurs INT NULL');
CALL _mig_add_col('Reponses', 'Id_Utilisateurs', 'Id_Utilisateurs INT NULL');
CALL _mig_add_col('Reponses', 'Est_Solution',    'Est_Solution TINYINT(1) NOT NULL DEFAULT 0');
DROP PROCEDURE IF EXISTS _mig_add_col;

UPDATE Sujets s
   JOIN Particuliers p ON p.Id_Particuliers = s.Id_Particuliers
   SET s.Id_Utilisateurs = p.Id_Utilisateurs
   WHERE s.Id_Utilisateurs IS NULL;

UPDATE Reponses r
   JOIN Professionnels_artisans pa ON pa.Id_Professionnels = r.Id_Professionnels
   SET r.Id_Utilisateurs = pa.Id_Utilisateurs
   WHERE r.Id_Utilisateurs IS NULL AND r.Id_Professionnels IS NOT NULL;

DROP PROCEDURE IF EXISTS _mig_relax_null;
DELIMITER //
CREATE PROCEDURE _mig_relax_null(IN tbl VARCHAR(64), IN col VARCHAR(64), IN coltype VARCHAR(64))
BEGIN
   IF EXISTS (SELECT 1 FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl
                    AND COLUMN_NAME = col AND IS_NULLABLE = 'NO') THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` MODIFY COLUMN `', col, '` ', coltype, ' NULL');
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_relax_null('Sujets', 'Id_Particuliers', 'INT');
DROP PROCEDURE IF EXISTS _mig_relax_null;

DROP PROCEDURE IF EXISTS _mig_add_fk;
DELIMITER //
CREATE PROCEDURE _mig_add_fk(IN tbl VARCHAR(64), IN fkname VARCHAR(64), IN ddl TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl
                        AND CONSTRAINT_NAME = fkname AND CONSTRAINT_TYPE = 'FOREIGN KEY') THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD CONSTRAINT `', fkname, '` ', ddl);
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_fk('Sujets',   'fk_sujets_utilisateur',   'FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)');
CALL _mig_add_fk('Reponses', 'fk_reponses_utilisateur', 'FOREIGN KEY (Id_Utilisateurs) REFERENCES Utilisateurs(Id_Utilisateurs)');
DROP PROCEDURE IF EXISTS _mig_add_fk;
