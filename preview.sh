#!/usr/bin/env bash
set -euo pipefail

PORT="${1:-8080}"
ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Starting PHP built-in server on http://127.0.0.1:${PORT}"
echo "Docroot: ${ROOT_DIR}/public"
php -S "127.0.0.1:${PORT}" -t "${ROOT_DIR}/public"
