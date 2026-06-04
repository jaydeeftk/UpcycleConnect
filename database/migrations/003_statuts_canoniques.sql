-- 003_statuts_canoniques.sql
-- Phase 3 — Schéma & intégrité : vocabulaires de statut bornés (CHECK).
--
-- Les statuts étaient des VARCHAR libres : on les contraint à un vocabulaire
-- canonique (dérivé des machines à états de la Phase 2). La base devient la
-- dernière ligne de défense : aucune valeur hors-enum ne peut être écrite,
-- même si un handler bogué l'essaie.
--
-- IMPORTANT (prod) : la NORMALISATION doit précéder le CHECK. Sur une base
-- reconstruite depuis init.sql les valeurs sont connues (seeds 'a_venir',
-- 'actif', ...). Sur la base de PRODUCTION, les valeurs distinctes réelles
-- doivent être vérifiées AVANT application (requiert une lecture approuvée) :
-- un CHECK posé sur une ligne hors-enum échouerait. Les UPDATE ci-dessous
-- couvrent les divergences connues (legacy 'active').
--
-- Idempotent : ré-exécutable sans erreur (gardes information_schema).

-- --- Contrats : ajout de la colonne Statut (cycle de vie absent jusqu'ici) ----
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

-- --- Normalisation des divergences connues -----------------------------------
UPDATE Annonces SET Statut = 'validee'    WHERE Statut = 'active';
UPDATE Annonces SET Statut = 'en_attente' WHERE Statut IS NULL OR Statut = '';

-- --- CHECK idempotents --------------------------------------------------------
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
