
UPDATE Evenements
   SET Date_ = CONCAT(DATE(Date_), ' ', ELT(1 + MOD(Id_Evenements, 3), '14:00:00', '10:00:00', '18:30:00'))
 WHERE Date_ >= NOW();

UPDATE Formations
   SET Date_formation = CONCAT(DATE(Date_formation), ' 09:00:00')
 WHERE Date_formation >= NOW();
