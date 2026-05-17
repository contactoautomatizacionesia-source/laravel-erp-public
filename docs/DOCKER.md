# Docker — Guía de desarrollo local

> Entorno Docker + WSL como **entorno principal de desarrollo** para AmazCart ERP.
> El código vive en WSL (`~/projects/amazingsite-erp`) y se monta con bind mount.
> MySQL se mantiene en **Laragon (host Windows)** — los contenedores se conectan vía `host.docker.internal`.

---

## Tabla de contenidos

- [Arquitectura del entorno](#arquitectura-del-entorno)
- [Requisitos previos](#requisitos-previos)
- [Configuración inicial (primera vez)](#configuración-inicial-primera-vez)
- [Flujo de trabajo diario](#flujo-de-trabajo-diario)
- [Comandos de uso frecuente](#comandos-de-uso-frecuente)
- [Reconstruir la imagen](#reconstruir-la-imagen)
- [Solución de problemas](#solución-de-problemas)

---

## Arquitectura del entorno

| Servicio            | Imagen                        | Puerto  | Descripción                        |
|---------------------|-------------------------------|---------|------------------------------------|
| `amazingsite-nginx` | `amazingsite-erp-nginx`       | `8080`  | Proxy reverso → php-fpm            |
| `amazingsite-app`   | `amazingsite-erp-app`         | 9000    | PHP-FPM (Laravel)                  |
| `amazingsite-worker`| `amazingsite-erp-app`         | —       | Queue worker (`php artisan queue:work`) |
| `amazingsite-scheduler` | `amazingsite-erp-app`     | —       | Cron scheduler (`php artisan schedule:run`) |
| `amazingsite-redis` | `redis:7-alpine`              | 6379    | Cache, queue, session              |

**Bind mounts relevantes (desde el repo WSL):**

| Host (WSL)                  | Contenedor                      |
|-----------------------------|---------------------------------|
| `./.env`                    | `/var/www/.env`                 |
| `./storage/app`             | `/var/www/storage/app`          |
| `./storage/logs`            | `/var/www/storage/logs`         |
| `./storage/framework`       | `/var/www/storage/framework`    |
| `./bootstrap/cache`         | `/var/www/bootstrap/cache`      |
| `./public/uploads`          | `/var/www/public/uploads`       |
| `./public/carrier`          | `/var/www/public/carrier`       |
| `./public/invoice`          | `/var/www/public/invoice`       |
| `./public/database-backup`  | `/var/www/public/database-backup` |

---

## Requisitos previos

- **Docker Desktop for Windows** con integración WSL2 habilitada para la distro Ubuntu
- **Laragon** corriendo en Windows con MySQL activo (la app se conecta vía `host.docker.internal`)
- **WSL2 (Ubuntu)** con el repo clonado en `~/projects/amazingsite-erp`
- Imagen construida localmente: `amazingsite-erp-app:latest` (ver [Reconstruir la imagen](#reconstruir-la-imagen))

---

## Configuración inicial (primera vez)

### 1. Copiar archivos de runtime

Estos directorios no están en git y deben copiarse desde un backup o servidor existente:

```bash
SRC="/mnt/c/Users/darwi/Downloads/amazingsite.igni-soft.com.tar/amazingsite.igni-soft.com"
DST="/home/darwi/projects/amazingsite-erp"

cp -r "$SRC/storage/app/."        "$DST/storage/app/"
cp -r "$SRC/storage/logs/."       "$DST/storage/logs/"
cp -r "$SRC/storage/framework/."  "$DST/storage/framework/"
cp -r "$SRC/bootstrap/cache/."    "$DST/bootstrap/cache/"
cp -r "$SRC/public/uploads/."     "$DST/public/uploads/"
cp -r "$SRC/public/carrier/."     "$DST/public/carrier/"
cp -r "$SRC/public/invoice/."     "$DST/public/invoice/"
```

> **Importante:** Si `bootstrap/cache` tiene archivos de root, ejecutar antes:
> ```bash
> sudo chown -R darwi:darwi /home/darwi/projects/amazingsite-erp/bootstrap/cache
> ```

### 2. Limpiar el bootstrap cache del servidor

Los archivos de `bootstrap/cache/config.php`, `routes-v7.php`, etc. copiados del servidor contienen rutas absolutas (`/www/wwwroot/...`) que rompen el contenedor. Eliminarlos para que Laravel los regenere:

```bash
rm -f bootstrap/cache/config.php \
      bootstrap/cache/routes-v7.php \
      bootstrap/cache/packages.php \
      bootstrap/cache/services.php \
      bootstrap/cache/events.php
```

### 3. Permisos del .env

El contenedor corre como `www-data` (uid 33). El `.env` debe ser escribible para que el panel admin pueda modificarlo:

```bash
chmod 666 .env
```

### 4. Construir la imagen y levantar

```bash
docker compose build
docker compose up -d
```

Verificar que todos los contenedores estén healthy:

```bash
docker compose ps
```

---

## Flujo de trabajo diario

### Antes de trabajar — verificar que Laragon esté activo

La app necesita MySQL desde Windows. Abrir Laragon y asegurarse de que MySQL esté corriendo.

### Levantar el entorno

```bash
cd ~/projects/amazingsite-erp
docker compose up -d
```

La app queda disponible en **http://localhost:8080**

### Detener el entorno

```bash
docker compose down
```

> `docker compose down` elimina los contenedores pero conserva los volúmenes y bind mounts (datos, uploads, logs).

### Aplicar cambios en docker-compose.yml o .env.docker

`restart` no aplica cambios de volúmenes ni variables de entorno. Usar `up` para recrear:

```bash
docker compose up -d --no-deps app worker scheduler
```

---

## Comandos de uso frecuente

### Ver logs en tiempo real

```bash
# Todos los servicios
docker compose logs -f

# Solo app
docker compose logs -f app

# Últimas 50 líneas del app
docker compose logs --tail=50 app
```

### Artisan

```bash
docker compose exec app php artisan <comando>

# Ejemplos:
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:status
docker compose exec app php artisan tinker
docker compose exec app php artisan route:list
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

### Migraciones y seeders

```bash
# Migraciones pendientes
docker compose exec app php artisan migrate

# Migraciones de módulos
docker compose exec app php artisan module:migrate

# Seeder específico
docker compose exec app php artisan db:seed --class=NombreSeeder
```

### Limpiar todos los cachés

```bash
docker compose exec app php artisan optimize:clear
```

### Shell en el contenedor

```bash
docker compose exec app bash
```

---

## Reconstruir la imagen

Necesario al cambiar `Dockerfile`, `composer.json`, o extensiones PHP:

```bash
docker compose build --no-cache app
docker compose up -d --no-deps app worker scheduler
```

Para subir la imagen al registry (CI/CD lo hace automáticamente, pero en manual):

```bash
docker build -t ghcr.io/daruinherreraigniweb/amazingsite-erp:latest .
docker push ghcr.io/daruinherreraigniweb/amazingsite-erp:latest
```

---

## Solución de problemas

### 502 Bad Gateway

nginx no puede conectar con php-fpm. Revisar logs del app:

```bash
docker compose logs --tail=30 app
```

Causas comunes:
- **`bootstrap/cache/config.php` con rutas del servidor** (`/www/wwwroot/...`): eliminar los archivos de caché (ver paso 2 de configuración inicial) y reiniciar.
- **php-fpm no arrancó**: verificar que el entrypoint completó sin errores (`[entrypoint] Starting php-fpm...`).

### `file_get_contents(/var/www/.env): No such file or directory`

El `.env` no está montado en el contenedor. Verificar que el bind mount existe en `docker-compose.yml` para el servicio `app`:

```yaml
- ./.env:/var/www/.env
```

Luego recrear el contenedor:
```bash
docker compose up -d --no-deps app worker scheduler
```

### `file_put_contents(/var/www/.env): Permission denied`

El contenedor corre como `www-data` y no puede escribir el `.env`. Solución:

```bash
chmod 666 .env
```

### `mount src=...: no such file or directory` al hacer `docker compose up`

Error de Docker Desktop con bind mounts en WSL2 (hash caching). Solución:

```bash
docker compose down && docker compose up -d
```

Si persiste, reiniciar Docker Desktop desde la bandeja del sistema de Windows.

### Error de base de datos (`SQLSTATE[HY000] [2002] Network is unreachable`)

Laragon/MySQL no está corriendo en Windows, o `DB_HOST` no apunta a `host.docker.internal`. Verificar en `.env`:

```
DB_HOST=host.docker.internal
```

Y confirmar que MySQL de Laragon esté activo.
