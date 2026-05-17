#!/bin/sh
set -e

# =============================================================================
# Entrypoint — amazingsite-erp
# Ejecuta migraciones, seeders y cacheo antes de arrancar el servicio.
# Se usa tanto para php-fpm como para worker/scheduler via $1.
# =============================================================================

# Solo ejecutar setup en el contenedor principal (php-fpm), no en worker/scheduler
run_setup() {
    echo "[entrypoint] Ensuring storage directories..."
    mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/puppeteer_profile bootstrap/cache 2>/dev/null || true
    mkdir -p public/uploads public/carrier public/invoice public/database-backup 2>/dev/null || true
    # Corregir ownership en bind mounts: Docker crea directorios del host como root
    chown -R www-data:www-data storage bootstrap/cache public/uploads public/carrier public/invoice public/database-backup 2>/dev/null || true

    # Esperar a que la BD esté disponible (hasta 30s)
    echo "[entrypoint] Waiting for database..."
    RETRIES=30
    until php artisan db:monitor --databases=mysql > /dev/null 2>&1 || [ "$RETRIES" -eq 0 ]; do
        RETRIES=$((RETRIES - 1))
        sleep 1
    done

    if [ "$RETRIES" -eq 0 ]; then
        echo "[entrypoint] WARNING: Database not reachable after 30s, continuing anyway..."
    fi

    echo "[entrypoint] Running migrations..."
    php artisan migrate --force || echo "[entrypoint] WARNING: Migration had issues"
    php artisan module:migrate --force || echo "[entrypoint] WARNING: Module migration had issues"

    # Seeders: seed:deploy para producción, seed:staging para desarrollo/local
    # Replica el comportamiento del Jenkinsfile (main → seed:deploy, develop → seed:staging)
    echo "[entrypoint] Running seeders (APP_ENV=${APP_ENV:-local})..."
    if [ "${APP_ENV}" = "production" ]; then
        php artisan seed:deploy --force || echo "[entrypoint] WARNING: seed:deploy had issues"
    else
        php artisan seed:staging --force || echo "[entrypoint] WARNING: seed:staging had issues"
    fi

    echo "[entrypoint] Caching configuration..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    echo "[entrypoint] Setup complete."
}

case "${1}" in
    php-fpm)
        # En desarrollo (SKIP_SETUP=true) saltar migraciones/seeders/cache
        # para que php-fpm arranque de inmediato. Ejecuta manualmente:
        #   docker compose exec app php artisan migrate
        if [ "${SKIP_SETUP}" = "true" ]; then
            echo "[entrypoint] SKIP_SETUP=true — skipping migrations/seeders/cache"
            mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/puppeteer_profile bootstrap/cache 2>/dev/null || true
            mkdir -p public/uploads public/carrier public/invoice public/database-backup 2>/dev/null || true
            chown -R www-data:www-data storage bootstrap/cache public/uploads public/carrier public/invoice public/database-backup 2>/dev/null || true
        else
            run_setup
        fi
        echo "[entrypoint] Starting php-fpm..."
        exec php-fpm
        ;;
    worker)
        echo "[entrypoint] Starting queue worker..."
        exec php artisan queue:work redis \
            --sleep=3 \
            --tries=3 \
            --max-time=3600 \
            --max-jobs=1000 \
            --memory=256
        ;;
    scheduler)
        echo "[entrypoint] Starting scheduler loop..."
        while true; do
            php artisan schedule:run --no-interaction >> /dev/null 2>&1
            sleep 60
        done
        ;;
    *)
        # Cualquier otro comando (ej: artisan, sh, bash)
        exec "$@"
        ;;
esac
