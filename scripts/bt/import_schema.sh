#!/usr/bin/env bash
set -euo pipefail

# Imports database/setup.sql into MySQL (BT Panel environment)
# Usage: ./scripts/bt/import_schema.sh user password database

DB_USER="${1:-taisafe}"
DB_PASS="${2:-ChangeMe123!}"
DB_NAME="${3:-taisafe_cdk}"
SQL_FILE="$(cd "$(dirname "$0")/.." && pwd)/database/setup.sql"

if [ ! -f "$SQL_FILE" ]; then
  echo "Schema file not found: $SQL_FILE" >&2
  exit 1
fi

echo "Creating database $DB_NAME (if not exists)..."
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importing schema..."
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

echo "Done."
