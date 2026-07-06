-- Migration 028 : etapes d'une formation (programme pedagogique), visibles
-- sur la fiche publique et gerees par le salarie createur. Idempotente
-- (CREATE TABLE IF NOT EXISTS).

CREATE TABLE IF NOT EXISTS Formation_Etapes(
   Id_Etapes INT AUTO_INCREMENT,
   Id_Formations INT NOT NULL,
   Titre VARCHAR(150) NOT NULL,
   Description VARCHAR(500),
   Ordre INT NOT NULL DEFAULT 0,
   PRIMARY KEY(Id_Etapes),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations)
);
