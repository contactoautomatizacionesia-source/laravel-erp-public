# AmazCart ERP

[![Quality Gate Status](https://sonar.igni-soft.com/api/project_badges/measure?project=Amazingsite-erp&metric=alert_status&token=sqb_f7f4048e7035371e29f28fc38fc464c069cd93a7)](https://sonar.igni-soft.com/dashboard?id=Amazingsite-erp)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

Sistema ERP completo para comercio electrónico y gestión empresarial, construido sobre Laravel. Cubre desde la gestión de productos y pedidos hasta multi-vendor, pasarelas de pago, CMS, RRHH y analíticas — todo en una única plataforma modular.

---

## Tabla de contenidos

- [Módulos del sistema](#módulos-del-sistema)
- [Requisitos del sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Desarrollo con Docker](#desarrollo-con-docker)
- [Variables de entorno clave](#variables-de-entorno-clave)
- [Estructura del proyecto](#estructura-del-proyecto)
- [CI/CD y calidad de código](#cicd-y-calidad-de-código)
- [Flujo de trabajo y convenciones](#flujo-de-trabajo-y-convenciones)
- [Comandos útiles](#comandos-útiles)
  - [Localizar URLs de producción (`db:localize`)](#localizar-urls-de-producción-en-la-base-de-datos-dblocalize)
- [Pruebas y calidad de código](#pruebas-y-calidad-de-código)
- [Documentación y soporte](#documentación-y-soporte)
- [Licencia](#licencia)

---

## Módulos del sistema

El sistema está compuesto por **48 módulos** independientes ubicados en `Modules/`:

| Área | Módulos |
|---|---|
| **Comercio y Ventas** | Product, InhouseOrder, OrderManage, WholeSale, Refund, GiftCard, Shipping |
| **Clientes y Fidelización** | Customer, Affiliate, ClubPoint, Review, Wallet |
| **Multi-Vendor** | Seller, MultiVendor |
| **Pagos** | PaymentGateway, MercadoPago, Plans |
| **CMS y Apariencia** | Blog, FrontendCMS, AoraPageBuilder, PageBuilder, Menu, FooterSetting, Appearance |
| **Comunicación y Marketing** | SupportTicket, ContactRequest, Marketing, Otp |
| **RRHH** | Attendance, ScheduleManagement, JobPortal |
| **Finanzas** | Account, CostCenter, GST |
| **Administración** | GeneralSetting, Setup, Language, RolePermission, SidebarManager, ModuleManager, Backup |
| **Utilidades** | Utilities, UserActivityLog, Visitor, DigitalFolder, FormBuilder, AdminReport |

---

## Requisitos del sistema

| Herramienta | Versión |
|---|---|
| PHP | 8.3 |
| Servidor web | Apache (requerido para `.htaccess` e ionCube) |
| Base de datos | MySQL 8.0+ / MariaDB |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |

### Extensiones de PHP necesarias

`ionCube` `fileinfo` `opcache` `redis` `mbstring` `readline` `bz2` `igbinary` `pcov` *(para cobertura de tests)*

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd amazingsite-erp

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS y compilar assets
npm install && npm run dev

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate
# Editar .env con las credenciales de BD y servicios externos

# 5. Migrar y poblar la base de datos
php artisan migrate --seed

# 6. Permisos de escritura (Linux/macOS)
chmod -R 775 storage bootstrap/cache
```

---

## Desarrollo con Docker

Si prefieres usar Docker en vez de WAMP/Laragon, el proyecto incluye un entorno completo con PHP-FPM, Nginx, Redis, worker y scheduler. Solo necesitas tener MySQL corriendo en tu máquina.

```powershell
# Construir la imagen (solo la primera vez o al cambiar dependencias)
docker compose build app

# Levantar los 5 servicios
docker compose up -d

# Ejecutar migraciones
docker compose exec app php artisan migrate
docker compose exec app php artisan module:migrate

# Cualquier comando artisan
docker compose exec app php artisan <comando>
```

Accede a la app en `http://localhost:8080`.

> Guía completa con solución de problemas y todos los comandos: **[docs/DOCKER.md](/docs/DOCKER.md)**

---

## Variables de entorno clave

Después de copiar `.env.example`, configurar al menos estas variables:

```ini
# Aplicación
APP_NAME=AmazCart
APP_URL=https://tu-dominio.com

# Base de datos
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña

# Correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com

# Pasarelas de pago (activar las que apliquen)
STRIPE_KEY=
STRIPE_SECRET=
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=
MERCADOPAGO_PUBLIC_KEY=
MERCADOPAGO_ACCESS_TOKEN=

# Google (analíticas, cloud storage, login social)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_ANALYTICS_MEASUREMENT_ID=

# Shiprocket (logística)
SHIPROCKET_EMAIL=
SHIPROCKET_PASSWORD=
```

> Para el entorno de pruebas, ver el archivo `.env.testing` ya configurado en el repositorio.

---

## Estructura del proyecto

```
amazingsite-erp/
├── app/                    # Código base (modelos globales, providers, helpers)
│   ├── Http/               # Middleware y controllers base
│   ├── Models/             # Modelos Eloquent globales
│   ├── Overrides/          # Overrides de paquetes vendor (ej: LicenseCheckRepository)
│   └── Providers/          # Service providers
├── Modules/                # Módulos funcionales del ERP (48 módulos)
│   └── {Modulo}/
│       ├── Entities/       # Modelos Eloquent del módulo
│       ├── Http/           # Controllers y Requests del módulo
│       ├── Repositories/   # Lógica de negocio
│       ├── Routes/         # Rutas web y API del módulo
│       ├── Resources/      # Vistas, JS y CSS del módulo
│       └── Tests/Feature/  # Tests del módulo
├── config/                 # Archivos de configuración de Laravel y paquetes
├── database/               # Migraciones, factories y seeders
├── public/                 # Assets compilados y punto de entrada HTTP
├── resources/              # Vistas y assets globales
├── routes/                 # Rutas globales
└── tests/                  # Clase base TestCase
```

---

## CI/CD y calidad de código

El repositorio cuenta con tres pipelines en GitHub Actions:

| Workflow | Disparador | Qué hace |
|---|---|---|
| **SonarQube Analysis** | Push a `main`/`develop`, PR hacia `develop` | Ejecuta tests con cobertura (Xdebug) y envía resultados a SonarQube |
| **Deploy** | Push a `main`, `staging`, `develop` | Despliega por SFTP al servidor correspondiente |
| **Check Branch** | PR hacia `main` o `staging` | Bloquea merges que no provengan de `develop` |

### Secrets requeridos en GitHub

| Secret | Descripción |
|---|---|
| `SONAR_TOKEN` | Token de usuario generado en SonarQube |
| `SONAR_HOST_URL` | URL del servidor SonarQube |
| `SFTP_SERVER` | Host del servidor de despliegue |
| `SFTP_USERNAME` | Usuario SFTP |
| `SFTP_PRIVATE_KEY` | Clave privada SSH |
| `SFTP_SERVER_PATH_PRODUCTION` | Ruta destino en producción |
| `SFTP_SERVER_PATH_STAGING` | Ruta destino en staging |
| `SFTP_SERVER_PATH_DEVELOP` | Ruta destino en develop |

Ver el dashboard de calidad en **[sonar.igni-soft.com](https://sonar.igni-soft.com)**.

Ver la guía completa de comandos SonarQube en **[SONARQUBE.md](/docs/SONARQUBE.md)**.

---

## Flujo de trabajo y convenciones

### Estrategia de ramas

```
main        ← producción (solo merges desde develop vía PR)
staging     ← pre-producción (solo merges desde develop vía PR)
develop     ← rama de integración (merges diarios del equipo)
feature/*   ← funcionalidades nuevas
fix/*       ← corrección de bugs
chore/*     ← tareas de mantenimiento (deps, config, refactor)
```

### Ciclo de trabajo

```bash
# 1. Crear rama desde develop
git checkout develop
git pull origin develop
git checkout -b feature/nombre-descriptivo

# 2. Desarrollar, commitear con frecuencia
git add .
git commit -m "feat: descripción corta del cambio"

# 3. Antes de abrir PR, pasar los tests
php artisan test

# 4. Abrir Pull Request hacia develop
```

### Convención de commits (Conventional Commits)

| Prefijo | Cuándo usarlo |
|---|---|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de bug |
| `refactor:` | Cambio de código sin nuevo comportamiento |
| `test:` | Agregar o corregir tests |
| `chore:` | Dependencias, configuración, CI |
| `docs:` | Solo documentación |
| `style:` | Formato, punto y coma, sin cambio lógico |

Ejemplos:
```
feat: agregar filtro de fecha en listado de órdenes
fix: corregir cálculo de descuento en carrito con cupón
test: agregar tests para HolidayRepository
chore: actualizar laravel/framework a 10.48
```

---

## Comandos útiles

### Localizar URLs de producción en la base de datos (`db:localize`)

Cuando se restaura un dump de producción en local (o en cualquier otro entorno), la base de datos puede contener URLs hardcodeadas que apuntan al dominio original. Este comando escanea **toda la base de datos** y reemplaza esas URLs por la del entorno actual.

#### ¿Por qué existe este comando?

El sistema almacena algunas rutas directamente en la base de datos: ítems de menú, enlaces de anuncios, páginas dinámicas, etc. Al restaurar un dump de producción en local esas referencias siguen apuntando al dominio de producción, lo que rompe la navegación y los recursos enlazados. El comando automatiza lo que antes se hacía manualmente (buscar tabla por tabla en phpMyAdmin y editar a mano).

#### Uso

```bash
# 1. Ver qué cambiaría, sin tocar nada (recomendado siempre primero)
php artisan db:localize "https://amazingsite.igni-soft.com" --dry-run

# 2. Aplicar el reemplazo (el destino se toma automáticamente de APP_URL en .env)
php artisan db:localize "https://amazingsite.igni-soft.com"

# 3. Si el destino es distinto al APP_URL (ej: staging)
php artisan db:localize "https://amazingsite.igni-soft.com" --to="https://staging.amazingsite.com"

# 4. Excluir tablas adicionales (además de logs/auditoría que se excluyen por defecto)
php artisan db:localize "https://amazingsite.igni-soft.com" --exclude=support_tickets --exclude=blog_posts

# 5. Limitar el escaneo a tablas específicas
php artisan db:localize "https://amazingsite.igni-soft.com" --only=menu_elements --only=mega_menu_ads
```

#### Flujo recomendado al restaurar un dump de producción

```bash
# 1. Restaurar el dump en la BD local
# 2. Verificar qué tablas tienen la URL de prod hardcodeada
php artisan db:localize "https://amazingsite.igni-soft.com" --dry-run

# 3. Si el resultado se ve correcto, aplicar
php artisan db:localize "https://amazingsite.igni-soft.com"
```

#### Archivos de runtime — prevención del `/install`

Al terminar el reemplazo (solo cuando no es `--dry-run`), el comando verifica y crea automáticamente los archivos de runtime que el sistema necesita para arrancar. Estos archivos **no viajan con el repositorio ni con el dump**, por lo que cualquier entorno que parta de un dump limpio los necesita.

| Archivo | Para qué sirve |
|---|---|
| `storage/app/.app_installed` | El middleware de licencia redirige a `/install` si no existe. Se crea con el checksum de `infix_module_managers` |
| `storage/app/amazy_img.json` | Imagen lazy del tema amazy (`themeDefaultImg()`) |
| `storage/app/default_img.json` | Imagen lazy para temas distintos a amazy |

> Si el sistema redirige a `/install` después de restaurar un dump, ejecutar el comando lo corrige automáticamente.

#### Detalles de comportamiento

| Aspecto | Comportamiento |
|---|---|
| **Destino por defecto** | `APP_URL` del `.env` activo |
| **Detección de columnas** | Escanea automáticamente columnas de tipo `char`, `text`, `blob` y `json` |
| **Valores JSON** | Reemplaza recursivamente solo los valores string, sin tocar claves ni estructura |
| **Tablas excluidas por defecto** | `activity_log`, `log_activity`, `failed_jobs`, `jobs`, `migrations`, `telescope_*`, `password_resets`, `personal_access_tokens`, `oauth_*` |
| **Archivos de runtime** | Se verifican y crean al finalizar (omitido en `--dry-run`) |
| **Seguridad** | El `--dry-run` nunca escribe en la BD ni crea archivos — úsalo primero para revisar |

---

```bash
# Levantar servidor local
php artisan serve

# Limpiar caché de configuración, rutas y vistas
php artisan optimize:clear

# Recompilar assets
npm run dev        # desarrollo (con watch)
npm run prod       # producción (minificado)

# Ver rutas registradas
php artisan route:list

# Crear migración
php artisan make:migration create_tabla_nombre_table

# Ejecutar migraciones pendientes
php artisan migrate

# Revertir y re-migrar con seeders (¡destructivo en producción!)
php artisan migrate:fresh --seed
```

---

## Pruebas y calidad de código

```powershell
# Ejecutar tests de un módulo
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage Modules/{Modulo}/Tests/Feature/{Clase}Test.php

# Suite completa
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage

# Generar cobertura para SonarQube (usar PCOV, no Xdebug)
php -d xdebug.mode=off -d pcov.enabled=1 vendor/bin/phpunit --coverage-clover coverage.xml

# Enviar análisis a SonarQube desde local
$env:SONAR_TOKEN = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=", 2)[1].Trim()
& "C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat" "--define" "sonar.scanner.socketTimeout=120"
```

| Documento | Descripción |
|---|---|
| **[DOCKER.md](/docs/DOCKER.md)** | Entorno Docker para desarrollo local |
| **[TESTING.md](/docs/TESTING.md)** | Guía completa de escritura y ejecución de tests |
| **[SONARQUBE.md](/docs/SONARQUBE.md)** | Comandos y proceso completo de análisis con SonarQube |
| [`db:localize`](#localizar-urls-de-producción-en-la-base-de-datos-dblocalize) | Reemplazar URLs hardcodeadas al restaurar un dump de producción en local |

---

## Documentación y soporte

- Entorno Docker para desarrollo local: [DOCKER.md](/docs/DOCKER.md)
- Guía de testing: [TESTING.md](/docs/TESTING.md)
- Guía de SonarQube: [SONARQUBE.md](/docs/SONARQUBE.md)
- Dashboard de calidad: [sonar.igni-soft.com](https://sonar.igni-soft.com)
- Configuración de Entorno: Generación de PDF con Browsershot y Google Chrome: [Browsershot y Google Chrome](/docs/Browsershot-chrome.md)
- Para dudas o soporte, contactar al equipo de desarrollo.

---

## NUEVA LIBRERÍA PARA MAQUETAR PDFS A PARTIR DE BLADE

Se ha aprovechado la integración de **Node** en el proyecto para incluir una librería de esta tecnología conocida como **Puppeter** para construcción de PDFs con una alta fidelidad de CSS.

Se hicieron pruebas con otras librerías propias de PHP como **mPDF** y **wkhtmltopdf** pero estas dejaban resultados con mala calidad.

Se requieren hacer ciertas cosas para asegurar que esta librería funcione bien en sus proyectos locales.

**1.** Asegurar que están ejecutando el proyecto en la versión **PHP 8.3.x**
**2.** Asegurar que tienen instalado correctamente [**Ioncube VC16**](<https://www.ioncube.com/loaders.php>)
```
- Descargue el Loader desde https://get-loader.ioncube.com seleccionando Windows > VC16 > Windows VC16 (64 bits) > zip
- Extraer el ZIP y copiar el archivo ioncube_loader_win_8.3.dll a la carpeta C:\laragon\bin\php\php-8.3.14-Win32-vs16-x64\ext
- Ingresar al archivo php.ini y buscar la linea ;zend_extension y cambiarla por zend_extension=ioncube_loader_win_8.3.dll
- Reiniciar Laragon
```
**3.** En la terminal de Laragon, ejecutar el siguiente comando: `composer require spatie/browsershot`
**4.** En la misma terminal, ejecutar: `npm install puppeteer`
**5.** Reiniciar Laragon


## Licencia

MIT