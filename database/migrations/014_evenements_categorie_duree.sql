-- Migration 014 : ajoute les colonnes Categorie et Duree a la table Evenements.
-- Categorie permet de filtrer les evenements dans le catalogue (atelier, marche,
-- conference, exposition, communautaire) ; Duree (en heures) permet d'afficher
-- la plage horaire de l'evenement dans le planning personnel.
-- Idempotente : rejouable sans erreur (colonnes/contrainte gardees par information_schema).
-- A appliquer avec --default-character-set=utf8mb4 (titres accentues).

SET @add_categorie := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Evenements' AND COLUMN_NAME = 'Categorie') = 0,
    'ALTER TABLE Evenements ADD COLUMN Categorie VARCHAR(50) DEFAULT ''atelier''',
    'DO 0');
PREPARE stmt FROM @add_categorie; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_duree := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Evenements' AND COLUMN_NAME = 'Duree') = 0,
    'ALTER TABLE Evenements ADD COLUMN Duree INT DEFAULT 2',
    'DO 0');
PREPARE stmt FROM @add_duree; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_check := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Evenements' AND CONSTRAINT_NAME = 'chk_evenements_categorie') = 0,
    'ALTER TABLE Evenements ADD CONSTRAINT chk_evenements_categorie CHECK (Categorie IN (''atelier'',''marche'',''conference'',''exposition'',''communautaire''))',
    'DO 0');
PREPARE stmt FROM @add_check; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Categorisation des evenements du seed initial (UPDATE rejouable)
UPDATE Evenements SET Categorie = 'atelier',       Duree = 3 WHERE Titre = 'Atelier Upcycling Textile';
UPDATE Evenements SET Categorie = 'atelier',       Duree = 4 WHERE Titre = 'Workshop Mobilier Recyclé';
UPDATE Evenements SET Categorie = 'communautaire', Duree = 6 WHERE Titre = 'Journée Zéro Déchet';
