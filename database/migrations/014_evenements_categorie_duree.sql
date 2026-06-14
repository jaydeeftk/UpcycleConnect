-- Migration 014 : ajoute les colonnes Categorie et Duree a la table Evenements.
-- Categorie permet de filtrer les evenements dans le catalogue (atelier, marche,
-- conference, exposition, communautaire) ; Duree (en heures) permet d'afficher
-- la plage horaire de l'evenement dans le planning personnel.

ALTER TABLE Evenements ADD COLUMN Categorie VARCHAR(50) DEFAULT 'atelier';
ALTER TABLE Evenements ADD COLUMN Duree INT DEFAULT 2;

ALTER TABLE Evenements ADD CONSTRAINT chk_evenements_categorie
    CHECK (Categorie IN ('atelier','marche','conference','exposition','communautaire'));

-- Categorisation des evenements du seed initial
UPDATE Evenements SET Categorie = 'atelier',  Duree = 3 WHERE Titre = 'Atelier Upcycling Textile';
UPDATE Evenements SET Categorie = 'atelier',  Duree = 4 WHERE Titre = 'Workshop Mobilier Recyclé';
UPDATE Evenements SET Categorie = 'communautaire', Duree = 6 WHERE Titre = 'Journée Zéro Déchet';
