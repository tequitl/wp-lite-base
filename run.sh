#!/usr/bin/env bash
# Run PHP server and expose via ngrok on Ubuntu
set -euo pipefail

WP_DIR="${WP_DIR:-/root/wp-net-base}"
PORT="${PORT:-8080}"
DOMAIN="${DOMAIN:-wp-net.ngrok.io}"
LOG_DIR="${LOG_DIR:-/var/log/wp-net}"

mkdir -p "$LOG_DIR" || true

# Require dependencies
command -v php >/dev/null 2>&1 || { echo "[ERROR] php not found"; exit 1; }
command -v ngrok >/dev/null 2>&1 || { echo "[ERROR] ngrok not found"; exit 1; }

# Graceful cleanup
cleanup() {
  echo "[INFO] Stopping child processes..."
  [[ -n "${PHP_PID:-}" ]] && kill "$PHP_PID" >/dev/null 2>&1 || true
  [[ -n "${NGROK_PID:-}" ]] && kill "$NGROK_PID" >/dev/null 2>&1 || true
}
trap cleanup INT TERM

# Check if port is free
if command -v ss >/dev/null 2>&1 && ss -lntp | awk -v p=":$PORT" '$4 ~ p {found=1} END {exit !found}'; then
  echo "[ERROR] Port $PORT is in use"
  exit 1
fi

echo "[INFO] Starting PHP server on 127.0.0.1:${PORT} (docroot: $WP_DIR)"
php -S "127.0.0.1:${PORT}" -t "$WP_DIR" >> "$LOG_DIR/app.log" 2>> "$LOG_DIR/app.err" &
PHP_PID=$!

# Give PHP a moment
sleep 1

echo "[INFO] Starting ngrok for domain ${DOMAIN}"
ngrok http --domain="$DOMAIN" "$PORT" >> "$LOG_DIR/ngrok.log" 2>> "$LOG_DIR/app.err" &
NGROK_PID=$!

# Wait on ngrok (keeps this script running as a daemon)
wait "$NGROK_PID"
