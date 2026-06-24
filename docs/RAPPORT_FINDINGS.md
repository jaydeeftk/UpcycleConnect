# UpcycleConnect — Rapport de findings (Phase 2 — GATE)

Audit READ-ONLY du tip `main` @ `97cd715`. Méthode : 5 scouts parallèles (cartographie + 4 lots), preuve `fichier:ligne` + `SELECT`. **Aucune correction écrite.**

**Cadre :** les 19 bugs de ce catalogue correspondent par substance à Mission 1, déjà implémentée et déployée. La vérif confirme **14 RÉSOLUS** et isole **5 points à statuer**, dont **1 vrai trou jamais implémenté (Bug 2)**.

## Table de findings

| ID | Sévérité | Acteur | Cause racine / état confirmé | Fichiers:lignes | Stratégie de fix | Migration ? | Effort |
|---|---|---|---|---|---|---|---|
| **2** | **High** | Pro | **OUVERT — jamais implémenté.** L'achat d'un objet en vente par un pro → transaction + commission n'existe pas. `resoudrePrixItem` ne gère que `formation`/`evenement` ; **0 INSERT `Commissions`** dans tout le code ; `VendreAnnonce` ne fait que passer `Statut='vendue'` ; objets = récupération only. | `paiements.go:364-376`, `facturation_service.go:398-478`, `annonces.go`, `objets.go` ; `Commissions`=0 ligne | Étendre checkout `type=objet/annonce` → prix depuis Annonces → à la confirmation webhook, INSERT `Commissions` (Taux×Montant) + transition annonce. **Taux de commission non spécifié → décision requise.** | Possible (statut annonce/objet, lien paiement) | L |
| 17 | Low | Salarié | **PARTIEL.** Création + circuit validation OK (le BLOQUANT est résolu). Mais une formation/événement **déjà validé n'est plus ré-éditable** : l'édition n'est permise que si `Statut='en_attente'` et aucune logique ne repasse `Statut_validation='en_attente'` après modif. | `salaries.go:~150-156`, `evenements_salarie.go`, `admin_validation.go:54` | Sur édition d'un item `valide`, repasser `Statut_validation='en_attente'` (re-soumission à validation). | Non | S |
| 19 | Low | Admin | **PARTIEL.** Emboîtement box→conteneur + taux de remplissage **opérationnels**. Mais **aucune table d'incidents** : suivi des incidents/maintenance par box/conteneur absent. | `conteneur_service.go:111-119,311`, `admin/conteneurs/index.php:34`, schéma (pas de table `Incidents`) | Table `Incidents` (Id_Conteneurs/Id_Box, type, description, statut, date) + CRUD admin. | **Oui** | M |
| 4 | Low | Particulier | **PARTIEL (durcissement).** CP capturé, validé `preg_match /^\d{5}$/` côté PHP et persisté. Mais **aucune validation serveur Go** : un POST direct à l'API accepte un CP malformé. | `AnnonceController.php:121` (OK), `annonces.go`/`domain/annonce.go` (pas de garde) | Ajouter garde domaine Go (5 chiffres) sur création/màj annonce. | Non | S |
| 8 | Info | Salarié/Particulier | **RÉSOLU fonctionnellement** (RBAC correct : owner supprime le sien, salarié/admin supprime tout, autre → 403 via `SupprimerReponseUtilisateur`). Note : le check est au niveau service, pas middleware (défense en profondeur préférable mais non bloquant). | `forum_service.go:269-281`, `salaries_forum.go:39`, `admin.go:557` | (Optionnel) durcir au niveau route. | Non | XS |

## Confirmés RÉSOLUS (preuve à l'appui)

| ID | Sujet | Preuve clé |
|---|---|---|
| 17 (création) | Salarié crée/gère SON événement → validation admin → publié | `Statut_validation` ∈ {en_attente,valide,refuse} ; gate `GetEvenements WHERE Statut_validation='valide'` |
| 16 | Valeurs « en dur » module salarié | Aucune trouvée : lieu/tarif/capacité/places viennent du `$_POST`/DB. **Non reproduit.** |
| 5 | Dates/timezone | `DATE_FORMAT '%Y-%m-%dT%H:%i:%s'` partout + DSN `loc=Europe/Paris` ; wall-clock FR (politique assumée). |
| 11 | Inscription post-paiement | `EnregistrerPaiementItem` écrit `Reserver_formation`/`Participer_evenements` dans la tx webhook. `SELECT` : 2 résa + 3 participations. |
| 12 | Décrément places | `UPDATE Places_dispo-1` sous `FOR UPDATE` + garde `PlacesDispo>0`. Form 1 : 19/20 ; Form 2 : 14/15. |
| 13 | Inscription → planning | `GetPlanning` joint Reserver_formation/Participer_evenements. |
| 15 | Durée dans le planning | `GetPlanning` remonte `Duree` (events+formations). |
| 14 | Lecture planning | `GetPlanning` renvoie evenements+formations+libres. |
| 18 | Remboursement | `ExecuterRemboursement` 3 phases (lock→refund Stripe idempotent→libère place + désinscription + Historique). `Demandes_remboursement` + `Paiements.Ref_refund` peuplés. |
| 6 | Forum→conseils état résiduel | `cat_conseil`/`cat_forum` isolés (`ConseilController.php:21`). **Non reproduit** (no-op confirmé). |
| 7 | Réponse sur sujet résolu/fermé | Bloqué serveur (`domain.PeutRepondre`) + UI (`sujet.php:110`). |
| 9 | Filtre catégorie conseils | `conseils.go:21` `categorie != "" && != "tous"`. « Tous » → 4 conseils. |
| 10 | Contraste blanc/blanc | Aucun `text-white`+`bg-white` ; palette `text-base-content` adaptative. |
| 1 | Contrats/facturation pro | `FacturationDuProfessionnel` agrège abo+pub+commissions filtré par pro ; résiliation ownership-checkée. Total démo 270€. |
| 3 | Projets + photos avant/après | Ownership pro→projet→étape (`AjouterPhotoEtape`) ; `Medias.Id_Etapes/Type_photo`. |

## Remédiation priorisée & dépendances

1. **Bug 2 (High)** — le seul vrai manque fonctionnel vs la matrice RBAC (« Acheter un objet mis en vente : Pro ✅ »). **Décision bloquante avant fix : taux de commission** (non spécifié au descriptif) + workflow d'achat (checkout hosted réutilisable). Dépend de la plomberie Stripe (déjà réparée).
2. **Bug 17 (Low)** — ré-édition après validation : fix isolé, 1 ligne de logique.
3. **Bug 4 (Low)** — garde CP serveur : fix isolé domaine Go.
4. **Bug 19 (Low)** — incidents box/conteneur : migration + CRUD admin (le plus lourd des « petits »).
5. **Bug 8 (Info)** — durcissement RBAC route : optionnel.

Aucune dépendance inter-bugs hormis Bug 2 ↔ Stripe (résolu). Pas de collision de migration (prochain numéro libre : **022**).

## Hors-scope repéré (noté, non corrigé — scope creep interdit)
- UI admin ateliers minimaliste ; Publicites/Commissions sans CRUD admin ; redimensionnement images projets absent. Ce sont des dettes M1 déjà documentées, hors des 19 bugs.
