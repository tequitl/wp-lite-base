#!/bin/bash
set -e

YELLOW='\033[1;33m'
NC='\033[0m'

prompt() { local msg="$1"; local def="$2"; local var; if [ -n "$def" ]; then read -r -p "$msg [$def]: " var; [ -z "$var" ] && var="$def"; else read -r -p "$msg: " var; fi; echo "$var"; }

ensure_cmd() { local cmd="$1"; local pkg="$2"; command -v "$cmd" >/dev/null 2>&1 || pkg install -y "$pkg" >/dev/null 2>&1; }

PREFIX="${PREFIX:-/data/data/com.termux/files/usr}"

ensure_npm() { command -v npm >/dev/null 2>&1 || pkg install -y nodejs >/dev/null 2>&1 || pkg install -y nodejs-lts >/dev/null 2>&1; }

ensure_localtunnel() {
  command -v lt >/dev/null 2>&1 && return 0
  ensure_npm
  npm config set prefix "$PREFIX" >/dev/null 2>&1 || true
  npm i -g localtunnel >/dev/null 2>&1 || npm i -g localtunnel || return 1
  local lt_dir
  lt_dir="$(npm root -g 2>/dev/null)/localtunnel"
  [ -f "$lt_dir/node_modules/openurl/openurl.js" ] && sed -i "s/throw new Error(.*process.platform.*)/module.exports.open=function(){}; module.exports.browser=function(){};/" "$lt_dir/node_modules/openurl/openurl.js" || true
}

ensure_cloudflared() {
  command -v cloudflared >/dev/null 2>&1 && return 0
  local arch asset url
  arch="$(uname -m)"
  case "$arch" in aarch64) asset="cloudflared-linux-arm64" ;; arm*) asset="cloudflared-linux-arm" ;; x86_64) asset="cloudflared-linux-amd64" ;; i686|i386) asset="cloudflared-linux-386" ;; *) exit 1 ;; esac
  url="https://github.com/cloudflare/cloudflared/releases/latest/download/${asset}"
  mkdir -p "$PREFIX/bin" && wget -q "$url" -O "$PREFIX/bin/cloudflared" && chmod +x "$PREFIX/bin/cloudflared"
}

PORT="$(prompt "Local port" "8080")"
KIND="$(prompt "Tunnel [cloudflared/ssh/localtunnel]" "cloudflared")"

case "${KIND,,}" in
  cloudflared)
    ensure_cloudflared
    echo -e "${YELLOW}Starting cloudflared...${NC}"
    cloudflared tunnel --url http://127.0.0.1:$PORT --no-autoupdate
    ;;
  ssh)
    ensure_cmd ssh openssh
    echo -e "${YELLOW}Starting SSH reverse tunnel...${NC}"
    ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=60 -R 80:localhost:$PORT nokey@localhost.run
    ;;
  localtunnel)
    ensure_localtunnel
    echo -e "${YELLOW}Starting localtunnel...${NC}"
    lt --port "$PORT"
    ;;
  *) echo "Unsupported tunnel"; exit 1 ;;
esac