-- Migration 017 : Statut_validation (en_attente/valide/refuse) + Motif_refus
-- sur Evenements, Formations, Atelier. Dissocie l'etat de moderation du Statut
-- de cycle de vie (brouillon/a_venir/...). Idempotente (gardes information_schema),
-- rejouable sans effet de bord. A appliquer avec --default-character-set=utf8mb4.

-- 1) Evenements : ajout colonne + check + motif_refus
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Evenements ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Evenements ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND CONSTRAINT_NAME='chk_evenements_statut_validation')=0,
    "ALTER TABLE Evenements ADD CONSTRAINT chk_evenements_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2) Formations : meme chose
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Formations ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Formations ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND CONSTRAINT_NAME='chk_formations_statut_validation')=0,
    "ALTER TABLE Formations ADD CONSTRAINT chk_formations_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3) Atelier : meme chose
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Atelier ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Atelier ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND CONSTRAINT_NAME='chk_atelier_statut_validation')=0,
    "ALTER TABLE Atelier ADD CONSTRAINT chk_atelier_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 4) Backfill : les lignes deja publiees passent a 'valide' (idempotent : ne
-- modifie que les lignes encore 'en_attente' qui matchent un cycle de vie publie).
UPDATE Evenements SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut IN ('a_venir','en_cours','termine');
UPDATE Formations SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut='actif';
UPDATE Atelier SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut IN ('actif','a_venir');
