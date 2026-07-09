
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
CALL _mig_add_col('Contrats', 'Statut', "Statut VARCHAR(50) NOT NULL DEFAULT 'actif'");
DROP PROCEDURE IF EXISTS _mig_add_col;

UPDATE Annonces SET Statut = 'validee'    WHERE Statut = 'active';
UPDATE Annonces SET Statut = 'en_attente' WHERE Statut IS NULL OR Statut = '';

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
CALL _mig_add_check('Annonces',            'chk_annonces_statut',  "Statut IN ('en_attente','validee','refusee','retiree','vendue')");
CALL _mig_add_check('Evenements',          'chk_evenements_statut', "Statut IN ('brouillon','a_venir','en_cours','termine','annule')");
CALL _mig_add_check('Formations',          'chk_formations_statut', "Statut IN ('en_attente','actif','rejete','cloturee')");
CALL _mig_add_check('Contrats',            'chk_contrats_statut',   "Statut IN ('brouillon','actif','suspendu','resilie','expire')");
CALL _mig_add_check('Demandes_conteneurs', 'chk_demandes_statut',   "Statut IN ('en_attente','validee','refusee','deposee')");
CALL _mig_add_check('Objets',              'chk_objets_statut',     "Statut IS NULL OR Statut IN ('en_stock','reserve_pro','recupere')");
DROP PROCEDURE IF EXISTS _mig_add_check;
