-- Migration 018 : enrichit Planning_personnel pour permettre les entrees libres
-- (titre, lieu, description) en plus du seul intervalle Date_debut/Date_fin.
-- Idempotente (gardes information_schema). Item 13 (action manuelle "Ajouter
-- au planning"). La table reste FK vers Particuliers (mode personnel only).

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Planning_personnel' AND COLUMN_NAME='Titre')=0,
    "ALTER TABLE Planning_personnel ADD COLUMN Titre VARCHAR(150) NOT NULL DEFAULT ''",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Planning_personnel' AND COLUMN_NAME='Lieu')=0,
    "ALTER TABLE Planning_personnel ADD COLUMN Lieu VARCHAR(150) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Planning_personnel' AND COLUMN_NAME='Description')=0,
    "ALTER TABLE Planning_personnel ADD COLUMN Description VARCHAR(255) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
