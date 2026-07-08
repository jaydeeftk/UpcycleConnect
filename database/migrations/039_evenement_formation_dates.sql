CREATE TABLE IF NOT EXISTS Evenement_Dates(
   Id_Evenement_Dates INT AUTO_INCREMENT,
   Id_Evenements INT NOT NULL,
   Date_session DATETIME NOT NULL,
   PRIMARY KEY(Id_Evenement_Dates),
   FOREIGN KEY(Id_Evenements) REFERENCES Evenements(Id_Evenements) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Formation_Dates(
   Id_Formation_Dates INT AUTO_INCREMENT,
   Id_Formations INT NOT NULL,
   Date_session DATETIME NOT NULL,
   PRIMARY KEY(Id_Formation_Dates),
   FOREIGN KEY(Id_Formations) REFERENCES Formations(Id_Formations) ON DELETE CASCADE
);

INSERT INTO Evenement_Dates (Id_Evenements, Date_session)
SELECT e.Id_Evenements, e.Date_
FROM Evenements e
LEFT JOIN Evenement_Dates ed ON ed.Id_Evenements = e.Id_Evenements
WHERE ed.Id_Evenements IS NULL AND e.Date_ IS NOT NULL;

INSERT INTO Formation_Dates (Id_Formations, Date_session)
SELECT f.Id_Formations, f.Date_formation
FROM Formations f
LEFT JOIN Formation_Dates fd ON fd.Id_Formations = f.Id_Formations
WHERE fd.Id_Formations IS NULL AND f.Date_formation IS NOT NULL;
