-- Migration 028 : ajout des dimensions physiques (hauteur, largeur, longueur en cm)
-- sur la table Box (casiers UpcycleBox). Idempotente.

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Box' AND COLUMN_NAME='Hauteur_cm')=0,
    'ALTER TABLE Box ADD COLUMN Hauteur_cm DECIMAL(6,1) NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Box' AND COLUMN_NAME='Largeur_cm')=0,
    'ALTER TABLE Box ADD COLUMN Largeur_cm DECIMAL(6,1) NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Box' AND COLUMN_NAME='Longueur_cm')=0,
    'ALTER TABLE Box ADD COLUMN Longueur_cm DECIMAL(6,1) NULL',"DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
