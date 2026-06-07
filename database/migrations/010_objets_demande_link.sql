-- 010 — relie l'objet matérialisé à la demande de dépôt d'origine
-- Permet d'afficher le code-barres (QR) de l'objet sur la demande validée du particulier.
ALTER TABLE Objets ADD COLUMN Id_Demandes_conteneurs INT NULL;
ALTER TABLE Objets ADD CONSTRAINT fk_objets_demande
    FOREIGN KEY (Id_Demandes_conteneurs) REFERENCES Demandes_conteneurs(Id_Demandes_conteneurs);
