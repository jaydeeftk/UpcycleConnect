-- Migration 012 : redate les evenements/formations de demonstration dans le futur.
-- Les seeds avaient des dates fixes (mai/juin 2026) desormais passees, ce qui vidait
-- les pages publiques (qui masquent les elements passes). On ne touche qu'aux passes.

UPDATE Evenements
   SET Date_ = DATE_ADD(NOW(), INTERVAL (10 + (Id_Evenements * 7)) DAY)
 WHERE Date_ IS NOT NULL AND Date_ < NOW();

UPDATE Formations
   SET Date_formation = DATE_ADD(NOW(), INTERVAL (14 + (Id_Formations * 9)) DAY)
 WHERE Date_formation IS NOT NULL AND Date_formation < NOW();
