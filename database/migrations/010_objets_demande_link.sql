ALTER TABLE Objets ADD COLUMN Id_Demandes_conteneurs INT NULL;
ALTER TABLE Objets ADD CONSTRAINT fk_objets_demande
    FOREIGN KEY (Id_Demandes_conteneurs) REFERENCES Demandes_conteneurs(Id_Demandes_conteneurs);
