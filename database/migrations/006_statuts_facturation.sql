-- 006_statuts_facturation.sql
-- Phase 4 — Vertical facturation : vocabulaires de statut/méthode bornés (CHECK).
--
-- Factures.Statut, Paiements.Statut, Paiements.Methode et Abonnement.Statut
-- étaient des VARCHAR libres. On les contraint au vocabulaire canonique dérivé
-- du domaine (api/internal/domain/facturation.go). La base devient la dernière
-- ligne de défense : aucune valeur hors-enum ne peut être écrite, même si un
-- handler bogué l'essaie. Les transitions (contrat/abonnement) sont déjà gardées
-- côté service ; ce CHECK borne en plus les écritures directes de facture/paiement.
--
-- IMPORTANT (prod) : un CHECK posé sur une ligne hors-enum échouerait. Sur une
-- base reconstruite depuis init.sql ces tables sont VIDES (aucun seed) : sûr.
-- Sur la base de PRODUCTION, les valeurs distinctes réelles de Paiements.Statut,
-- Paiements.Methode et Abonnement.Statut doivent être vérifiées AVANT application
-- (requiert une lecture approuvée). À ce jour ces valeurs sont « inconnues » côté
-- prod : aucune normalisation n'est inventée ici (on ne corrige pas à l'aveugle).
--
-- NULL toléré : un CHECK MySQL passe quand l'expression vaut TRUE ou UNKNOWN ;
-- les colonnes nullables (Paiements.*, Abonnement.Statut) acceptent donc NULL.
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
CALL _mig_add_check('Factures',   'chk_factures_statut',   "Statut IN ('brouillon','emise','payee','annulee')");
CALL _mig_add_check('Paiements',  'chk_paiements_statut',  "Statut IS NULL OR Statut IN ('en_attente','paye','echoue','rembourse')");
CALL _mig_add_check('Paiements',  'chk_paiements_methode', "Methode IS NULL OR Methode IN ('carte','virement','especes','cheque')");
CALL _mig_add_check('Abonnement', 'chk_abonnement_statut', "Statut IS NULL OR Statut IN ('actif','suspendu','resilie','expire')");
DROP PROCEDURE IF EXISTS _mig_add_check;
