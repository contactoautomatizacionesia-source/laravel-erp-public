#!/bin/bash
# =============================================================================
# setup-wsl-dev.sh — Configura el entorno de desarrollo en WSL
#
# Ejecutar UNA SOLA VEZ después de clonar/mover el proyecto a WSL ext4:
#   cd ~/projects/amazingsite-erp
#   bash docker/setup-wsl-dev.sh
# =============================================================================
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== Setup WSL Development Environment ===${NC}"

# ── 1. Verificar que estamos en WSL ─────────────────────────────────────────
if ! grep -qi microsoft /proc/version 2>/dev/null; then
    echo -e "${RED}ERROR: Este script debe ejecutarse dentro de WSL (Ubuntu).${NC}"
    exit 1
fi

# ── 2. Verificar que el proyecto está en ext4 (no en /mnt/c/) ──────────────
PROJECT_DIR=$(pwd)
if [[ "$PROJECT_DIR" == /mnt/* ]]; then
    echo -e "${RED}ERROR: El proyecto está en $PROJECT_DIR (filesystem Windows NTFS).${NC}"
    echo -e "${YELLOW}Muévelo a ext4, por ejemplo:${NC}"
    echo "  mkdir -p ~/projects"
    echo "  cp -r $PROJECT_DIR ~/projects/amazingsite-erp"
    echo "  cd ~/projects/amazingsite-erp"
    echo "  bash docker/setup-wsl-dev.sh"
    exit 1
fi

# ── 3. Verificar Docker ────────────────────────────────────────────────────
if ! command -v docker &>/dev/null; then
    echo -e "${RED}ERROR: Docker no está disponible en WSL.${NC}"
    echo "Asegúrate de que Docker Desktop tiene habilitada la integración WSL:"
    echo "  Settings → Resources → WSL Integration → Enable Ubuntu"
    exit 1
fi

echo -e "${GREEN}✓${NC} Docker disponible: $(docker --version)"

# ── 4. Verificar que Laragon/MySQL está corriendo en Windows ────────────────
echo -n "Verificando MySQL en Windows (host.docker.internal:3306)... "
if docker run --rm --add-host=host.docker.internal:host-gateway mysql:8 \
    mysqladmin ping -h host.docker.internal --silent 2>/dev/null; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${YELLOW}⚠ No se pudo conectar. Asegúrate de que Laragon/MySQL esté corriendo.${NC}"
fi

# ── 5. Crear .env si no existe ──────────────────────────────────────────────
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}✓${NC} .env creado desde .env.example"
        echo -e "${YELLOW}  → Edita .env con tus credenciales de BD antes de continuar.${NC}"
    else
        echo -e "${RED}ERROR: No existe .env ni .env.example${NC}"
        exit 1
    fi
fi

# ── 6. Asegurar APP_PORT en .env ───────────────────────────────────────────
if ! grep -q "^APP_PORT=" .env; then
    echo "" >> .env
    echo "APP_PORT=8080" >> .env
    echo -e "${GREEN}✓${NC} APP_PORT=8080 agregado a .env"
else
    echo -e "${GREEN}✓${NC} APP_PORT ya configurado en .env"
fi

# ── 7. Fix line endings (Windows → Unix) ───────────────────────────────────
echo -n "Corrigiendo line endings en scripts Docker... "
if command -v dos2unix &>/dev/null; then
    find docker/ -type f \( -name "*.sh" -o -name "*.ini" -o -name "*.conf" \) -exec dos2unix -q {} +
    echo -e "${GREEN}✓${NC}"
else
    # Fallback sin dos2unix
    find docker/ -type f \( -name "*.sh" -o -name "*.ini" -o -name "*.conf" \) -exec sed -i 's/\r$//' {} +
    echo -e "${GREEN}✓${NC} (via sed)"
fi

# ── 8. Git config para este repo ──────────────────────────────────────────
git config core.autocrlf input 2>/dev/null || true
git config core.eol lf 2>/dev/null || true
echo -e "${GREEN}✓${NC} Git configurado: autocrlf=input, eol=lf"

# ── 9. Permisos de storage ─────────────────────────────────────────────────
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓${NC} Permisos de storage configurados"

# ── 10. Construir imagen ───────────────────────────────────────────────────
echo ""
echo -e "${YELLOW}Construyendo imagen Docker (primera vez tarda ~5-7 min)...${NC}"
docker compose build app

# ── 11. Levantar servicios en modo desarrollo ──────────────────────────────
echo ""
echo -e "${YELLOW}Levantando servicios en modo desarrollo...${NC}"
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# ── 12. Esperar a que app esté ready ───────────────────────────────────────
echo -n "Esperando que php-fpm esté listo... "
RETRIES=30
until docker compose exec -T app php -r "exit(0);" 2>/dev/null || [ "$RETRIES" -eq 0 ]; do
    RETRIES=$((RETRIES - 1))
    sleep 1
done

if [ "$RETRIES" -eq 0 ]; then
    echo -e "${RED}✗ Timeout${NC}"
else
    echo -e "${GREEN}✓${NC}"
fi

# ── 13. Ejecutar migraciones ──────────────────────────────────────────────
echo ""
echo -e "${YELLOW}Ejecutando migraciones...${NC}"
docker compose exec -T app php artisan migrate --force || echo -e "${YELLOW}⚠ Algunas migraciones tuvieron problemas${NC}"
docker compose exec -T app php artisan module:migrate --force || echo -e "${YELLOW}⚠ Algunas migraciones de módulos tuvieron problemas${NC}"

# ── 14. Configurar alias sugerido ──────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✓ Entorno de desarrollo listo!${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════════════${NC}"
echo ""
echo "  App:       http://localhost:8080"
echo "  Logs:      docker compose logs -f app"
echo ""
echo -e "${YELLOW}Alias recomendado (agregar a ~/.bashrc):${NC}"
echo ""
echo "  alias dc='docker compose -f docker-compose.yml -f docker-compose.dev.yml'"
echo ""
echo "  Uso:"
echo "    dc up -d                              # levantar"
echo "    dc exec app php artisan migrate       # migraciones"
echo "    dc exec app php artisan tinker        # tinker"
echo "    dc logs -f app                        # logs"
echo "    dc down                               # detener"
echo ""
