-- 002_unicite_codes.sql
-- Phase 3 — Schéma & intégrité : unicité des codes d'accès et codes-barres.
--
-- Demandes_conteneurs.Code_acces et Codes_Barres.Code doivent être uniques :
-- la base est la dernière ligne de défense contre une collision (le service
-- génère + ré-essaie en cas de doublon, mais c'est l'index UNIQUE qui garantit).
--
-- Code_acces est NULL tant que la demande n'est pas validée. Sous UNIQUE, MySQL
-- autorise plusieurs NULL : les demandes en attente ne se gênent pas. On normalise
-- d'abord les éventuelles chaînes vides en NULL pour ne pas violer l'unicité.
--
-- Idempotent : ré-exécutable sans erreur (gardes information_schema).

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
