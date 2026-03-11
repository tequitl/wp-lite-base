#!/bin/bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== WordPress on Termux: Interactive Install/Run ===${NC}"

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

ensure_cmd() {
  local cmd="$1"; local pkg="$2"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo -e "${YELLOW}Installing $pkg...${NC}"
    pkg install -y "$pkg" >/dev/null 2>&1
  fi
}

ensure_php_ext() {
  local ext="$1"; local pkg="$2"
  if php -m 2>/dev/null | grep -qi "^${ext}$"; then
    return 0
  else
    echo -e "${YELLOW}Installing $pkg...${NC}"
    pkg install -y "$pkg" >/dev/null 2>&1
  fi
}

is_wp_dir() {
  local dir="$1"
  [ -f "$dir/wp-load.php" ] && [ -d "$dir/wp-admin" ] && [ -d "$dir/wp-includes" ]
}

# credentials management
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CRED_FILE="$SCRIPT_DIR/credentials"

load_credentials() {
  if [ -f "$CRED_FILE" ]; then
    . "$CRED_FILE"
  fi
}

save_credentials() {
  umask 077
  cat > "$CRED_FILE" <<EOF
DB_ROOT_PASSWORD='${DB_ROOT_PASSWORD}'
DB_USER='${DB_USER}'
DB_PASSWORD='${DB_PASSWORD}'
EOF
}

 

echo -e "${YELLOW}Checking base dependencies...${NC}"
pkg update -y >/dev/null 2>&1 || true
ensure_cmd php php
ensure_cmd wget wget
ensure_cmd unzip unzip
ensure_cmd tar tar

load_credentials

PWD_DIR="$(pwd)"
RUN_BASE_DIR="$(prompt "Folder to run" "$PWD_DIR")"
if [ ! -d "$RUN_BASE_DIR" ]; then mkdir -p "$RUN_BASE_DIR"; fi

if is_wp_dir "$RUN_BASE_DIR"; then
  START_SERVER="$(prompt "Start PHP dev server now? (y/n)" "y")"
  SERVER_PORT="$(prompt "PHP dev server port" "8080")"
  if [ "${START_SERVER,,}" = "y" ] || [ "${START_SERVER,,}" = "yes" ]; then
    echo -e "${YELLOW}Starting PHP dev server on 127.0.0.1:${SERVER_PORT}${NC}"
    php -S 127.0.0.1:"$SERVER_PORT" -t "$RUN_BASE_DIR"
  else
    echo -e "${YELLOW}You can start the server with:${NC}"
    echo "php -S 127.0.0.1:$SERVER_PORT -t '$RUN_BASE_DIR'"
  fi
  exit 0
fi

SITE_NAME="$(prompt "Site name (used for new folder)" "mysite")"
SITE_SLUG=$(echo "$SITE_NAME" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//')
if [ -z "$SITE_SLUG" ]; then SITE_SLUG="mysite"; fi
TARGET_DIR="$RUN_BASE_DIR/$SITE_SLUG"

if [ -z "${DB_ROOT_PASSWORD:-}" ]; then DB_ROOT_PASSWORD="$(prompt_secret "MariaDB root password")"; fi
if [ -z "${DB_NAME:-}" ]; then DB_NAME="$(prompt "Database name" "${SITE_SLUG//-/_}")"; fi
if [ -z "${DB_USER:-}" ]; then DB_USER="$(prompt "Database user" "wpuser")"; fi
if [ -z "${DB_PASSWORD:-}" ]; then DB_PASSWORD="$(prompt_secret "Database user password")"; fi
save_credentials

SITE_TITLE="$(prompt "Site title" "$SITE_NAME")"
START_SERVER="$(prompt "Start PHP dev server after install? (y/n)" "y")"
SERVER_PORT="$(prompt "PHP dev server port" "8080")"
SITE_URL="$(prompt "Site URL" "http://127.0.0.1:$SERVER_PORT")"
ADMIN_USER="$(prompt "Admin user" "admin")"
ADMIN_PASSWORD="$(prompt_secret "Admin password")"
ADMIN_EMAIL="$(prompt "Admin email" "admin@example.com")"

PREFIX="${PREFIX:-/data/data/com.termux/files/usr}"
BIN_DIR="$PREFIX/bin"
DATA_DIR="$PREFIX/var/lib/mysql"
RUN_DIR="$PREFIX/var/run/mysqld"
SOCKET="$RUN_DIR/mysqld.sock"
PID_FILE="$RUN_DIR/mysqld.pid"

echo -e "${YELLOW}Ensuring required PHP extensions and MariaDB...${NC}"
ensure_php_ext mysqli php-mysqli
ensure_php_ext gd php-gd
ensure_php_ext curl php-curl
ensure_php_ext zip php-zip
ensure_php_ext mbstring php-mbstring
ensure_php_ext xml php-xml
ensure_cmd mysqld mariadb

mkdir -p "$RUN_DIR" "$DATA_DIR"

echo -e "${YELLOW}Initializing MariaDB...${NC}"
if [ ! -d "$DATA_DIR/mysql" ]; then
  if command -v mariadb-install-db >/dev/null 2>&1; then
    mariadb-install-db --basedir="$PREFIX" --datadir="$DATA_DIR" --user="$(whoami)" >/dev/null 2>&1
  elif command -v mysql_install_db >/dev/null 2>&1; then
    mysql_install_db --basedir="$PREFIX" --datadir="$DATA_DIR" --user="$(whoami)" >/dev/null 2>&1
  else
    echo -e "${RED}MariaDB init tool not found${NC}"; exit 1
  fi
fi

echo -e "${YELLOW}Starting MariaDB...${NC}"
if ! pgrep -f "mysqld" >/dev/null 2>&1; then
  mysqld_safe --datadir="$DATA_DIR" --socket="$SOCKET" --pid-file="$PID_FILE" --bind-address=127.0.0.1 >/dev/null 2>&1 &
fi

echo -e "${YELLOW}Waiting for MariaDB...${NC}"
for i in $(seq 1 60); do
  if mysqladmin --socket="$SOCKET" ping >/dev/null 2>&1; then break; fi
  sleep 1
done
if ! mysqladmin --socket="$SOCKET" ping >/dev/null 2>&1; then echo -e "${RED}MariaDB failed to start${NC}"; exit 1; fi

echo -e "${YELLOW}Securing MariaDB root...${NC}"
if mysql --socket="$SOCKET" -u root -e "SELECT 1;" >/dev/null 2>&1; then
  mysql --socket="$SOCKET" -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}'; FLUSH PRIVILEGES;" >/dev/null 2>&1 || true
fi

echo -e "${YELLOW}Creating database and user...${NC}"
if mysql --socket="$SOCKET" -u root -p"${DB_ROOT_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; then
  mysql --socket="$SOCKET" -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  mysql --socket="$SOCKET" -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
  mysql --socket="$SOCKET" -u root -p"${DB_ROOT_PASSWORD}" -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"
else
  echo -e "${RED}Cannot authenticate to MariaDB with provided root password${NC}"; exit 1
fi

echo -e "${YELLOW}Downloading WordPress...${NC}"
WP_ARCHIVE="latest.tar.gz"
if [ ! -f "$WP_ARCHIVE" ]; then
  wget -q https://wordpress.org/latest.tar.gz -O "$WP_ARCHIVE"
fi
TMP_DIR="$(mktemp -d)"
tar -xzf "$WP_ARCHIVE" -C "$TMP_DIR"
EXTRACTED_DIR="$TMP_DIR/wordpress"
mkdir -p "$TARGET_DIR"
rm -rf "$TARGET_DIR"/* 2>/dev/null || true
mv "$EXTRACTED_DIR"/* "$TARGET_DIR"/
rm -rf "$TMP_DIR"

echo -e "${YELLOW}Installing WP-CLI...${NC}"
if command -v wp >/dev/null 2>&1; then
  WP_CMD="wp"
else
  mkdir -p "$BIN_DIR"
  wget -q https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -O "$BIN_DIR/wp"
  chmod +x "$BIN_DIR/wp"
  WP_CMD="$BIN_DIR/wp"
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

echo -e "${GREEN}WordPress installed in: $TARGET_DIR${NC}"
if [ "${START_SERVER,,}" = "y" ] || [ "${START_SERVER,,}" = "yes" ]; then
  echo -e "${YELLOW}Starting PHP dev server on 127.0.0.1:${SERVER_PORT}${NC}"
  php -S 127.0.0.1:"$SERVER_PORT" -t "$TARGET_DIR"
else
  echo -e "${YELLOW}You can start the server with:${NC}"
  echo "php -S 127.0.0.1:$SERVER_PORT -t '$TARGET_DIR'"
fi