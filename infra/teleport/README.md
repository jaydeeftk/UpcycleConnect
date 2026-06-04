# Bastion Teleport — accès distant RGPD

Point d'entrée **unique** et *identity-aware* vers la pile RGPD (Module 3,
bloc INFRA). Le bastion authentifie l'utilisateur, applique le MFA et le
RBAC, **journalise** et **enregistre** chaque session, puis proxifie :

- l'**application RGPD** (`rgpd-app:8090`) — chemin nominal du DPO ;
- la **base RGPD** (`rgpd-db:3306`) — accès audité d'administration
  (« break-glass »), à activer explicitement (voir plus bas).

Référence schéma réseau : nœud **« BASTION TELEPORT »** (DMZ) →
**« Application RGPD »** (segment interne) → site **SUISSE**.

```
   Client (tsh / navigateur)
            │  TLS 443 (MFA + RBAC)
            ▼
   ┌───────────────────┐   patte DMZ (public)
   │  BASTION TELEPORT │──────────────────────────
   │  auth + proxy     │
   │  app_service      │   patte interne (rgpd_rgpd-net, internal:true)
   │  db_service       │──────────────┬───────────────┐
   └───────────────────┘              ▼               ▼
                                  rgpd-app:8090    rgpd-db:3306
```

## 1. Prérequis

1. Démarrer **d'abord** la pile RGPD pour créer le réseau interne :
   ```bash
   cd ../rgpd && cp .env.example .env   # puis éditer les secrets
   docker compose -f docker-compose.rgpd.yml up -d --build
   ```
2. Dans `teleport.yaml`, remplacer `CHANGEME_RGPD_API_TOKEN` par la **même
   valeur** que `RGPD_API_TOKEN` (fichier `infra/rgpd/.env`). C'est le
   jeton que le bastion injecte pour passer l'authentification de l'app.
3. Adapter `public_addr` (et le DNS) : l'accès applicatif se fait par
   sous-domaine `rgpd-app.<public_addr>` — prévoir un enregistrement
   DNS *wildcard* `*.bastion…` pointant vers le proxy.

## 2. Démarrage du bastion

```bash
docker compose -f docker-compose.teleport.yml up -d
docker logs -f rgpd-bastion        # vérifier le démarrage
```

> TLS : sans `https_keypairs` ni ACME, Teleport génère un certificat
> auto-signé (avertissement navigateur). En production, fournir un
> certificat ou activer `proxy_service.acme`.

## 3. RBAC — application des rôles (moindre privilège)

Trois rôles, avec **séparation des pouvoirs** :

| Rôle               | Application RGPD | Base RGPD            | Audit / sessions | MFA |
|--------------------|------------------|----------------------|------------------|-----|
| `rgpd-dpo`         | oui              | lecture seule (`rgpd_ro`) | —          | oui |
| `rgpd-infra-admin` | oui              | complet (`rgpd`, `rgpd_ro`) | lecture    | oui |
| `rgpd-auditor`     | **non**          | **non**              | lecture          | —   |

```bash
# Charger les rôles
for r in roles/*.yaml; do tctl create -f "$r"; done

# Créer les utilisateurs et leur attribuer un rôle
tctl users add dpo      --roles=rgpd-dpo
tctl users add audit    --roles=rgpd-auditor
tctl users add infra    --roles=rgpd-infra-admin
# -> suivre le lien d'inscription pour définir le mot de passe + MFA
```

## 4. Accès DPO à l'application

```bash
tsh login --proxy=bastion.rgpd.upcycleconnect.example:443 --user=dpo
tsh apps ls                     # liste les apps autorisées -> rgpd-app
tsh apps login rgpd-app         # ouvre une session applicative
```
La session ouvre l'app dans le navigateur **via le proxy**. À chaque
requête, Teleport :
- injecte `Authorization: Bearer …` (jeton attendu par l'app) ;
- injecte l'en-tête signé `Teleport-Jwt-Assertion` ; l'application en
  extrait le claim `username` pour **attribuer** chaque action dans son
  journal d'accès (corrélé au journal d'audit du bastion).

## 5. Accès base de données (optionnel, audité)

Teleport établit un mTLS vers MySQL : la base doit **faire confiance à la
CA de Teleport**. Étapes :

```bash
# 1) Exporter la CA hôte de la base depuis Teleport
tctl auth sign --format=db --host=rgpd-db --out=server --ttl=2190h

# 2) Monter server.cas / server.crt / server.key dans rgpd-db et activer
#    le TLS côté MySQL (require_secure_transport, ssl-ca, ssl-cert, ssl-key).

# 3) Créer l'utilisateur applicatif EN LECTURE SEULE attendu par le RBAC,
#    authentifié par certificat :
#    CREATE USER 'rgpd_ro'@'%' REQUIRE SUBJECT '/CN=rgpd_ro';
#    GRANT SELECT ON rgpd.* TO 'rgpd_ro'@'%';
```

Connexion auditée :
```bash
tsh db login --db-user=rgpd_ro --db-name=rgpd rgpd-db
tsh db connect rgpd-db
```

## 6. Audit & conformité (art. 5.2 & 32 RGPD)

- **Qui a accédé** à l'app / la base, quand, depuis où : journal d'audit
  du bastion.
  ```bash
  tsh recordings ls           # enregistrements de session
  tctl get events             # événements d'audit
  ```
- **Ce qui a été fait** dans l'app (CRUD, droits, exports) : journal
  applicatif (`GET /api/journal`), attribué à l'identité Teleport.
- **Révocation immédiate** d'un accès compromis :
  ```bash
  tctl lock --user=dpo --message="incident" --ttl=720h
  ```

## 7. Modèle de sécurité (résumé)

- Bastion = **seul** point d'entrée ; app et base sur réseau interne
  `internal: true` (aucune route Internet, aucun port publié).
- **MFA** obligatoire (DPO, infra) ; **RBAC** au moindre privilège ;
  **séparation des pouvoirs** (auditeur sans accès aux données).
- **Enregistrement de session** + **journal d'audit** côté bastion,
  **journal applicatif** côté app : double traçabilité corrélée.
- Conteneurs durcis (non-root, rootfs en lecture seule, capabilities
  supprimées, `no-new-privileges`).
