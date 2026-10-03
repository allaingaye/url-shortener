#!/bin/sh
set -e

echo "▶ Entrypoint starting..."

# ── Wait for Redis ──
if [ -n "$REDIS_HOST" ]; then
    echo "▶ Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    until nc -z "${REDIS_HOST}" "${REDIS_PORT:-6379}" 2>/dev/null; do
        sleep 1
    done
    echo "✔ Redis is up"
fi

# ── Ensure storage is writable ──
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/api-docs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true

# ── Clear stale caches ──
if [ -f artisan ]; then
    php artisan config:clear  >/dev/null 2>&1 || true
    php artisan route:clear   >/dev/null 2>&1 || true
    php artisan view:clear    >/dev/null 2>&1 || true
fi

# ── Run migrations if requested ──
if [ "$RUN_MIGRATIONS" = "true" ] && [ -f artisan ]; then
    echo "▶ Running migrations..."
    php artisan migrate --force || echo "⚠ Migration failed (continuing)"
fi

echo "✔ Ready, handing off to: $@"
exec "$@"