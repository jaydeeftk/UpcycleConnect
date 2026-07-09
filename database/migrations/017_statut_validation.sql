
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Evenements ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Evenements ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Evenements' AND CONSTRAINT_NAME='chk_evenements_statut_validation')=0,
    "ALTER TABLE Evenements ADD CONSTRAINT chk_evenements_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Formations ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Formations ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND CONSTRAINT_NAME='chk_formations_statut_validation')=0,
    "ALTER TABLE Formations ADD CONSTRAINT chk_formations_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND COLUMN_NAME='Statut_validation')=0,
    "ALTER TABLE Atelier ADD COLUMN Statut_validation VARCHAR(20) NOT NULL DEFAULT 'en_attente'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND COLUMN_NAME='Motif_refus')=0,
    "ALTER TABLE Atelier ADD COLUMN Motif_refus VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Atelier' AND CONSTRAINT_NAME='chk_atelier_statut_validation')=0,
    "ALTER TABLE Atelier ADD CONSTRAINT chk_atelier_statut_validation CHECK (Statut_validation IN ('en_attente','valide','refuse'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

UPDATE Evenements SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut IN ('a_venir','en_cours','termine');
UPDATE Formations SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut='actif';
UPDATE Atelier SET Statut_validation='valide'
    WHERE Statut_validation='en_attente' AND Statut IN ('actif','a_venir');
