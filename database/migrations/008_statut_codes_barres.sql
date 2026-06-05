-- 008_statut_codes_barres.sql
-- Phase 4 — Vertical code-barres : vocabulaire de statut borné (CHECK).
--
-- Codes_Barres.Statut était un VARCHAR libre. On le contraint au vocabulaire
-- canonique dérivé du domaine (api/internal/domain/codebarres.go) : 'active'
-- (l'objet désigné est récupérable) ou 'utilise' (code consommé au moment de la
-- récupération, état terminal). La base devient la dernière ligne de défense :
-- aucune valeur hors-enum ne peut être écrite, même par un handler bogué. Les
-- transitions (génération à la matérialisation de l'objet, consommation à la
-- récupération) sont déjà gardées côté service ; ce CHECK borne les écritures
-- directes.
--
-- IMPORTANT (prod) : un CHECK posé sur une ligne hors-enum échouerait. Sur une
-- base reconstruite depuis init.sql la table est sans seed (sûr). Sur la base de
-- PRODUCTION, les valeurs distinctes réelles de Codes_Barres.Statut doivent être
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
CALL _mig_add_check('Codes_Barres', 'chk_codes_barres_statut', "Statut IS NULL OR Statut IN ('active','utilise')");
DROP PROCEDURE IF EXISTS _mig_add_check;
