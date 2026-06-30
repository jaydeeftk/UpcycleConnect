-- Migration 023 : annonces ouvertes aux professionnels.
-- Jusqu'ici Annonces.Id_Particuliers etait NOT NULL avec FK stricte vers
-- Particuliers : un compte professionnel ne pouvait pas deposer/gerer
-- d'annonce (resoudreParticulier renvoyait 403 "Action reservee aux
-- particuliers"). On ajoute Id_Professionnels (nullable) en parallele et on
-- rend Id_Particuliers nullable, avec un CHECK garantissant qu'une annonce
-- appartient a exactement un proprietaire (particulier OU professionnel).
-- Idempotente (gardes information_schema), suit le style des migrations 003/019.

-- 1) Id_Particuliers devient nullable (necessaire pour les annonces deposees
--    par un professionnel, qui n'a pas de ligne dans Particuliers).
ALTER TABLE Annonces MODIFY COLUMN Id_Particuliers INT NULL;

-- 2) Ajout de la colonne Id_Professionnels + FK vers Professionnels_artisans.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND COLUMN_NAME='Id_Professionnels')=0,
    "ALTER TABLE Annonces ADD COLUMN Id_Professionnels INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND CONSTRAINT_NAME='fk_annonces_professionnel')=0,
    "ALTER TABLE Annonces ADD CONSTRAINT fk_annonces_professionnel FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3) Exactement un proprietaire (particulier OU professionnel), jamais les
--    deux, jamais aucun.
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND CONSTRAINT_NAME='chk_annonces_proprietaire')=0,
    "ALTER TABLE Annonces ADD CONSTRAINT chk_annonces_proprietaire CHECK ((Id_Particuliers IS NOT NULL AND Id_Professionnels IS NULL) OR (Id_Particuliers IS NULL AND Id_Professionnels IS NOT NULL))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
