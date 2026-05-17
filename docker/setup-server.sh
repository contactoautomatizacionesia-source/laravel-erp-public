#!/bin/bash
set -euo pipefail

# =============================================================================
# setup-server.sh — Preparación inicial del servidor para amazingsite-erp
#
# Ejecutar UNA VEZ en cada servidor (staging y producción).
# Pre-requisitos: Docker + Docker Compose v2 instalados.
#
# Uso:
#   scp docker/setup-server.sh user@server:/tmp/
#   ssh user@server "bash /tmp/setup-server.sh"
#
# Variables de entorno requeridas (o se preguntan interactivamente):
#   DEPLOY_PATH    — Directorio del proyecto Docker (ej: /opt/amazingsite)
#   STORAGE_BASE   — Disco dedicado para datos (ej: /www/data-projects/amazingsite)
#   APP_ENV        — production | staging
#   GHCR_USER      — Usuario de GitHub para GHCR
#   GHCR_TOKEN     — PAT de GitHub con scope read:packages
# =============================================================================

echo "============================================"
echo "  amazingsite-erp — Server Setup"
echo "============================================"

# ── 1. Verificar Docker ─────────────────────────────────────────────────────
if ! command -v docker &>/dev/null; then
    echo "ERROR: Docker no está instalado. Instálalo primero:"
    echo "  curl -fsSL https://get.docker.com | sh"
    exit 1
fi

if ! docker compose version &>/dev/null; then
    echo "ERROR: Docker Compose v2 no disponible."
    echo "  Actualiza Docker o instala el plugin compose."
    exit 1
fi

echo "✓ Docker $(docker --version | grep -oP '\d+\.\d+\.\d+')"
echo "✓ $(docker compose version)"

# ── 2. Parámetros ───────────────────────────────────────────────────────────
read -rp "Directorio del proyecto Docker [/opt/amazingsite]: " DEPLOY_PATH
DEPLOY_PATH="${DEPLOY_PATH:-/opt/amazingsite}"

read -rp "Directorio de datos (disco dedicado) [${DEPLOY_PATH}]: " STORAGE_BASE
STORAGE_BASE="${STORAGE_BASE:-${DEPLOY_PATH}}"

read -rp "Entorno (production/staging) [staging]: " APP_ENV
APP_ENV="${APP_ENV:-staging}"

if [ "$APP_ENV" = "production" ]; then
    IMAGE_TAG="latest"
else
    IMAGE_TAG="develop-latest"
fi

REGISTRY="ghcr.io"
IMAGE_REPO="daruinherreraigniweb/amazingsite-erp"

# ── 3. Autenticar en GHCR ───────────────────────────────────────────────────
echo ""
echo "Autenticación en GitHub Container Registry..."
read -rp "GitHub username: " GHCR_USER
read -rsp "GitHub PAT (read:packages): " GHCR_TOKEN
echo ""
echo "$GHCR_TOKEN" | docker login "$REGISTRY" -u "$GHCR_USER" --password-stdin

# ── 4. Crear estructura de directorios ───────────────────────────────────────
echo ""
echo "Creando directorios..."
mkdir -p "$DEPLOY_PATH"
mkdir -p "$STORAGE_BASE"/{storage/app,storage/logs,storage/framework/{cache/data,sessions,views}}
mkdir -p "$STORAGE_BASE"/bootstrap/cache
mkdir -p "$STORAGE_BASE"/public/{uploads,carrier,invoice,database-backup}

# Permisos: www-data (UID 33) es el usuario dentro de los contenedores PHP
chown -R 33:33 "$STORAGE_BASE"/storage "$STORAGE_BASE"/bootstrap/cache
chown -R 33:33 "$STORAGE_BASE"/public/uploads "$STORAGE_BASE"/public/carrier
chown -R 33:33 "$STORAGE_BASE"/public/invoice "$STORAGE_BASE"/public/database-backup
chmod -R 775 "$STORAGE_BASE"/storage "$STORAGE_BASE"/bootstrap/cache

echo "✓ Directorios creados en $STORAGE_BASE"

# ── 5. Crear .env base ──────────────────────────────────────────────────────
ENV_FILE="$DEPLOY_PATH/.env"
if [ -f "$ENV_FILE" ]; then
    echo ""
    echo "⚠ $ENV_FILE ya existe. No se sobreescribe."
    echo "  Verifica que contenga APP_IMAGE, NGINX_IMAGE y STORAGE_BASE."
else
    cat > "$ENV_FILE" <<EOF
# =============================================================================
# .env — amazingsite-erp (${APP_ENV})
# Generado por setup-server.sh el $(date '+%Y-%m-%d %H:%M:%S')
# =============================================================================

# ── Laravel ──
APP_NAME=Amazingsite
APP_ENV=${APP_ENV}
APP_KEY=
APP_DEBUG=false
APP_URL=https://CHANGE_ME.igni-soft.com

# ── Docker Images (desde GHCR) ──
APP_IMAGE=${REGISTRY}/${IMAGE_REPO}:${IMAGE_TAG}
NGINX_IMAGE=${REGISTRY}/${IMAGE_REPO}:nginx-${IMAGE_TAG}

# ── Storage (disco dedicado para datos dinámicos) ──
STORAGE_BASE=${STORAGE_BASE}

# ── Puerto (80 para acceso directo, o detrás de reverse proxy) ──
APP_PORT=80

# ── Base de datos (MySQL en el host) ──
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=CHANGE_ME
DB_USERNAME=CHANGE_ME
DB_PASSWORD=CHANGE_ME

# ── Mail ──
MAIL_MAILER=smtp
MAIL_HOST=CHANGE_ME
MAIL_PORT=587
MAIL_USERNAME=CHANGE_ME
MAIL_PASSWORD=CHANGE_ME
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=CHANGE_ME
MAIL_FROM_NAME="\${APP_NAME}"
EOF

    echo "✓ Archivo .env creado en $ENV_FILE"
    echo "  ⚠ EDÍTALO para configurar APP_KEY, APP_URL, DB_*, MAIL_*"
fi

# ── 6. Crear .env.docker ────────────────────────────────────────────────────
cat > "$DEPLOY_PATH/.env.docker" <<EOF
# Docker-specific overrides (universal para todos los entornos)
DB_HOST=host.docker.internal
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CHROME_PATH=/usr/bin/google-chrome
EOF

echo "✓ .env.docker creado"

# ── 7. Pull de imágenes inicial ──────────────────────────────────────────────
echo ""
echo "Descargando imágenes desde GHCR..."
echo "  App:   ${REGISTRY}/${IMAGE_REPO}:${IMAGE_TAG}"
echo "  Nginx: ${REGISTRY}/${IMAGE_REPO}:nginx-${IMAGE_TAG}"

# El docker-compose.yml se sincronizará desde Jenkins en el primer deploy.
# Por ahora, pull manual:
docker pull "${REGISTRY}/${IMAGE_REPO}:${IMAGE_TAG}"
docker pull "${REGISTRY}/${IMAGE_REPO}:nginx-${IMAGE_TAG}"

echo "✓ Imágenes descargadas"

# ── 8. Resumen ───────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo "  Setup completo"
echo "============================================"
echo ""
echo "Directorio Docker:  $DEPLOY_PATH"
echo "Directorio datos:   $STORAGE_BASE"
echo "Entorno:            $APP_ENV"
echo "App Image:          ${REGISTRY}/${IMAGE_REPO}:${IMAGE_TAG}"
echo "Nginx Image:        ${REGISTRY}/${IMAGE_REPO}:nginx-${IMAGE_TAG}"
echo ""
echo "Próximos pasos:"
echo "  1. Editar $DEPLOY_PATH/.env (APP_KEY, APP_URL, DB_*, MAIL_*)"
echo "  2. Generar APP_KEY:"
echo "     docker run --rm ${REGISTRY}/${IMAGE_REPO}:${IMAGE_TAG} php artisan key:generate --show"
echo "  3. El primer deploy desde Jenkins copiará docker-compose.yml y levantará los servicios."
echo "  4. O levantar manualmente (si ya tienes docker-compose.yml):"
echo "     cd $DEPLOY_PATH && docker compose up -d"
echo ""
echo "Para migrar datos de producción existente:"
echo "  rsync -avz old-server:${DEPLOY_PATH}/storage/app/ ${STORAGE_BASE}/storage/app/"
echo "  rsync -avz old-server:${DEPLOY_PATH}/public/uploads/ ${STORAGE_BASE}/public/uploads/"
echo "  rsync -avz old-server:${DEPLOY_PATH}/public/carrier/ ${STORAGE_BASE}/public/carrier/"
echo "  rsync -avz old-server:${DEPLOY_PATH}/public/invoice/ ${STORAGE_BASE}/public/invoice/"
