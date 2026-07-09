
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Montant')=0,
    "ALTER TABLE Contrats ADD COLUMN Montant DECIMAL(10,2) NOT NULL DEFAULT 0",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Frequence')=0,
    "ALTER TABLE Contrats ADD COLUMN Frequence VARCHAR(20) NOT NULL DEFAULT 'mensuel'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND CONSTRAINT_NAME='chk_contrats_frequence')=0,
    "ALTER TABLE Contrats ADD CONSTRAINT chk_contrats_frequence CHECK (Frequence IN ('mensuel','campagne','unique'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Abonnement' AND COLUMN_NAME='Id_Professionnels')=0,
    "ALTER TABLE Abonnement ADD COLUMN Id_Professionnels INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Abonnement' AND CONSTRAINT_NAME='fk_abonnement_professionnel')=0,
    "ALTER TABLE Abonnement ADD CONSTRAINT fk_abonnement_professionnel FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

INSERT INTO Contrats (Date_signature, Date_debut, Date_fin, Type, Montant, Frequence, Statut, Id_Professionnels)
SELECT NOW(), '2026-01-01', '2026-12-31', 'Premium', 20.00, 'mensuel', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Contrats WHERE Type='Premium' AND Statut='actif' AND Id_Professionnels=1);

INSERT INTO Contrats (Date_signature, Date_debut, Date_fin, Type, Montant, Frequence, Statut, Id_Professionnels)
SELECT NOW(), '2026-03-01', '2026-06-30', 'Campagne publicitaire', 250.00, 'campagne', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Contrats WHERE Type='Campagne publicitaire' AND Statut='actif' AND Id_Professionnels=1);

INSERT INTO Abonnement (Id_Abonnement, Type, Prix, Date_Debut, Date_Fin, Statut, Id_Professionnels)
SELECT 'ABO-PRO-PREMIUM-DEMO', 'Premium', 20.00, '2026-01-01', '2026-12-31', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Abonnement WHERE Id_Abonnement='ABO-PRO-PREMIUM-DEMO');

INSERT INTO Commissions (Taux, Montant, Date_, Id_Annonces, Id_Facture)
SELECT 7.50, 18.75, NOW(),
    (SELECT MIN(Id_Annonces) FROM Annonces),
    (SELECT MIN(Id_Facture) FROM Factures)
WHERE EXISTS (SELECT 1 FROM Annonces) AND EXISTS (SELECT 1 FROM Factures)
  AND NOT EXISTS (SELECT 1 FROM Commissions WHERE Taux=7.50 AND Montant=18.75);
