#!/usr/bin/env bash
#
# Déploiement local de la pile UpcycleConnect (mysql + api Go + frontend PHP +
# Caddy + adminer) via Docker Compose. Remplace start.bat, qui visait l'ancienne
# architecture (binaire app.exe + MAMP) et ne correspond plus à la pile Docker.
#
# Usage : ./install.sh
#
set -euo pipefail

cd "$(dirname "$0")"

# --- Couleurs (désactivées si la sortie n'est pas un terminal) ----------------
if [ -t 1 ]; then
  RED=$'\033[31m'; GREEN=$'\033[32m'; YELLOW=$'\033[33m'; BOLD=$'\033[1m'; RESET=$'\033[0m'
else
  RED=''; GREEN=''; YELLOW=''; BOLD=''; RESET=''
fi
info()  { printf '%s\n' "${GREEN}==>${RESET} $*"; }
warn()  { printf '%s\n' "${YELLOW}/!\\${RESET} $*" >&2; }
fail()  { printf '%s\n' "${RED}ERREUR :${RESET} $*" >&2; exit 1; }

# --- 1. Prérequis : Docker + Compose v2 ---------------------------------------
command -v docker >/dev/null 2>&1 || fail "Docker n'est pas installé. Voir https://docs.docker.com/engine/install/"
docker compose version >/dev/null 2>&1 || fail "Le plugin 'docker compose' (v2) est absent. Installe docker-compose-plugin."
docker info >/dev/null 2>&1 || fail "Le démon Docker ne répond pas (droits ? service arrêté ?)."

# --- 2. Bootstrap du fichier .env ---------------------------------------------
# Compose interpole ${VAR} depuis .env : sans ce fichier, rien ne démarre.
if [ ! -f .env ]; then
  [ -f .env.example ] || fail ".env et .env.example sont tous deux absents."
  cp .env.example .env
  # Génère d'office un secret JWT fort pour éviter un démarrage sur placeholder.
  if command -v openssl >/dev/null 2>&1; then
    secret="$(openssl rand -hex 32)"
    # Échappe les caractères spéciaux sed (le hex n'en contient pas, mais on reste prudent).
    sed -i "s|^JWT_SECRET=.*|JWT_SECRET=${secret}|" .env
    info "Secret JWT généré automatiquement (openssl rand -hex 32)."
  fi
  warn ".env créé depuis .env.example."
  warn "Renseigne MYSQL_ROOT_PASSWORD, DB_DSN (même mot de passe) et STRIPE_SECRET_KEY,"
  warn "puis relance ./install.sh."
  exit 0
fi

# --- 3. Garde-fou sécurité : pas de secret placeholder en clair ----------------
# Charge .env de façon sûre (sans exécuter d'éventuelles substitutions).
get_env() { grep -E "^$1=" .env | head -n1 | cut -d= -f2-; }
jwt="$(get_env JWT_SECRET)"
case "$jwt" in
  ''|*change-me*|change-me-to-a-long-random-string)
    fail "JWT_SECRET est vide ou laissé à la valeur d'exemple. Mets une chaîne aléatoire (ex: openssl rand -hex 32) dans .env."
    ;;
esac
mysql_pw="$(get_env MYSQL_ROOT_PASSWORD)"
case "$mysql_pw" in
  ''|*change-me*) warn "MYSQL_ROOT_PASSWORD est encore une valeur d'exemple — à durcir avant toute mise en ligne." ;;
esac

# --- 4. Construction + démarrage ----------------------------------------------
info "Construction des images et démarrage de la pile (docker compose up -d --build)…"
docker compose up -d --build

# --- 5. Attente de la disponibilité de MySQL ----------------------------------
info "Attente de la santé de MySQL…"
mysql_cid="$(docker compose ps -q mysql)"
[ -n "$mysql_cid" ] || fail "Conteneur mysql introuvable après 'up'."
deadline=$(( $(date +%s) + 120 ))
until [ "$(docker inspect -f '{{.State.Health.Status}}' "$mysql_cid" 2>/dev/null)" = "healthy" ]; do
  [ "$(date +%s)" -lt "$deadline" ] || fail "MySQL n'est pas devenu 'healthy' en 120 s. Voir : docker compose logs mysql"
  sleep 3
done
info "MySQL est prêt."

# --- 6. Vérification HTTP via Caddy -------------------------------------------
# L'API n'expose pas de port publié ; on valide la chaîne complète par Caddy (:80).
info "Vérification de la réponse HTTP du frontend (via Caddy :80)…"
code=""
deadline=$(( $(date +%s) + 60 ))
while [ "$(date +%s)" -lt "$deadline" ]; do
  code="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null || true)"
  case "$code" in 2*|3*) break ;; esac
  sleep 2
done
case "$code" in
  2*|3*) info "Frontend joignable (HTTP $code)." ;;
  *)     warn "Le frontend n'a pas répondu en HTTP 2xx/3xx (dernier code: ${code:-aucun}). Voir : docker compose logs caddy frontend api" ;;
esac

# --- 7. Récapitulatif ---------------------------------------------------------
printf '\n%s\n' "${BOLD}UpcycleConnect est démarré.${RESET}"
echo "  • Application   : http://localhost/   (et l'URL publique définie par APP_URL)"
echo "  • Adminer (BDD) : http://127.0.0.1:8081/   (serveur: mysql)"
echo
echo "  Logs en direct : docker compose logs -f"
echo "  Arrêt          : docker compose down       (ajouter -v pour purger la base)"
