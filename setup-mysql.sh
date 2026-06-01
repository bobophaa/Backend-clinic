#!/usr/bin/env bash
# Creates the MySQL database and runs Laravel migrations + seeders.
# Usage: ./setup-mysql.sh
# Set DB_PASSWORD in .env before running (or export MYSQL_PWD).

set -euo pipefail

cd "$(dirname "$0")"

if [[ ! -f .env ]]; then
  echo "Missing .env file. Copy .env.example to .env first."
  exit 1
fi

# shellcheck disable=SC1091
source <(grep -E '^DB_' .env | sed 's/^/export /')

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-clinic_sync}"
DB_USERNAME="${DB_USERNAME:-root}"

echo "Creating database '${DB_DATABASE}' if needed..."
if [[ -n "${DB_PASSWORD:-}" ]]; then
  export MYSQL_PWD="${DB_PASSWORD}"
fi

mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USERNAME}" -e \
  "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Running migrations and seeders..."
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force

echo "Done. MySQL database '${DB_DATABASE}' is ready."
