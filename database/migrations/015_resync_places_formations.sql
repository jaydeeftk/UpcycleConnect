
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
