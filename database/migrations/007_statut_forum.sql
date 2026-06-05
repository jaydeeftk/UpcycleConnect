-- 007_statut_forum.sql
-- Phase 4 — Vertical forum : vocabulaire de statut borné (CHECK).
--
-- Sujets.Statut était un VARCHAR libre (DEFAULT 'ouvert'). On le contraint au
-- vocabulaire canonique dérivé du domaine (api/internal/domain/forum.go) :
-- 'ouvert', 'resolu', 'ferme'. La base devient la dernière ligne de défense :
-- aucune valeur hors-enum ne peut être écrite, même par un handler bogué. Les
-- transitions (marquage de solution, modération) sont déjà gardées côté service ;
-- ce CHECK borne en plus les écritures directes.
--
-- IMPORTANT (prod) : un CHECK posé sur une ligne hors-enum échouerait. Sur une
-- base reconstruite depuis init.sql la table est sans seed (sûr). Sur la base de
-- PRODUCTION, les valeurs distinctes réelles de Sujets.Statut doivent être
-- vérifiées AVANT application (requiert une lecture approuvée). À ce jour ces
-- valeurs sont « inconnues » côté prod : aucune normalisation n'est inventée ici.
--
-- NULL toléré : un CHECK MySQL passe quand l'expression vaut TRUE ou UNKNOWN ;
-- la colonne nullable accepte donc NULL.
--
-- Idempotent : ré-exécutable sans erreur (garde information_schema).

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
CALL _mig_add_check('Sujets', 'chk_sujets_statut', "Statut IS NULL OR Statut IN ('ouvert','resolu','ferme')");
DROP PROCEDURE IF EXISTS _mig_add_check;
