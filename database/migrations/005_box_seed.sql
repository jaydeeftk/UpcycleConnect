-- 005_box_seed.sql
-- Phase 4 — Vertical Demande/Conteneur/Box : peupler la table Box.
--
-- 001_box_et_occupation a créé la table Box (Conteneur 1—N Box) et le lien
-- Objets.Id_Box, mais aucune Box n'était insérée : la table restait vide et donc
-- le modèle d'occupation n'était jamais exercé. Cette migration crée UNE Box par
-- conteneur existant, de capacité égale à celle du conteneur. L'occupation reste
-- DÉRIVÉE côté service (COUNT des Objets 'en_stock' dans la Box), comparée à
-- Box.Capacite sous SELECT ... FOR UPDATE au moment de la validation d'un dépôt.
--
-- Idempotent : n'insère une Box que pour les conteneurs qui n'en ont pas encore.
-- Conteneurs.Capacite est un VARCHAR ; CAST en UNSIGNED, plancher à 1.

INSERT INTO Box (Reference, Capacite, Statut, Id_Conteneurs)
SELECT CONCAT('BOX-C', c.Id_Conteneurs),
       GREATEST(COALESCE(NULLIF(CAST(c.Capacite AS UNSIGNED), 0), 1), 1),
       'disponible',
       c.Id_Conteneurs
FROM Conteneurs c
WHERE NOT EXISTS (SELECT 1 FROM Box b WHERE b.Id_Conteneurs = c.Id_Conteneurs);
