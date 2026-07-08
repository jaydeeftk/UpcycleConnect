SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Abonnement' AND COLUMN_NAME='Stripe_Subscription_Id')=0,
    'ALTER TABLE Abonnement ADD COLUMN Stripe_Subscription_Id VARCHAR(255) NULL UNIQUE', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Id_Abonnement')=0,
    'ALTER TABLE Contrats ADD COLUMN Id_Abonnement VARCHAR(50) NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Id_Publicites')=0,
    'ALTER TABLE Contrats ADD COLUMN Id_Publicites VARCHAR(50) NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND COLUMN_NAME='Description')=0,
    'ALTER TABLE Contrats ADD COLUMN Description VARCHAR(500) NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND CONSTRAINT_NAME='fk_contrats_abonnement')=0,
    'ALTER TABLE Contrats ADD CONSTRAINT fk_contrats_abonnement FOREIGN KEY (Id_Abonnement) REFERENCES Abonnement(Id_Abonnement)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Contrats' AND CONSTRAINT_NAME='fk_contrats_publicites')=0,
    'ALTER TABLE Contrats ADD CONSTRAINT fk_contrats_publicites FOREIGN KEY (Id_Publicites) REFERENCES Publicites(Id_Publicites)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Publicites' AND COLUMN_NAME='Id_Services')=0,
    'ALTER TABLE Publicites ADD COLUMN Id_Services INT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Publicites' AND CONSTRAINT_NAME='fk_publicites_services')=0,
    'ALTER TABLE Publicites ADD CONSTRAINT fk_publicites_services FOREIGN KEY (Id_Services) REFERENCES Services(Id_Services)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;