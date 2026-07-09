
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Box' AND COLUMN_NAME='Taille')=0,
    "ALTER TABLE Box ADD COLUMN Taille VARCHAR(20) NOT NULL DEFAULT 'standard'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Box' AND CONSTRAINT_NAME='chk_box_taille')=0,
    "ALTER TABLE Box ADD CONSTRAINT chk_box_taille CHECK (Taille IN ('standard','encombrant'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Demandes_conteneurs' AND COLUMN_NAME='Id_Box')=0,
    "ALTER TABLE Demandes_conteneurs ADD COLUMN Id_Box INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Demandes_conteneurs' AND CONSTRAINT_NAME='fk_demandes_box')=0,
    "ALTER TABLE Demandes_conteneurs ADD CONSTRAINT fk_demandes_box FOREIGN KEY (Id_Box) REFERENCES Box(Id_Box)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Codes_Barres' AND COLUMN_NAME='Id_Box')=0,
    "ALTER TABLE Codes_Barres ADD COLUMN Id_Box INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Codes_Barres' AND CONSTRAINT_NAME='fk_codes_box')=0,
    "ALTER TABLE Codes_Barres ADD CONSTRAINT fk_codes_box FOREIGN KEY (Id_Box) REFERENCES Box(Id_Box)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs, Taille)
SELECT CONCAT('UB-C', c.Id_Conteneurs, '-T', LPAD(n.i, 2, '0')), 1, 'disponible', c.Id_Conteneurs,
       CASE WHEN n.i <= 4 THEN 'standard' ELSE 'encombrant' END
FROM Conteneurs c
JOIN (SELECT 1 AS i UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
      UNION ALL SELECT 5 UNION ALL SELECT 6) n
WHERE NOT EXISTS (
    SELECT 1 FROM Box b
    WHERE b.Reference = CONCAT('UB-C', c.Id_Conteneurs, '-T', LPAD(n.i, 2, '0'))
);
