#!/bin/bash
set -e

cd /var/www/html

# создаём .env из .env.example, если его нет
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    echo "[entrypoint] .env not found, creating from .env.example"
    cp .env.example .env
fi

# фиксим права на storage и cache
if [ -d "storage" ] && [ -d "bootstrap/cache" ]; then
    echo "[entrypoint] Fixing permissions for storage and cache..."
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwx storage bootstrap/cache
fi

# фиксим права на sqlite (database/database.sqlite)
if [ -d "database" ]; then
    # если файла ещё нет - создадим пустой
    if [ ! -f "database/database.sqlite" ]; then
        echo "[entrypoint] Creating empty sqlite database file..."
        touch database/database.sqlite
    fi

    echo "[entrypoint] Fixing permissions for sqlite database..."
    chown www-data:www-data database/database.sqlite
    chmod 664 database/database.sqlite
fi

echo "[entrypoint] Starting php-fpm..."
exec "$@"
