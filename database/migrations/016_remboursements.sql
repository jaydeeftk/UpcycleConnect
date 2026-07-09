
CREATE TABLE IF NOT EXISTS Demandes_remboursement (
    Id_Demande      INT AUTO_INCREMENT PRIMARY KEY,
    Id_Paiements    INT NOT NULL,
    Id_Particuliers INT NOT NULL,
    Motif           VARCHAR(255),
    Statut          VARCHAR(30) NOT NULL DEFAULT 'en_attente',
    Date_demande    DATETIME DEFAULT NULL,
    Date_traitement DATETIME DEFAULT NULL,
    FOREIGN KEY (Id_Paiements) REFERENCES Paiements(Id_Paiements),
    FOREIGN KEY (Id_Particuliers) REFERENCES Particuliers(Id_Particuliers),
    CONSTRAINT chk_demandes_remb_statut CHECK (Statut IN ('en_attente','approuvee','refusee','remboursee','echouee'))
);

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Paiements' AND COLUMN_NAME='Date_remboursement')=0,
    'ALTER TABLE Paiements ADD COLUMN Date_remboursement DATETIME NULL','DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Paiements' AND COLUMN_NAME='Motif_remboursement')=0,
    'ALTER TABLE Paiements ADD COLUMN Motif_remboursement VARCHAR(255) NULL','DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Paiements' AND COLUMN_NAME='Ref_refund')=0,
    'ALTER TABLE Paiements ADD COLUMN Ref_refund VARCHAR(255) NULL','DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Paiements' AND COLUMN_NAME='Ref_paiement_intent')=0,
    'ALTER TABLE Paiements ADD COLUMN Ref_paiement_intent VARCHAR(255) NULL','DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Paiements' AND CONSTRAINT_NAME='chk_paiements_statut')>0,
    'ALTER TABLE Paiements DROP CHECK chk_paiements_statut','DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
ALTER TABLE Paiements ADD CONSTRAINT chk_paiements_statut
    CHECK (Statut IS NULL OR Statut IN ('en_attente','paye','echoue','rembourse','remboursement_en_cours'));
