-- Migration 020 : etend Medias pour porter les photos d'etapes de projets pro
-- (item 3, descriptif 3.4 'mise en avant des projets', avant/apres). Pas de
-- nouvelle table : on garde Medias et on relache Id_Annonces (nullable) pour
-- accueillir aussi des medias rattaches a une Etape. Idempotente (gardes
-- information_schema). A appliquer avec --default-character-set=utf8mb4.

-- 1) Nouvelles colonnes
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND COLUMN_NAME='Id_Etapes')=0,
    "ALTER TABLE Medias ADD COLUMN Id_Etapes INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND COLUMN_NAME='Type_photo')=0,
    "ALTER TABLE Medias ADD COLUMN Type_photo VARCHAR(10) NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2) Id_Annonces devient nullable (un media peut maintenant pointer une Etape SEULE)
SET @s := IF((SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND COLUMN_NAME='Id_Annonces')='NO',
    "ALTER TABLE Medias MODIFY Id_Annonces INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3) Contraintes : FK Etapes + CHECK Type_photo
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND CONSTRAINT_NAME='fk_medias_etapes')=0,
    "ALTER TABLE Medias ADD CONSTRAINT fk_medias_etapes FOREIGN KEY (Id_Etapes) REFERENCES Etapes(Id_Etapes)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND CONSTRAINT_NAME='chk_medias_type_photo')=0,
    "ALTER TABLE Medias ADD CONSTRAINT chk_medias_type_photo CHECK (Type_photo IS NULL OR Type_photo IN ('avant','apres'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 4) Garde-fou : un media DOIT pointer soit une Annonce, soit une Etape, jamais
-- les deux NULL en meme temps.
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Medias' AND CONSTRAINT_NAME='chk_medias_cible')=0,
    "ALTER TABLE Medias ADD CONSTRAINT chk_medias_cible CHECK (Id_Annonces IS NOT NULL OR Id_Etapes IS NOT NULL)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
