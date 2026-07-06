-- Migration 029 : détails supplémentaires pour les formations (objectifs,
-- prérequis, programme). Affichés dans la fiche formation côté salariés.
-- Idempotente.

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Objectifs')=0,
    'ALTER TABLE Formations ADD COLUMN Objectifs TEXT NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Prerequis')=0,
    'ALTER TABLE Formations ADD COLUMN Prerequis TEXT NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Formations' AND COLUMN_NAME='Programme')=0,
    'ALTER TABLE Formations ADD COLUMN Programme TEXT NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
