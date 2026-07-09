
UPDATE Evenements
   SET Date_ = DATE_ADD(NOW(), INTERVAL (10 + (Id_Evenements * 7)) DAY)
 WHERE Date_ IS NOT NULL AND Date_ < NOW();

UPDATE Formations
   SET Date_formation = DATE_ADD(NOW(), INTERVAL (14 + (Id_Formations * 9)) DAY)
 WHERE Date_formation IS NOT NULL AND Date_formation < NOW();
