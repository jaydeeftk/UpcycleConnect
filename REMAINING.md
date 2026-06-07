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

---

# Audit UI/UX (juin 2026)

## Corrigés et vérifiés au navigateur (espaces accessibles sans connexion)
1. **admin/factures** : la vue contenait deux tableaux empilés et une balise `</div>`
   orpheline qui fermait `<main>` trop tôt — le vrai tableau sortait du layout et
   s'affichait comme une 3e colonne à côté de la sidebar. Vue nettoyée (un seul tableau).
2. **Contraste dark mode** (layouts admin, salariés, front) : `text-gray/slate-700/600/500`
   n'étaient pas remappés en sombre (ex. la colonne Date des factures ressortait trop foncée).
   Overrides ajoutés. Mode clair inchangé.
3. **/register** ouvre désormais directement l'onglet Inscription (avant : onglet Connexion).
4. **Pages 404 et 403** : étaient des documents HTML complets rendus à travers le layout
   `main` (document imbriqué → fond sombre en mode clair, 404 illisible). Transformées en
   fragments : elles héritent de la navbar, du footer et du thème. Vérifiées clair + sombre.
5. **Lien mort** « Mot de passe oublié ? » (`href="#"`, aucune route) retiré de la connexion.

## Corrigé au code, à confirmer en session connectée
6. **Pages professionnelles en pleine page** : les vues pro sont des documents complets
   (sidebar propre) mais étaient rendues via le layout `main` → on cumulait la navbar et le
   footer publics par-dessus l'app pro. Ajout d'un layout passe-plat `raw` utilisé par le
   dashboard, la création de projet et la récupération. **À confirmer visuellement** lors
   d'une connexion pro.

## Audité au code — aucun bug bloquant trouvé
- **Particulier** (score, conteneurs/create, planning, mes-annonces) : empty states présents,
  structure HTML équilibrée, dark géré par le layout.
- **Salarié** (dashboard, formations, événements, ateliers, conseils, planning) : dark géré
  par le layout (`bg-white` remappé), empty states présents.
- **Admin** (utilisateurs, annonces, conteneurs, demandes, contrats, etc.) : dark géré par le
  layout ; factures corrigé.
- **Jointure contrats** : correcte (`Contrats → Professionnels_artisans → Utilisateurs`).
- **KPIs dashboard admin** : réels (API `/admin/dashboard`), non codés en dur.

## À finir (nécessite un accès dont l'agent ne dispose pas)
- **Pass visuel des espaces connectés** (particulier / pro / salarié / admin) en clair ET
  sombre : nécessite une connexion. L'agent ne saisit pas de mots de passe (règle de sécurité) ;
  une connexion par espace dans le navigateur suffit pour que l'audit visuel soit terminé.
- **Pass responsive 375px** : l'environnement navigateur est figé à 1536px de large (le
  redimensionnement ne change pas le viewport de rendu). La navbar et les layouts utilisent
  déjà les classes responsive (`md:hidden` hamburger, `lg:grid-cols-2`). À valider sur un vrai
  mobile ou via les DevTools.

## Dette / nettoyage (non bloquant)
- `ressources/views/admin/maintenance/index.php` n'est routé nulle part (mort) et contient un
  bloc dupliqué (la page « Site en maintenance » publique). À supprimer si confirmé inutile.
- « Mot de passe oublié » : fonctionnalité absente (lien retiré). À implémenter si souhaité.
- i18n : navigation traduite (fr/en/es/de), corps des pages en FR (cf. note i18n existante).

---

# V2 — Logique métier + routing + UI (juin 2026)

## Fait + vérifié au navigateur (admin / public)
- **Helper `format.php`** (formatStatut/formatDate/formatPrix/statutCouleur) inclus globalement.
  Dates `JJ/MM/AAAA`, statuts lisibles, prix `30,00 €`/Gratuit — appliqués sur **tout l'admin**
  (factures, conteneurs, demandes, événements, contrats, utilisateurs + détail, formations,
  conseils, forum, notifications) et les **pages publiques** (événements, formations, conseils, annonces).
- **window.confirm → modale inline** `confirmer()`/`ucConfirm()` (**0 window.confirm**), et
  **window.alert → toast** non bloquant (**0 window.alert**).
- **Conteneurs — CRUD complet** : liste (taux réel Box), **page détail `/admin/conteneurs/{id}`**
  (infos box, capacité, taux, dépôts + valider/rejeter), modale **Modifier**, suppression confirmée.
  Côté API : `id_conteneur` exposé sur les demandes admin (filtrage par box) ; gofmt/vet/build OK.

## Décisions actées
- **Modèle conteneur Box conservé** (taux = occupation Objets / capacité Box). Pas de réécriture
  en modèle poids (aurait régressé un système fonctionnel et plus complet).

## Fait au code, à confirmer sous session connectée
- **Formatage dates/statuts particulier / pro / salarié** appliqué via le helper (déjà prouvé en
  admin). Vérification visuelle au navigateur en attente des connexions de ces rôles.

## Nécessite l'utilisateur (l'agent ne peut pas, par sécurité, saisir de mot de passe)
- **Flux conteneur complet à 4 rôles** (particulier dépose → admin valide + QR → particulier voit
  le QR → pro récupère → taux box) : test d'acceptation central de la V2.
- **Pass visuel pro / salarié / particulier** en clair + sombre (1 connexion par espace suffit).
- **Responsive 375px** : viewport figé à 1536px dans cet environnement (à valider sur vrai mobile / DevTools).
- **Isolation rôles** : middlewares admin/pro/salarié + redirections post-login déjà corrects au
  code ; à confirmer en fenêtre privée rôle par rôle.
