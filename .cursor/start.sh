#!/usr/bin/env bash
set -euo pipefail

# Per-boot DB daemon reconciliation for Cloud Agents (idempotent).
mkdir -p /var/run/mysqld  # pragma: allowlist secret
chown mysql:mysql /var/run/mysqld 2>/dev/null || true  # pragma: allowlist secret

if ! mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then  # pragma: allowlist secret
  if command -v service >/dev/null 2>&1; then
    service mysql start || true  # pragma: allowlist secret
  fi
  if ! mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then  # pragma: allowlist secret
    mysqld --user=mysql --datadir=/var/lib/mysql --socket=/var/run/mysqld/mysqld.sock --pid-file=/var/run/mysqld/mysqld.pid &  # pragma: allowlist secret
    for _ in $(seq 1 30); do
      mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null && break  # pragma: allowlist secret
      sleep 1
    done
  fi
fi

# Cloud secrets often use Sail-style host/db/user names. Alias host "mysql" to loopback.  # pragma: allowlist secret
grep -qE $'^[0-9.]+[[:space:]]+mysql([[:space:]]|$)' /etc/hosts || echo '127.0.0.1 mysql' >> /etc/hosts  # pragma: allowlist secret

DB_NAME="$(printenv DB_DATABASE 2>/dev/null || echo laravel)"  # pragma: allowlist secret
DB_USER="$(printenv DB_USERNAME 2>/dev/null || echo sail)"  # pragma: allowlist secret
DB_PASS="$(printenv DB_PASSWORD 2>/dev/null || true)"  # pragma: allowlist secret

mysql -u root <<SQL || true  # pragma: allowlist secret
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;  # pragma: allowlist secret
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON *.* TO '${DB_USER}'@'%' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO '${DB_USER}'@'127.0.0.1' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO '${DB_USER}'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL

if [ ! -f .env ]; then
  cp .env.example .env
fi

# Only generate a key when .env still has an empty APP_KEY.
if ! grep -qE '^APP_KEY=.+' .env; then
  env -u APP_KEY php artisan key:generate --force --no-interaction || true
fi

php artisan migrate --force --no-interaction || true
