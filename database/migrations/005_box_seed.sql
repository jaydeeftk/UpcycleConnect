
INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs)
SELECT CONCAT('BOX-C', c.Id_Conteneurs),
       GREATEST(COALESCE(NULLIF(CAST(c.Capacite AS UNSIGNED), 0), 1), 1),
       'disponible',
       c.Id_Conteneurs
FROM Conteneurs c
WHERE NOT EXISTS (SELECT 1 FROM Box b WHERE b.Id_Conteneurs = c.Id_Conteneurs);
