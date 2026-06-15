-- Migration 015 : resynchronise Places_dispo des formations sur la realite des
-- reservations (Places_total - COUNT(Reserver_formation)), clampe a [0, Places_total].
-- Idempotente : recalcul a partir du COUNT a chaque execution, rejouable sans derive.
-- Formations uniquement ; les evenements ont une capacite computee (Capacite - COUNT)
-- et n'ont pas de colonne Places_dispo. Les sieges surbookes (payes sans ligne
-- d'inscription, item 11) sont volontairement ignores : sans ligne Reserver_formation
-- ils ne regonflent pas la disponibilite et restent une dette a regulariser (item 16).

UPDATE Formations f
SET f.Places_dispo = LEAST(
        GREATEST(
            COALESCE(f.Places_total, 0) - (
                SELECT COUNT(*) FROM Reserver_formation rf WHERE rf.Id_Formations = f.Id_Formations
            ),
            0
        ),
        COALESCE(f.Places_total, 0)
    );
