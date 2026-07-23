#!/usr/bin/env bash
# Librarium one-shot launcher.
#
#   ./run.sh                build + start the whole stack (db, api, spa)
#   ./run.sh down | --down  stop and remove the containers
#   ./run.sh logs           follow the container logs
#
# To stop the stack without this script: `docker compose down`
# (add `-v` to also drop the database volume and start fresh next time).
#
# It prepares the three .env files from their .env.example templates, fills in a
# Laravel APP_KEY, maps the public hostname in /etc/hosts, then brings the
# Docker containers up and waits until the app is served.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

# --- pretty output -----------------------------------------------------------
info()  { printf '\033[1;34m›\033[0m %s\n' "$*"; }
ok()    { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn()  { printf '\033[1;33m!\033[0m %s\n' "$*"; }
die()   { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

# --- prerequisites -----------------------------------------------------------
command -v docker >/dev/null 2>&1 || die "docker is not installed."
if docker compose version >/dev/null 2>&1; then
    COMPOSE=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE=(docker-compose)
else
    die "docker compose (v2) or docker-compose is required."
fi

# --- sub-commands ------------------------------------------------------------
case "${1:-up}" in
    down|--down|-d) info "Stopping the stack…"; "${COMPOSE[@]}" down; ok "Stopped."; exit 0 ;;
    logs|--logs) exec "${COMPOSE[@]}" logs -f ;;
    up|"") ;;
    *) die "Unknown command '$1' (use: up | down | logs)." ;;
esac

# --- .env preparation --------------------------------------------------------
# ensure_env <example-file> <target-file>
ensure_env() {
    local example="$1" target="$2"
    [ -f "$example" ] || die "Missing template: $example"
    if [ -f "$target" ]; then
        ok "$target already present."
    else
        cp "$example" "$target"
        ok "Created $target from $(basename "$example")."
    fi
}

info "Preparing environment files…"
ensure_env ".env.example"          ".env"
ensure_env "backend/.env.example"  "backend/.env"
ensure_env "frontend/.env.example" "frontend/.env"

# --- APP_KEY -----------------------------------------------------------------
# shellcheck disable=SC1091
set -a; . ./.env; set +a

if [ -z "${APP_KEY:-}" ]; then
    NEW_KEY="base64:$(openssl rand -base64 32)"
    # Replace an existing APP_KEY= line, or append one.
    if grep -q '^APP_KEY=' .env; then
        # Use a temp file so the sed delimiter never clashes with the key.
        awk -v k="$NEW_KEY" 'BEGIN{FS=OFS="="} /^APP_KEY=/{print "APP_KEY=" k; next} {print}' .env > .env.tmp
        mv .env.tmp .env
    else
        printf 'APP_KEY=%s\n' "$NEW_KEY" >> .env
    fi
    APP_KEY="$NEW_KEY"
    ok "Generated APP_KEY."
else
    ok "APP_KEY already set."
fi

APP_HOST="${APP_HOST:-teachassignment.local}"
APP_PORT="${APP_PORT:-8080}"

# --- hosts entry -------------------------------------------------------------
# Map APP_HOST -> 127.0.0.1 in a given hosts file. Args: <hosts-file> <label>.
map_host_in() {
    local hosts="$1" label="$2" line="127.0.0.1 ${APP_HOST}"
    if grep -qE "^[^#]*[[:space:]]${APP_HOST}(\$|[[:space:]])" "$hosts" 2>/dev/null; then
        ok "${label} already maps ${APP_HOST}."
        return 0
    fi
    if [ -w "$hosts" ]; then
        printf '%s\n' "$line" >> "$hosts" && { ok "Added ${APP_HOST} to ${label}."; return 0; }
    fi
    if command -v sudo >/dev/null 2>&1 && printf '%s\n' "$line" | sudo tee -a "$hosts" >/dev/null 2>&1; then
        ok "Added ${APP_HOST} to ${label}."
        return 0
    fi
    return 1
}

map_host_in /etc/hosts "/etc/hosts" || \
    warn "Could not edit /etc/hosts — reach the app at http://localhost:${APP_PORT}."

# On WSL2 the browser runs on Windows, which ignores the Linux /etc/hosts. The
# name only resolves once it is in the Windows hosts file too.
if grep -qi microsoft /proc/version 2>/dev/null; then
    WIN_HOSTS="/mnt/c/Windows/System32/drivers/etc/hosts"
    if ! map_host_in "$WIN_HOSTS" "the Windows hosts file"; then
        warn "WSL2 detected: ${APP_HOST} is not in the Windows hosts file, so the"
        warn "Windows browser cannot resolve it. Either use http://localhost:${APP_PORT}"
        warn "instead, or add the entry from an *Administrator* PowerShell:"
        printf '\n    Add-Content -Path "$env:windir\\System32\\drivers\\etc\\hosts" -Value "127.0.0.1 %s"\n\n' "${APP_HOST}"
    fi
fi

# --- launch ------------------------------------------------------------------
info "Building and starting the containers (first run pulls images and builds)…"
"${COMPOSE[@]}" up -d --build

# --- wait for the API to be healthy -----------------------------------------
info "Waiting for the API to come up (migrations and seeding run on first boot)…"
deadline=$(( $(date +%s) + 180 ))
until curl -fsS "http://localhost:${BACKEND_PORT:-8000}/up" >/dev/null 2>&1; do
    if [ "$(date +%s)" -ge "$deadline" ]; then
        warn "API did not report healthy in time. Check: ./run.sh logs"
        break
    fi
    sleep 3
done
ok "Stack is up."

printf '\n'
ok  "Librarium is served at:  http://${APP_HOST}:${APP_PORT}"
info "  …or, always working:   http://localhost:${APP_PORT}"
info "Storybook:               http://localhost:${APP_PORT}/storybook/"
info "API (direct):            http://localhost:${BACKEND_PORT:-8000}/api/v1"
info "Follow logs with:        ./run.sh logs"
info "Stop everything with:    ./run.sh down"
