#!/bin/bash
set -e

echo "=== Starting Production Reverb WebSocket Service ==="

# 1. Ensure storage logs directory exists
mkdir -p storage/logs

# 2. Set runtime permissions for storage & cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

REVERB_PORT="${PORT:-8080}"
echo "Launching Laravel Reverb on 0.0.0.0:${REVERB_PORT}..."

exec php artisan reverb:start --host=0.0.0.0 --port="${REVERB_PORT}"
