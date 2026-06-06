# Points restants (action équipe requise)

## QR de dépôt côté particulier (sur « Mes demandes »)
**Fait :** côté pro, la page Récupération scanne le code-barres objet (caméra html5-qrcode
ou saisie manuelle) et affiche le QR de chaque objet réservé.

**Bloquant pour le côté particulier :** un objet matérialisé n'est pas relié à la demande
dont il provient (`Objets` n'a pas de colonne `Id_Demandes_conteneurs`). On ne peut donc
pas retrouver le code-barres (UCB-…) à afficher sur « Mes demandes ». Le seul code présent
sur la demande validée est le code d'accès conteneur (UC-…), volontairement flouté et révélé
par mot de passe — l'afficher en QR ouvert le divulguerait.

**Action équipe :**
1. Ajouter `Objets.Id_Demandes_conteneurs INT NULL` (init.sql + migration) et le renseigner
   à la validation de la demande.
2. Exposer le code-barres sur le DTO « mes demandes » (jointure Objets → Codes_Barres).
3. Afficher le QR du code-barres sur la demande validée + bouton de téléchargement.
4. Décider quel code le particulier présente au pro (le code-barres objet, pas le code
   d'accès conteneur).
