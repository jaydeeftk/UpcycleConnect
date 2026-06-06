# UpcycleConnect — Checklist « Logique métier » (Phase 8)

Branche : `refonte-coherence-m1`. Objectif du mandat : une logique métier complète et
rigoureuse de A à Z, par rôle, avec **machines à états explicites**, **invariants
garantis**, **autorisation au niveau métier**, **concurrence maîtrisée**, **UI dérivée
de l'état serveur**, le tout **SOLID** et **prouvé**.

Ce document récapitule, par entité : la machine à états, les invariants, l'autorisation,
et la preuve (commits + harnais HTTP). Il liste aussi honnêtement les **dettes connues**.

---

## 1. Conventions transverses

**Architecture SOLID (graphe de dépendances)**
`domain` (pur, stdlib) ← `repository` (SQL) ← `services` (cas d'usage + transactions + autorisation) → `database`.
`httpx` (bridge erreurs→HTTP) ← `handlers` (fins) → {services, middleware, domain}.
La règle métier ne vit JAMAIS dans un handler ni dans une vue PHP.

**Erreurs métier typées → HTTP** (`domain/errors.go`, `httpx.WriteError`) :

| Constructeur domaine | Catégorie | HTTP |
|---|---|---|
| `Introuvable` | ressource absente | 404 |
| `Forbidden` | rôle/propriété insuffisants | 403 |
| `Invalide` | entrée métier-invalide | 422 |
| `EtatInvalide` | transition interdite depuis l'état | 409 |
| `Deja` / `Complet` / `Conflit` | idempotence / capacité / concurrence | 409 |
| `PaiementRequis` | paiement nécessaire | 402 |
| (défaut) | erreur interne, message générique (CWE-209) | 500 |

**Identité** : toujours issue du **JWT (`sub`)**, jamais de l'URL ni du corps.

**Concurrence** : les transitions lisent la ligne `SELECT … FOR UPDATE`, valident l'état
via le domaine, puis écrivent — le tout en **transaction** (pas d'effet de bord partiel).
L'occupation dérivée d'une box utilise `READ COMMITTED` pour éviter le sur-remplissage.

**Dernière ligne de défense en base** : `CHECK` de vocabulaire sur chaque colonne de
statut (miroir des constantes du domaine) — aucune valeur hors-enum ne peut être écrite.

**UI dérivée du serveur** : l'API expose `allowed_actions` (et les badges) ; le front
n'affiche que ces actions, il ne décide rien.

---

## 2. Checklist par entité

Légende : [OK] = couvert et prouvé ; [DETTE] = à traiter (voir §4).

### #22 — Annonce
- **États** : `brouillon → en_attente → validee → (refusee | expiree | …)` ; visibilité publique dérivée de l'état.
- **Invariants** : seules les annonces `validee` sont publiquement visibles ; l'auteur est le particulier propriétaire.
- **Autorisation** : création/édition/annulation réservées au propriétaire (identité JWT) ; modération réservée à l'admin.
- **Preuve** : commits `c819d61` (vertical), `2d38caa` (inscription/identité). CHECK statut en base. [OK]

### #23 — Demande / Conteneur / Box
- **États (dépôt)** : `en_attente → validee → (refusee)` ; matérialise un objet `en_stock` en box à la validation.
- **Invariants** : **occupation dérivée** d'une box = `COUNT` des objets `en_stock` **+ `reserve_pro`** (un objet réservé occupe toujours sa place) ; pas de sur-remplissage.
- **Concurrence** : validation sous `FOR UPDATE` + `READ COMMITTED` → deux validations simultanées ne dépassent pas la capacité.
- **Autorisation** : file privée par utilisateur (identité JWT) ; validation réservée à l'admin.
- **Preuve** : commits `980c51a` (1 box/conteneur), `3e0d542` (vertical), `e22cae5` (occupation `reserve_pro`). Harnais conteneur **54/0** (régression #27). [OK]

### #24 — Contrat abo / Facture / Commission
- **États** : statuts de facturation et d'abonnement bornés par CHECK ; commission dérivée.
- **Invariants** : un professionnel ne voit que SES contrats (clé résolue depuis le JWT, pas de colonnes fantômes).
- **Autorisation** : **paiement exigé avant inscription** à un article payant (402 sinon).
- **Preuve** : commits `031e46c` (CHECK), `b14966b` (vertical), `3bf50a0` (paiement préalable). [OK]

### #25 — Forum (Sujets / Réponses)
- **États** : `ouvert → resolu → ferme` (CHECK `chk_sujets_statut`).
- **Invariants** : auteur stampé par `Id_Utilisateurs` (anti-usurpation) ; transitions réservées à l'auteur.
- **Autorisation** : mutations exigent le JWT ; propriété vérifiée sous verrou.
- **Preuve** : commits `561d6b3` (CHECK), `78ef8a9` (vertical + identité JWT). [DETTE] le front doit attacher l'`Authorization` sur les POST/PATCH forum.

### #26 — Récupération pro (objet déposé)
- **États** : `en_stock --reserver--> reserve_pro --recuperer--> recupere` ; `reserve_pro --annuler--> en_stock` (recupere terminal).
- **Invariants** : `reserve_pro` pose le propriétaire (Id_Professionnels) ; récupérer/annuler réservés au pro qui a réservé ; un objet réservé occupe toujours sa box.
- **Concurrence** : deux pros ne peuvent pas réserver le même objet (le 2ᵉ relit `reserve_pro` → 409).
- **Autorisation** : rôle pro (JWT) + propriété + précondition d'état.
- **Preuve** : commits `e22cae5`, `2ad7ae8`. Harnais **40/0**. [OK]

### #27 — Code d'accès / Code-barres
- **États (code-barres)** : `active → utilise` (CHECK `chk_codes_barres_statut`, terminal).
- **Invariants** : un code-barres `active` naît **dans la même transaction** que l'objet `en_stock` (tout objet a exactement un code) ; récupérer un objet (par id OU par scan) consomme le code → objet `recupere` ⟺ code `utilise`.
- **Concurrence** : deux scans du même code en parallèle → un 200, un 409.
- **Autorisation** : scan réservé au pro propriétaire ; le code voyage dans le **corps**, jamais l'URL.
- **Preuve** : commits `f2929c6` (CHECK), `3eaef2f` (vertical). Harnais **41/0**. [OK]

### #28 — Projet upcycling (+ Étapes)
- **États** : `en_cours ⇄ pause`, `{en_cours,pause} → termine → (rouvrir) en_cours` (CHECK `chk_projets_statut`).
- **Invariants** : un projet **terminé est figé** (ni édition de contenu ni ajout d'étape avant réouverture) ; statut modifiable UNIQUEMENT par transition (jamais via PUT) ; `date_debut` vide → NULL (corrige un 500 latent).
- **Autorisation** : **propriété vérifiée AVANT l'état** (un projet a toujours un propriétaire → 403 sans fuite d'état) ; étapes bornées à la propriété du projet parent ; identité JWT.
- **Concurrence** : `FOR UPDATE` → deux `terminer` simultanés → un 200, un 409.
- **UI dérivée** : `allowed_actions` par statut ; suppression cascade en transaction.
- **Preuve** : commits `ba6a117` (CHECK), `9effd4f` (vertical). Domaine 18/18 + harnais **72/0** + bisectabilité (`BISECT_A_BUILD_OK`). [OK]

### #29 — Score / Badges
- **Règle** : score **dérivé** de l'activité (barème en données : annonces×30, événements×20, sujets×10, dépôts×50, formations×15) ; badges **dérivés du score** (paliers Éco-Débutant/Recycleur Actif/Éco-Engagé/Phénix Vert).
- **Invariants** : lecture **pure** (le GET n'écrit plus le cache mort `Particuliers.Score`) ; barème et paliers remontés de la vue PHP vers le **serveur** (règle d'or) ; bug front corrigé (score=1000 retombait sur le 1er badge).
- **Autorisation** : `OwnerFromPath` + **identité JWT** (un non-admin ne lit que son score ; admin peut cibler).
- **Preuve** : commit `8c91135` (back) + `2e5789b` (front). Domaine 3/3 + harnais **20/0** + rendu PHP **26/0** (dérivation serveur, tests *killer*). [OK]

---

## 3. Sécurité (Phase 7)

Revue adversariale → découverte d'une **classe d'IDOR** : derrière `OwnerFromPath` (qui
autorise sur le *dernier* segment d'URL), des handlers lisaient un *autre* segment → une
URL `/{victime}/{moi}` contournait la garde.

| Route | Gravité | Origine | Correctif |
|---|---|---|---|
| `/api/score/{id}` | Moyenne | introduite en P4 | `e7eee5f` |
| `/api/conteneurs/user/{id}` | Moyenne | préexistante | `ec55704` |
| `/api/messages/user/{id}` | **Haute** (messages privés) | préexistante | `ec55704` |

Correctif uniforme : **identité du JWT** (non-admin → soi ; admin → via URL).

**Tests négatifs — harnais `prove_secu.sh` : 29/0**
- 3 IDOR prouvés fermés (attaquant récupère SA donnée, pas celle de la victime).
- Anti mass-assignment (statut non modifiable via PUT).
- Anti-injection SQL (titre malveillant stocké littéralement ; sweep : **259 requêtes, 0 concaténation**).
- Matrice de rôles (401/403) + hygiène d'erreurs CWE-209 (aucune fuite SQL/stack/panic).

**Balayage élargi (tout le back, pas seulement les verticals de la session)**
- **Upload non authentifié fermé** : `/api/messages/upload` exige désormais un JWT
  (commit `fix(secu)`) — l'endpoint acceptait des fichiers anonymement (orphelin côté front).
- Toutes les routes **sans middleware** auditées : lectures publiques, endpoints d'auth,
  dispatchers (annonces/événements/formations) qui gardent leurs mutations par `JWTAuth`
  en interne, succès Stripe validé via l'API (`PaymentStatus==Paid`).
- **Aucune identité d'autorisation tirée du body ou de l'URL** : tout passe par le JWT
  (la seule lecture d'`user_id` hors-JWT est une métadonnée Stripe vérifiée).

---

## 4. Dettes connues & hors-scope

1. [DETTE] **CSRF front (transverse)** : les formulaires POST du front (transitions projet,
   suppression, favoris) n'ont pas de jeton CSRF. Lacune préexistante à tout le front ;
   correctif = middleware CSRF + jetons. Non traité (intervention front globale).
2. [DETTE] **Badges/Gagner/Score (tables) orphelines** : la gamification réelle est *stateless*
   (dérivée du score). Ces tables ne sont ni lues ni écrites (et `Date_obtention` est mal
   placée sur `Badges` au lieu de `Gagner`). À trancher : supprimer ou exploiter.
3. [DETTE] **Drive navigateur (P6)** : table-driven + concurrence + dérivation front prouvés ;
   le *drive visuel* desktop/mobile reste à faire (nécessite d'exposer la stack jetable —
   décision réseau). Les règles serveur sont déjà prouvées au niveau HTTP.
4. [OK] **Front complété pour les verticals qui manquaient d'UI** :
   - **Récupération pro (#26/#27)** : `/professionnel/recuperation` (catalogue, réservation,
     scan), boutons dérivés de `allowed_actions` — commit `cc5b83d`.
   - **Mes annonces (#22)** : `/mes-annonces` (retirer / vendre), `allowed_actions` ajouté
     à la liste côté serveur (commit `34e6586`) puis consommé par la vue (commit `5af182b`).
     Au passage, correction d'un token JWT manquant qui faisait échouer l'annulation.
5. [DETTE] **CHECK en production** : les migrations CHECK supposent des données déjà conformes ;
   les valeurs héritées réelles en prod sont « inconnues » et doivent être vérifiées avant
   application (lecture prod approuvée requise) — aucune normalisation inventée.
6. [DETTE] **`forum` front** : doit attacher l'en-tête `Authorization` sur les mutations.

---

## 5. Récapitulatif des commits (mandat)

Socle : `05a73b7` (schéma cible), `5d9a645` (erreurs typées + mapping HTTP).
Verticals : Annonce `c819d61`/`2d38caa` · Demande/Conteneur/Box `980c51a`/`3e0d542`/`e22cae5`
· Facturation `031e46c`/`b14966b`/`3bf50a0` · Forum `561d6b3`/`78ef8a9` · Récup `2ad7ae8`
· Code-barres `f2929c6`/`3eaef2f` · Projet `ba6a117`/`9effd4f` · Score `8c91135`.
UI dérivée : `2e5789b`. Sécurité : `e7eee5f`/`ec55704`.

Tous les commits sont atomiques et compilent indépendamment (bisectables).
