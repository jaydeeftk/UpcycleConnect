
CREATE TABLE IF NOT EXISTS Box(
   Id_Box INT AUTO_INCREMENT,
   Reference VARCHAR(50) NOT NULL,
   Capacite INT NOT NULL DEFAULT 1,
   Statut VARCHAR(50) NOT NULL DEFAULT 'disponible',
   Id_Conteneurs INT NOT NULL,
   PRIMARY KEY(Id_Box),
   UNIQUE KEY uq_box_reference (Reference),
   CONSTRAINT fk_box_conteneur FOREIGN KEY(Id_Conteneurs) REFERENCES Conteneurs(Id_Conteneurs),
   CONSTRAINT chk_box_capacite CHECK (Capacite >= 0),
   CONSTRAINT chk_box_statut CHECK (Statut IN ('disponible','pleine','maintenance','hors_service'))
);

DROP PROCEDURE IF EXISTS _mig_add_col;
DELIMITER //
CREATE PROCEDURE _mig_add_col(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col) THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_col('Objets', 'Id_Box', 'Id_Box INT NULL');
DROP PROCEDURE IF EXISTS _mig_add_col;

DROP PROCEDURE IF EXISTS _mig_add_fk;
DELIMITER //
CREATE PROCEDURE _mig_add_fk(IN tbl VARCHAR(64), IN fkname VARCHAR(64), IN ddl TEXT)
BEGIN
   IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl
                        AND CONSTRAINT_NAME = fkname AND CONSTRAINT_TYPE = 'FOREIGN KEY') THEN
      SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD CONSTRAINT `', fkname, '` ', ddl);
      PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
   END IF;
END //
DELIMITER ;
CALL _mig_add_fk('Objets', 'fk_objets_box', 'FOREIGN KEY (Id_Box) REFERENCES Box(Id_Box)');
DROP PROCEDURE IF EXISTS _mig_add_fk;
