
UPDATE Demandes_conteneurs SET Code_acces = NULL WHERE Code_acces = '';

DROP PROCEDURE IF EXISTS _mig_add_unique;
DELIMITER //
CREATE PROCEDURE _mig_add_unique(IN tbl VARCHAR(64), IN idxname VARCHAR(64), IN cols TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idxname) THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD UNIQUE KEY `', idxname, '` (', cols, ')');
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_unique('Demandes_conteneurs', 'uq_demande_code_acces', 'Code_acces');
CALL _mig_add_unique('Codes_Barres', 'uq_codebarres_code', 'Code');
DROP PROCEDURE IF EXISTS _mig_add_unique;
