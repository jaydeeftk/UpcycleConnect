-- 009_statut_projets.sql
-- Phase 4 — Vertical projet upcycling : vocabulaire de statut borné (CHECK).
--
-- Projets.Statut était un VARCHAR(50) libre (en pratique toujours 'en_cours' par
-- défaut). On le contraint au vocabulaire canonique dérivé du domaine
-- (api/internal/domain/projet.go) : 'en_cours', 'pause' ou 'termine'. La base
-- devient la dernière ligne de défense : aucune valeur hors-enum ne peut être
-- écrite, même par un handler bogué. Les transitions (suspendre / reprendre /
-- terminer / rouvrir) sont déjà gardées côté service ; ce CHECK borne les
-- écritures directes.
--
-- IMPORTANT (prod) : un CHECK posé sur une ligne hors-enum échouerait. Sur une
-- base reconstruite depuis init.sql, les projets seedés respectent l'enum (sûr).
-- Sur la base de PRODUCTION, les valeurs distinctes réelles de Projets.Statut
-- doivent être vérifiées AVANT application (requiert une lecture approuvée) : le
-- formulaire front propose {en_cours, pause, termine}, mais d'éventuelles valeurs
-- héritées sont « inconnues » à ce stade — aucune normalisation n'est inventée ici.
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
CALL _mig_add_check('Projets', 'chk_projets_statut', "Statut IS NULL OR Statut IN ('en_cours','pause','termine')");
DROP PROCEDURE IF EXISTS _mig_add_check;
