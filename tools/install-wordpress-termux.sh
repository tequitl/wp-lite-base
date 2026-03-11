#!/bin/bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== WordPress on Termux: Interactive Install ===${NC}"

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

PWD_DIR="$(pwd)"
DEFAULT_DIR="$PWD_DIR/wordpress"
TARGET_DIR="$(prompt "Install directory" "$DEFAULT_DIR")"

DB_ROOT_PASSWORD="$(prompt_secret "MariaDB root password")"
DB_NAME="$(prompt "Database name" "wordpress")"
DB_USER="$(prompt "Database user" "wpuser")"
DB_PASSWORD="$(prompt_secret "Database user password")"

SITE_URL="$(prompt "Site URL" "http://127.0.0.1:8080")"
SITE_TITLE="$(prompt "Site title" "My WordPress")"
ADMIN_USER="$(prompt "Admin user" "admin")"
ADMIN_PASSWORD="$(prompt_secret "Admin password")"
ADMIN_EMAIL="$(prompt "Admin email" "admin@example.com")"

START_SERVER="$(prompt "Start PHP dev server after install? (y/n)" "y")"
SERVER_PORT="$(prompt "PHP dev server port" "8080")"

PREFIX="${PREFIX:-/data/data/com.termux/files/usr}"
BIN_DIR="$PREFIX/bin"
DATA_DIR="$PREFIX/var/lib/mysql"
RUN_DIR="$PREFIX/var/run/mysqld"
SOCKET="$RUN_DIR/mysqld.sock"
PID_FILE="$RUN_DIR/mysqld.pid"

echo -e "${YELLOW}Installing packages...${NC}"
pkg update -y >/dev/null 2>&1 || true
pkg install -y php php-mysqli php-gd php-curl php-zip php-mbstring php-xml wget unzip mariadb tar >/dev/null 2>&1

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

if [ -d "$TARGET_DIR" ] && [ -n "$(ls -A "$TARGET_DIR" 2>/dev/null)" ]; then
  echo -e "${YELLOW}Target directory exists and is not empty. Using it as is.${NC}"
else
  mkdir -p "$TARGET_DIR"
  rm -rf "$TARGET_DIR"/* 2>/dev/null || true
  mv "$EXTRACTED_DIR"/* "$TARGET_DIR"/
fi
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