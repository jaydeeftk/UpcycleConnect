
DROP PROCEDURE IF EXISTS _mig_add_check;
DELIMITER //
CREATE PROCEDURE _mig_add_check(IN tbl VARCHAR(64), IN cname VARCHAR(64), IN expr TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl
                        AND CONSTRAINT_NAME = cname AND CONSTRAINT_TYPE = 'CHECK') THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD CONSTRAINT `', cname, '` CHECK (', expr, ')');
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_check('Codes_Barres', 'chk_codes_barres_statut', "Statut IS NULL OR Statut IN ('active','utilise')");
DROP PROCEDURE IF EXISTS _mig_add_check;
