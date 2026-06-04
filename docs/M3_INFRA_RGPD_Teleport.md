# Module 3 (bloc INFRA) — Accès distant RGPD via bastion Teleport

> Dossier d'architecture du module M3 : mise en place d'un **accès distant
> sécurisé et identity-aware** (bastion **Teleport**) vers une **application
> isolée de gestion des données personnelles** des adhérents ayant transité
> par la **Suisse**, conforme au RGPD.
>
> Périmètre livré : `rgpd-app/` (service Go + base dédiée), `infra/rgpd/`
> (conteneurisation isolée), `infra/teleport/` (bastion + RBAC).

---

## 1. Contexte et objectif

L'association traite des données personnelles d'adhérents dont les données
**transitent par le site suisse** (collecte transfrontalière, ateliers).
Ces données doivent être :

- **cloisonnées** du système principal (minimisation, art. 5.1.c) ;
- **accessibles à distance** uniquement par des personnes habilitées (DPO,
  infra, audit), de façon **authentifiée, tracée et enregistrée** ;
- **conformes** aux droits des personnes (accès, rectification, effacement,
  portabilité…) et au registre des traitements.

Le module répond à ces trois exigences par : une **application dédiée** sur
une **base isolée**, et un **bastion Teleport** comme **point d'entrée
unique**.

## 2. Rattachement au schéma réseau de l'équipe

Le module s'insère dans la cible décrite par `schema/Schéma réseau.drawio`,
sans la modifier. Nœuds et segments concernés (labels d'origine) :

| Élément du schéma        | Rôle dans le module M3                                   |
|--------------------------|---------------------------------------------------------|
| **BASTION TELEPORT**     | Point d'entrée unique, en **VLAN 100 - DMZ** (Paris).   |
| **Application RGPD**      | Service applicatif + base, segment **isolé** (datacenter). |
| **SUISSE**               | Site source des données, relié par **VPN Site-to-Site / IPSec**. |
| **OPNSense / PFSense**   | Pare-feux : filtrage DMZ ↔ segment RGPD.                |
| **Reverse Proxy / Web Front** | Front du système principal — **distinct** du bastion RGPD. |

VLAN existants (légende du schéma) : `10 Direction`, `20 Marketing`,
`30 Commercial`, `40 RH`, `50 Informatique`, `60 Hmanager`, `70 Regional`,
`100 DMZ`, `WiFi_LAN`, `WiFi_GUEST`.

## 3. Architecture cible

```
        Internet
           │  TLS 443 (MFA + RBAC, identity-aware)
           ▼
  ┌──────────────────────┐        VLAN 100 - DMZ
  │   BASTION TELEPORT    │        (Paris)
  │  auth + proxy         │
  │  app_service          │
  │  db_service           │
  └───────────┬──────────┘
              │  flux filtré (pare-feu OPNSense/PFSense)
              ▼        VLAN 90 - RGPD (proposé, isolé, datacenter)
   ┌────────────────────┐        ┌──────────────────┐
   │   Application RGPD  │ ─────▶ │   Base RGPD       │
   │   rgpd-app:8090     │  SQL   │   rgpd-db:3306    │
   └────────────────────┘        └──────────────────┘
              ▲
              │ VPN Site-to-Site / IPSec
        Site SUISSE  (source des données d'adhérents transités)
```

**Principe clé** : le segment RGPD n'a **aucune route directe** vers
Internet ni vers les VLAN bureautiques. Le **seul** flux entrant autorisé
provient du bastion. C'est matérialisé dans le code par un réseau Docker
`internal: true` (`infra/rgpd/docker-compose.rgpd.yml`) et un bastion
**multi-homé** (`infra/teleport/docker-compose.teleport.yml` : patte DMZ +
patte interne `rgpd_rgpd-net`).

## 4. Plan d'adressage IP / VLAN (proposition)

Le schéma définit les VLAN mais pas les sous-réseaux. Proposition d'un plan
cohérent (à valider avec l'équipe infra), encodant le **site** sur le 2ᵉ
octet et le **VLAN** sur le 3ᵉ :

| Site / segment            | VLAN | Sous-réseau        | Exemples d'hôtes                         |
|---------------------------|------|--------------------|------------------------------------------|
| Paris — DMZ               | 100  | `10.1.100.0/24`    | BASTION TELEPORT `10.1.100.10`           |
| Paris — Informatique      | 50   | `10.1.50.0/24`     | Postes admin infra (clients `tsh`)       |
| Montreuil — Regional      | 70   | `10.2.70.0/24`     | Sites secondaires (11ᵉ / 13ᵉ)            |
| Suisse — LAN              | 70   | `10.3.70.0/24`     | Collecte transfrontalière                |
| Datacenter — **RGPD**     | **90** | **`10.10.90.0/24`** | rgpd-app `10.10.90.20`, rgpd-db `10.10.90.30` |

> **Ajout M3** : un **VLAN 90 dédié et isolé** pour le segment RGPD, dans le
> datacenter. Règle de pare-feu : `permit DMZ(10.1.100.10) → RGPD(10.10.90.0/24)
> tcp/443 (tunnel bastion)` ; **tout le reste en `deny`** vers ce VLAN.

## 5. Composants livrés

| Composant                         | Emplacement                                   | Vérification |
|-----------------------------------|-----------------------------------------------|--------------|
| Schéma base RGPD (5 tables)       | `rgpd-app/schema.sql`                          | chargée + jeux d'essai |
| Service applicatif (Go)           | `rgpd-app/*.go`                                | `go build/vet`, `gofmt` OK |
| Image conteneur durcie            | `rgpd-app/Dockerfile`                          | build multi-étapes, non-root |
| Pile isolée (db + app)            | `infra/rgpd/docker-compose.rgpd.yml`           | réseau `internal:true` |
| Bastion + RBAC                    | `infra/teleport/`                              | validé Teleport v16.5.18 |

L'application expose : CRUD adhérents, **consentements** (recueil/retrait),
**demandes de droits** (échéance J+30), **registre des traitements**,
**journal d'accès**, **export de portabilité**, **effacement par
anonymisation**, et un tableau de bord DPO (HTML auto-échappé).

## 6. Accès distant via Teleport

- **Point d'entrée unique** : proxy TLS sur `:443` (multiplexé). Aucun port
  de l'app ni de la base n'est publié.
- **MFA obligatoire** (OTP/WebAuthn) pour DPO et infra.
- **RBAC au moindre privilège**, avec **séparation des pouvoirs** :

  | Rôle (`infra/teleport/roles/`) | App RGPD | Base RGPD          | Audit | MFA |
  |--------------------------------|----------|--------------------|-------|-----|
  | `rgpd-dpo`                     | oui      | lecture (`rgpd_ro`) | —     | oui |
  | `rgpd-infra-admin`            | oui      | complet            | lecture | oui |
  | `rgpd-auditor`               | **non**  | **non**            | lecture | —   |

- **Enregistrement de session** (`proxy-sync`) et **journal d'audit** côté
  bastion.
- **Propagation d'identité** : à chaque requête, le bastion injecte un jeton
  **signé** `Teleport-Jwt-Assertion` ; l'application en extrait le claim
  `username` (`rgpd-app/teleport.go`) pour **attribuer** chaque action dans
  son propre journal — d'où une **double traçabilité corrélée**.

## 7. Conformité RGPD — matrice

| Article                         | Exigence                                  | Mise en œuvre |
|---------------------------------|-------------------------------------------|---------------|
| **5.1.c** minimisation          | Données strictement nécessaires           | Base dédiée, colonnes limitées au transit |
| **5.2** responsabilité          | Démontrer la conformité                   | `Journal_Acces` + audit Teleport |
| **6 / 7** licéité & consentement| Base légale, recueil/retrait              | Table `Consentements`, retrait aussi simple que le recueil |
| **12.3** délais                 | Réponse sous 1 mois                       | `Date_Echeance = J+30` (calcul base) |
| **15** accès                    | Communication des données                 | `GET /api/adherents/{id}` |
| **16** rectification            | Correction                                | `PUT /api/adherents/{id}` |
| **17** effacement               | Droit à l'oubli                           | Anonymisation irréversible + purge consentements |
| **20** portabilité              | Format structuré, lisible machine         | Export JSON (`Content-Disposition`) |
| **30** registre                 | Registre des traitements                  | Table `Registre_Traitements` + endpoint |
| **32** sécurité                 | Mesures techniques                        | Cloisonnement, MFA, chiffrement en transit, journalisation |
| **44–49** transferts            | Encadrement hors UE                       | Voir §8 |

### 8. Transfert depuis la Suisse (art. 44–49)

La Suisse bénéficie d'une **décision d'adéquation** de la Commission
européenne (décision 2000/518/CE, art. **45** RGPD). Le transfert est donc
**licite sans garanties supplémentaires** (ni clauses contractuelles types
art. 46, ni dérogations art. 49). C'est inscrit comme base du transfert dans
`Registre_Traitements.Garantie_Transfert` et dans la colonne
`Adherents.Base_Legale_Transfert` (« Décision d'adéquation (Suisse) »).

## 9. Modèle de menace (synthèse)

| Menace                                  | Mitigation |
|-----------------------------------------|-----------|
| Exposition directe de l'app/base        | Réseau `internal:true`, aucun port publié, accès seulement via bastion |
| Vol d'identifiants                      | MFA obligatoire, sessions courtes (`max_session_ttl`), verrous (`lock`) |
| Usurpation d'identité applicative       | Jeton porteur (portail de confiance) + JWT signé Teleport ; en prod, vérif. signature via JWKS |
| Injection SQL                           | **100 % requêtes paramétrées** (`?`) |
| XSS sur le tableau de bord              | `html/template` (échappement automatique) |
| Élévation de privilèges conteneur       | Non-root, rootfs en lecture seule, `cap_drop: ALL`, `no-new-privileges` |
| Répudiation d'une action                | Double journal (bastion + app), enregistrement de session |
| Accès illégitime à l'audit              | Rôle `rgpd-auditor` séparé, sans accès aux données |

## 10. Durées de conservation

- Données adhérents : **3 ans après le dernier contact**
  (`Registre_Traitements.Duree_Conservation`), puis anonymisation (art. 17).
- Journal d'accès / enregistrements de session : conservation alignée sur la
  politique de sécurité (preuve art. 5.2), distincte des données métier.

## 11. Runbook DPO (extrait)

```bash
tsh login --proxy=bastion.rgpd.upcycleconnect.example:443 --user=dpo
tsh apps login rgpd-app           # ouvre l'app via le bastion (session tracée)
# Exercice d'un droit (ex. effacement) depuis l'app ou l'API :
#   DELETE /api/adherents/{id}     -> anonymisation + entrée de journal
# Suivi des demandes (échéance J+30) :
#   GET /api/demandes              -> triées par échéance
```
Procédure détaillée et activation de l'accès base (mTLS) : voir
`infra/teleport/README.md`.

## 12. Limites et pistes d'amélioration

- **Vérification de signature du JWT** Teleport côté app (JWKS du cluster) :
  passer du modèle « confiance réseau » à une vérification cryptographique.
- **Accès base via Teleport** : nécessite la configuration mTLS de MySQL
  (étapes documentées) ; aujourd'hui chemin nominal = l'application.
- **Haute disponibilité** du bastion (cluster multi-instances) et backend
  d'audit externalisé.
- **Certificat TLS** de production (ACME ou PKI interne) en lieu et place du
  certificat auto-signé.
```
