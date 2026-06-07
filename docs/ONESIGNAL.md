# Notifications push (OneSignal)

Le push est **branché côté code** et **désactivé par défaut**. Sans clés, l'application
fonctionne normalement (les notifications restent visibles in-app : cloche pro,
page `/notifications` particulier). Pour activer le push temps réel :

## 1. Finir la configuration OneSignal (tableau de bord)
1. OneSignal > l'app « Upcycleconnect App » > **Continue Setup** > plateforme **Web**.
2. **Site URL** : `https://upcycleconnect.tech` (l'origine de production exacte, en HTTPS).
3. Laisser le **Default Notification Icon** / les réglages par défaut.
4. OneSignal sert son propre service worker, mais ce dépôt fournit déjà
   `frontend/public/OneSignalSDKWorker.js` à la racine du site
   (`https://.../OneSignalSDKWorker.js`) — ne rien changer côté chemins.

## 2. Récupérer les clés
OneSignal > **Settings > Keys & IDs** :
- **App ID** (public) → variable `ONESIGNAL_APP_ID`
- **REST API Key** (SECRET) → variable `ONESIGNAL_API_KEY`

## 3. Renseigner le `.env` (jamais committé)
```
ONESIGNAL_APP_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
ONESIGNAL_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
```
Puis recréer les conteneurs :
```
docker compose up -d --build api frontend
```
> Recréer `frontend` réinitialise les sessions PHP (`/tmp`) : tout le monde devra
> se reconnecter une fois.

## 4. Vérifier
- Ouvrir le site connecté → accepter l'invite d'abonnement du navigateur.
- OneSignal > **Audience** : l'abonné apparaît avec son **External ID = Id_Utilisateurs**.
- Admin > Notifications > envoyer un message → le push arrive sur le navigateur abonné.

## Comment ça marche
- **Frontend** (`layouts/main.php`, injecté seulement si `ONESIGNAL_APP_ID` est défini) :
  charge le SDK Web v16, `OneSignal.init({ appId })`, et `OneSignal.login(Id_Utilisateurs)`
  quand l'utilisateur est connecté → associe l'abonnement navigateur à son compte.
- **Backend** (`internal/notifier/onesignal.go`) : `SendPush(userIDs, contenu)` appelle
  l'API REST OneSignal en ciblant les `include_external_user_ids`. Appelé en goroutine
  depuis l'envoi admin (`AdminNotificationAction`) après l'écriture en base — donc
  in-app **et** push partent ensemble. No-op silencieux si les clés sont absentes.

## Sécurité
- `ONESIGNAL_APP_ID` est public (présent dans le HTML) : normal.
- `ONESIGNAL_API_KEY` est **secret** : utilisé uniquement côté API Go, jamais exposé au
  navigateur, jamais committé (`.env` est dans `.gitignore`).
