
ALTER TABLE Annonces MODIFY COLUMN Id_Particuliers INT NULL;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND COLUMN_NAME='Id_Professionnels')=0,
    "ALTER TABLE Annonces ADD COLUMN Id_Professionnels INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND CONSTRAINT_NAME='fk_annonces_professionnel')=0,
    "ALTER TABLE Annonces ADD CONSTRAINT fk_annonces_professionnel FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Annonces' AND CONSTRAINT_NAME='chk_annonces_proprietaire')=0,
    "ALTER TABLE Annonces ADD CONSTRAINT chk_annonces_proprietaire CHECK ((Id_Particuliers IS NOT NULL AND Id_Professionnels IS NULL) OR (Id_Particuliers IS NULL AND Id_Professionnels IS NOT NULL))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
