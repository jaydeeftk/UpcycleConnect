# UpcycleConnect — Cartographie d'architecture (Phase 0)

Audit du tip courant : `main` @ `97cd715`. `finalisation-m1` est 1 commit derrière (`0c160f5`, lui manque le fix `97cd715` payer.php).

## 1. Stack & arborescence

- **API Go 1.23** (`api/`) : net/http, JWT HS256, layout `internal/{handlers,services,repository,domain,middleware,routes,httpx,database,notifier,websockets}`. Entrée `cmd/api`.
- **Frontend PHP MVC** (`frontend/`) : sans framework. Routeur `routes/web.php`, contrôleurs `app/controllers/{front,admin,salaries,professionnel}`, vues `ressources/views`, `app/services/ApiService.php` (forward JWT), middleware `app/middleware/{Admin,Salarie,Professionnel,Maintenance}Middleware.php`.
- **MySQL 8.0** : 57 tables. **Migrations versionnées** `database/migrations/001…021` (idempotentes).
- **Caddy** : `upcycleconnect.tech` → reverse_proxy `frontend:80` ; Apache `.htaccess` proxifie `^api/(.*)` → `http://api:8080/api/$1`.

## 2. Couche d'autorisation (Go)

`api/internal/middleware/jwt.go` :
- `JWTAuth` (l.18), `OptionalJWT` (l.49), `AdminOnly` (l.75 → role=admin), `SalarieOnly` (l.86 → salarie|admin), `ProfessionnelOnly` (l.103 → professionnel|admin).
- `ownership.go` : `GetUserID` (sub), `OwnerFromPath` (l.38 → admin OU pathID==userID, sinon 403).

Garde FK admin : `Administrateurs.Id_Utilisateurs` est une vraie FK ; pas d'admin id=0 orphelin. RBAC appliqué côté API (source de vérité) + doublé côté PHP (session role).

## 3. Endpoints clés (extrait)

| Path | Handler | Auth |
|---|---|---|
| POST `/api/paiements/checkout` | CreateCheckoutSession | JWTAuth |
| POST `/api/paiements/webhook` | StripeWebhook | none (signature) |
| GET `/api/planning/{id}` | GetPlanning | OwnerFromPath |
| POST `/api/planning/personnel` | AjouterEntreePlanning | JWTAuth |
| POST `/api/remboursements` | CreerDemandeRemboursement | JWTAuth |
| `/api/salaries/planning/evenement/{id}` | CreateEvenement | SalarieOnly |
| `/api/admin/evenements/{id}` (valider/rejeter) | AdminEvenementAction | AdminOnly |
| GET `/api/professionnels/facturation` | ProfessionnelGetFacturation | ProfessionnelOnly |
| `/api/professionnels/projets/{id}/etapes/{e}/photos` | ProfessionnelEtapeAction | ProfessionnelOnly |
| GET `/api/box/{id}` (ouvrir) | OuvrirUpcycleBox | none (code strict) |
| GET `/api/conseils` | GetConseils | none |
| DELETE `/api/forum/reponses/{id}` | DeleteForumReponse | JWTAuth + ownership service |

## 4. Plomberie Stripe (source de vérité = webhook)

- Webhook : `handlers/paiements.go:111` `StripeWebhook` → vérif signature `ConstructEventWithOptions` (l.126), event `checkout.session.completed` + `payment_status=paid`, metadata `type/item_id/user_id`.
- Écriture : `services/facturation_service.go:398` `EnregistrerPaiementItem` → `withTx` (atomique) avec **garde idempotence `PaiementReferenceExiste(session_id)`** (l.401) → Facture + Lignes_Facture + Paiement + inscription (Reserver_formation/Participer_evenements) + Historique.
- Endpoint webhook **enregistré chez Stripe** (`we_1Tj3Ik…`, events `checkout.session.completed` + `charge.refunded`), `STRIPE_WEBHOOK_SECRET` aligné en prod. `resoudrePrixItem` (l.364) ne résout QUE `formation`/`evenement` → voir Findings Bug 2.

## 5. i18n

`app/helpers/lang.php` `t(clé, fallback)` → `$_SESSION['lang']` (fr par défaut) → `frontend/lang/{fr,en,de,es}.php`. Switch via `/lang/{lang}`.

## 6. Schéma — tables porteuses des corrections M1

- `Evenements`/`Formations`/`Atelier` : `Statut_validation`, `Motif_refus` (circuit modération).
- `Planning_personnel` : `Titre`, `Lieu`, `Description` (entrées libres).
- `Contrats` : `Montant`, `Frequence` ; `Abonnement.Id_Professionnels`.
- `Medias` : `Id_Etapes`, `Type_photo` (avant/après), `Id_Annonces` nullable.
- `Box` : `Taille` (standard/encombrant), FK `Id_Conteneurs` ; `Demandes_conteneurs.Id_Box` ; `Codes_Barres.Id_Box`.
- `Annonces` : `Code_postal`, `Ville`.
- `Commissions` : `Id_Commission, Taux, Montant, Date_, Id_Annonces, Id_Facture` — **schéma présent, 0 ligne, jamais peuplé par le code** (cf. Bug 2).

## 7. Convention de commit (observée sur 30 commits)

`type(scope): message` en français, impératif, minuscule. Types : `feat`, `fix`, `chore`, `secu`, `i18n`. Scopes : `paiements`, `conteneurs`, `pro`, `planning`, `salaries`, `ui`, `dates`, `admin`. Pas de `Co-Authored-By`.
