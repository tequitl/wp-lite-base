#!/bin/bash
set -e
export LC_ALL=C
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== WordPress on macOS (Homebrew): Interactive Install/Run ===${NC}"

prompt() {
  local msg="$1"; local def="$2"; local var
  if [ -n "$def" ]; then
    read -r -p "$msg [$def]: " var
    if [ -z "$var" ]; then var="$def"; fi
  else
    read -r -p "$msg: " var
  fi
  echo "$var"
}

prompt_secret() {
  local msg="$1"; local var
  while true; do
    read -s -p "$msg: " var; echo
    read -s -p "Confirm: " confirm; echo
    if [ "$var" = "$confirm" ] && [ -n "$var" ]; then
      echo "$var"
      return 0
    fi
    echo -e "${RED}Passwords do not match or empty. Try again.${NC}"
  done
}

is_wp_dir() {
  local dir="$1"
  [ -f "$dir/wp-load.php" ] && [ -d "$dir/wp-admin" ] && [ -d "$dir/wp-includes" ]
}

is_yes() {
  case "$1" in
    [Yy]|[Yy][Ee][Ss]) return 0 ;;
    *) return 1 ;;
  esac
}

ensure_brew() {
  if ! command -v brew >/dev/null 2>&1; then
    echo -e "${YELLOW}Installing Homebrew...${NC}"
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    eval "$((/usr/bin/arch -arm64 2>/dev/null && echo /opt/homebrew/bin/brew) || echo /usr/local/bin/brew) shellenv" || true
    echo -e "${YELLOW}Homebrew installed. Restart your terminal to apply PATH changes, then re-run this script.${NC}"
    exit 0
  fi
}

ensure_brew_pkg() {
  local pkg="$1"
  if ! brew list --versions "$pkg" >/dev/null 2>&1; then
    echo -e "${YELLOW}Installing $pkg...${NC}"
    brew install "$pkg"
  fi
}

ensure_cmd() {
  local cmd="$1"; local pkg="$2"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    ensure_brew_pkg "$pkg"
  fi
}

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CRED_FILE="$SCRIPT_DIR/credentials"

load_credentials() { if [ -f "$CRED_FILE" ]; then . "$CRED_FILE"; fi; }
save_credentials() {
  umask 077
  cat > "$CRED_FILE" <<EOF
DB_ROOT_PASSWORD='${DB_ROOT_PASSWORD}'
DB_USER='${DB_USER}'
DB_PASSWORD='${DB_PASSWORD}'
EOF
}

echo -e "${YELLOW}Checking base dependencies...${NC}"
ensure_brew
brew update -q || true
ensure_cmd php php
ensure_cmd wget wget
ensure_cmd unzip unzip
ensure_cmd mariadb mariadb

PWD_DIR="$(pwd)"
RUN_BASE_DIR="$(prompt "Folder to run" "$PWD_DIR")"
if [ ! -d "$RUN_BASE_DIR" ]; then mkdir -p "$RUN_BASE_DIR"; fi

if is_wp_dir "$RUN_BASE_DIR"; then
  START_SERVER="$(prompt "Start PHP dev server now? (y/n)" "y")"
  SERVER_PORT="$(prompt "PHP dev server port" "8080")"
  if is_yes "$START_SERVER"; then
    echo -e "${YELLOW}Starting PHP dev server on 127.0.0.1:${SERVER_PORT}${NC}"
    php -S 127.0.0.1:"$SERVER_PORT" -t "$RUN_BASE_DIR"
  else
    echo -e "${YELLOW}You can start the server with:${NC}"
    echo "php -S 127.0.0.1:$SERVER_PORT -t '$RUN_BASE_DIR'"
  fi
  exit 0
fi

load_credentials

SITE_NAME="$(prompt "Site name (used for new folder)" "mysite")"
SITE_SLUG=$(echo "$SITE_NAME" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//')
if [ -z "$SITE_SLUG" ]; then SITE_SLUG="mysite"; fi
TARGET_DIR="$RUN_BASE_DIR/$SITE_SLUG"

if [ -z "${DB_ROOT_PASSWORD:-}" ]; then DB_ROOT_PASSWORD="$(prompt_secret "MariaDB root password")"; fi
if [ -z "${DB_NAME:-}" ]; then DB_NAME="$(prompt "Database name" "${SITE_SLUG//-/_}")"; fi
if [ -z "${DB_USER:-}" ]; then DB_USER="$(prompt "Database user" "wpuser")"; fi
if [ -z "${DB_PASSWORD:-}" ]; then DB_PASSWORD="$(prompt_secret "Database user password")"; fi
save_credentials

APPLY_SQLITE="$(prompt "Apply SQLite Database Integration? (recommended portable) (y/n)" "y")"
SITE_TITLE="$(prompt "Site title" "$SITE_NAME")"
START_SERVER="$(prompt "Start PHP dev server after install? (y/n)" "y")"
SERVER_PORT="$(prompt "PHP dev server port" "8080")"
SITE_URL="$(prompt "Site URL" "http://127.0.0.1:$SERVER_PORT")"
ADMIN_USER="$(prompt "Admin user (press Enter for 'admin')" "admin")"
ADMIN_PASSWORD_INPUT="$(prompt "Admin password (Enter to auto-generate)" "")"
if [ -z "$ADMIN_PASSWORD_INPUT" ]; then
  ADMIN_PASSWORD="$(head /dev/urandom | tr -dc 'a-zA-Z0-9' | head -c 5)"
  ADMIN_PASSWORD_AUTO=true
else
  ADMIN_PASSWORD="$ADMIN_PASSWORD_INPUT"
  ADMIN_PASSWORD_AUTO=false
fi
ADMIN_EMAIL="$(prompt "Admin email" "admin@example.com")"

echo -e "${YELLOW}Starting MariaDB...${NC}"
brew services start mariadb >/dev/null 2>&1 || true
if command -v mysql.server >/dev/null 2>&1; then mysql.server start >/dev/null 2>&1 || true; fi

echo -e "${YELLOW}Creating database and user...${NC}"
if mysql -u root -p"${DB_ROOT_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; then
  mysql -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  mysql -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
  mysql -u root -p"${DB_ROOT_PASSWORD}" -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"
else
  echo -e "${RED}Cannot authenticate to MariaDB with provided root password${NC}"; exit 1
fi

echo -e "${YELLOW}Downloading WordPress...${NC}"
WP_ARCHIVE="latest.tar.gz"
if [ ! -f "$WP_ARCHIVE" ]; then
  wget -q https://wordpress.org/latest.tar.gz -o /dev/null -O "$WP_ARCHIVE"
fi
TMP_DIR="$(mktemp -d)"
tar -xzf "$WP_ARCHIVE" -C "$TMP_DIR"
EXTRACTED_DIR="$TMP_DIR/wordpress"
mkdir -p "$TARGET_DIR"
rm -rf "$TARGET_DIR"/* 2>/dev/null || true
mv "$EXTRACTED_DIR"/* "$TARGET_DIR"/
rm -rf "$TMP_DIR"

echo -e "${YELLOW}Ensuring WP-CLI...${NC}"
if command -v wp >/dev/null 2>&1; then
  WP_CMD="wp"
else
  brew install wp-cli || true
  if command -v wp >/dev/null 2>&1; then
    WP_CMD="wp"
  else
    mkdir -p "$HOME/.local/bin"
    wget -q https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -O "$HOME/.local/bin/wp"
    chmod +x "$HOME/.local/bin/wp"
    WP_CMD="$HOME/.local/bin/wp"
  fi
fi

cd "$TARGET_DIR"
echo -e "${YELLOW}Generating wp-config.php...${NC}"
"$WP_CMD" config create \
  --dbname="$DB_NAME" \
  --dbuser="$DB_USER" \
  --dbpass="$DB_PASSWORD" \
  --dbhost="127.0.0.1" \
  --dbprefix="wp_" \
  --skip-check \
  --force

echo -e "${YELLOW}Installing WordPress core...${NC}"
"$WP_CMD" core install \
  --url="$SITE_URL" \
  --title="$SITE_TITLE" \
  --admin_user="$ADMIN_USER" \
  --admin_password="$ADMIN_PASSWORD" \
  --admin_email="$ADMIN_EMAIL" \
  --skip-email

if is_yes "$APPLY_SQLITE"; then
  echo -e "${YELLOW}Installing and activating SQLite Database Integration plugin...${NC}"
  "$WP_CMD" plugin install sqlite-database-integration --activate --force
  echo -e "${YELLOW}Placing db.php drop-in and creating database folder...${NC}"
  mkdir -p "wp-content/database"
  if [ -f "wp-content/plugins/sqlite-database-integration/db.php" ]; then
    cp -f "wp-content/plugins/sqlite-database-integration/db.php" "wp-content/db.php"
  elif [ -f "wp-content/plugins/sqlite-database-integration/db.copy" ]; then
    cp -f "wp-content/plugins/sqlite-database-integration/db.copy" "wp-content/db.php"
  else
    echo -e "${RED}db.php not found in plugin directory${NC}"; exit 1
  fi
fi

echo -e "${GREEN}WordPress installed in: $TARGET_DIR${NC}"
if is_yes "$START_SERVER"; then
  echo -e "${YELLOW}Admin user: ${ADMIN_USER}${NC}"
  echo -e "${YELLOW}Admin password: ${ADMIN_PASSWORD}${NC}"
  echo -e "${YELLOW}Starting PHP dev server on 127.0.0.1:${SERVER_PORT}${NC}"
  php -S 127.0.0.1:"$SERVER_PORT" -t "$TARGET_DIR"
else
  echo -e "${YELLOW}You can start the server with:${NC}"
  echo "php -S 127.0.0.1:$SERVER_PORT -t '$TARGET_DIR'"
fi
