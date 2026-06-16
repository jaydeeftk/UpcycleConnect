-- Migration 019 : facturation pro (item 1, ref descriptif 3.1).
-- Contrats.Montant + Frequence (abo mensuel vs campagne pub), Abonnement lie
-- au pro, seed du pro demo avec un abonnement Premium 20e/mois + une campagne
-- pub 250e + une commission 7,5%, pour que l'agregat de facturation cote pro
-- soit non vide (et reflete les montants reels du descriptif). Idempotente
-- (gardes information_schema + checks SELECT...EXISTS sur le seed).

-- 1) Contrats : Montant + Frequence
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Montant')=0,
    "ALTER TABLE Contrats ADD COLUMN Montant DECIMAL(10,2) NOT NULL DEFAULT 0",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Frequence')=0,
    "ALTER TABLE Contrats ADD COLUMN Frequence VARCHAR(20) NOT NULL DEFAULT 'mensuel'",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND CONSTRAINT_NAME='chk_contrats_frequence')=0,
    "ALTER TABLE Contrats ADD CONSTRAINT chk_contrats_frequence CHECK (Frequence IN ('mensuel','campagne','unique'))",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2) Abonnement : rattachement au pro
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Abonnement' AND COLUMN_NAME='Id_Professionnels')=0,
    "ALTER TABLE Abonnement ADD COLUMN Id_Professionnels INT NULL",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Abonnement' AND CONSTRAINT_NAME='fk_abonnement_professionnel')=0,
    "ALTER TABLE Abonnement ADD CONSTRAINT fk_abonnement_professionnel FOREIGN KEY (Id_Professionnels) REFERENCES Professionnels_artisans(Id_Professionnels)",'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3) Seed du pro demo (Id_Professionnels=1) pour rendre la facturation non vide.
-- INSERT IGNORE n'aiderait pas ici (pas de cle unique sur Type), on garde
-- l'idempotence via SELECT EXISTS sur Type+Statut+Id_Professionnels.
INSERT INTO Contrats (Date_signature, Date_debut, Date_fin, Type, Montant, Frequence, Statut, Id_Professionnels)
SELECT NOW(), '2026-01-01', '2026-12-31', 'Premium', 20.00, 'mensuel', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Contrats WHERE Type='Premium' AND Statut='actif' AND Id_Professionnels=1);

INSERT INTO Contrats (Date_signature, Date_debut, Date_fin, Type, Montant, Frequence, Statut, Id_Professionnels)
SELECT NOW(), '2026-03-01', '2026-06-30', 'Campagne publicitaire', 250.00, 'campagne', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Contrats WHERE Type='Campagne publicitaire' AND Statut='actif' AND Id_Professionnels=1);

INSERT INTO Abonnement (Id_Abonnement, Type, Prix, Date_Debut, Date_Fin, Statut, Id_Professionnels)
SELECT 'ABO-PRO-PREMIUM-DEMO', 'Premium', 20.00, '2026-01-01', '2026-12-31', 'actif', 1
WHERE NOT EXISTS (SELECT 1 FROM Abonnement WHERE Id_Abonnement='ABO-PRO-PREMIUM-DEMO');

-- Commission demo : 7,5% sur une facture existante du pro, si elle existe ;
-- sinon une commission orpheline (Id_Facture=0 invalide a cause de la FK NOT
-- NULL) — on utilise donc une facture existante quelconque ou on saute.
INSERT INTO Commissions (Taux, Montant, Date_, Id_Annonces, Id_Facture)
SELECT 7.50, 18.75, NOW(),
    (SELECT MIN(Id_Annonces) FROM Annonces),
    (SELECT MIN(Id_Facture) FROM Factures)
WHERE EXISTS (SELECT 1 FROM Annonces) AND EXISTS (SELECT 1 FROM Factures)
  AND NOT EXISTS (SELECT 1 FROM Commissions WHERE Taux=7.50 AND Montant=18.75);
