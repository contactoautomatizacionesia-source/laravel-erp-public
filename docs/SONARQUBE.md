# Guía de Calidad con SonarQube — AmazCart ERP

> Referencia de comandos y procesos para el ciclo de calidad de código:
> escritura de tests, generación de cobertura y análisis con SonarQube.

---

## Tabla de contenidos

1. [Infraestructura y accesos](#1-infraestructura-y-accesos)
2. [Ciclo de calidad diario](#2-ciclo-de-calidad-diario)
3. [Crear un test para un módulo nuevo](#3-crear-un-test-para-un-módulo-nuevo)
4. [Ejecutar tests](#4-ejecutar-tests)
5. [Generar cobertura de código](#5-generar-cobertura-de-código)
6. [Enviar análisis a SonarQube desde local](#6-enviar-análisis-a-sonarqube-desde-local)
7. [Análisis automático vía GitHub Actions](#7-análisis-automático-vía-github-actions)
8. [Interpretar el dashboard de SonarQube](#8-interpretar-el-dashboard-de-sonarqube)
9. [Métricas objetivo del proyecto](#9-métricas-objetivo-del-proyecto)
10. [Referencia rápida de comandos](#10-referencia-rápida-de-comandos)

---

## 1. Infraestructura y accesos

| Recurso | Valor |
|---|---|
| **Dashboard SonarQube** | https://sonar.igni-soft.com |
| **Proyecto** | `AmazCart ERP - Laravel` |
| **Project Key** | `DaruinHerreraIgniweb_amazingsite-erp_0adb0749-69e5-4fb8-b95b-218ab3f0c548` |
| **sonar-scanner (local)** | `C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat` |
| **Token de análisis** | `.env` → `SONAR_TOKEN` (nunca en el código fuente) |
| **Configuración del proyecto** | `sonar-project.properties` en la raíz |

### Generar un token nuevo

1. Ir a `https://sonar.igni-soft.com` → *My Account* → *Security*
2. Click en *Generate Token*
3. Tipo: **Project Analysis Token** · Proyecto: `amazingsite-erp`
4. Copiar el token y guardarlo en `.env`:

```env
SONAR_TOKEN=sqa_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> El token **nunca debe commitearse**. `.env` está en `.gitignore`.

---

## 2. Ciclo de calidad diario

```
┌─────────────────────────────────────────────────────────┐
│  1. Escribir / modificar código                         │
│  2. Escribir o actualizar el test del módulo afectado   │
│  3. Ejecutar los tests del módulo  →  deben pasar       │
│  4. Ejecutar la suite completa     →  0 fallos          │
│  5. (Opcional) Regenerar coverage.xml                   │
│  6. Commit + Push → GitHub Actions ejecuta SonarQube    │
│  7. Revisar dashboard: Quality Gate debe ser PASSED     │
└─────────────────────────────────────────────────────────┘
```

Si quieres ver los resultados en SonarQube **antes de hacer push**,
puedes ejecutar el análisis local (ver [sección 6](#6-enviar-análisis-a-sonarqube-desde-local)).

---

## 3. Crear un test para un módulo nuevo

### Estructura estándar

```
Modules/{Modulo}/Tests/Feature/{Clase}Test.php
```

### Plantilla mínima

```php
<?php

namespace Modules\{Modulo}\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class {Clase}Test extends TestCase
{
    use DatabaseTransactions; // rollback automático — siempre obligatorio

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1); // usuario administrador base
    }

    public function test_puede_listar_{recurso}s(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/{prefijo-ruta}/{recurso}s');

        $response->assertStatus(200);
    }

    public function test_puede_crear_{recurso}(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/{prefijo-ruta}/{recurso}s', [
                // campos requeridos por el Request del controlador
            ]);

        $response->assertStatus(200); // o 201 / redirect según el controlador
        $this->assertDatabaseHas('{tabla}', [/* campos verificables */]);
    }

    public function test_puede_actualizar_{recurso}(): void
    {
        // crear registro base, luego hacer PUT/PATCH
    }

    public function test_puede_eliminar_{recurso}(): void
    {
        // crear registro base, luego hacer DELETE
        // assertDatabaseMissing para confirmar
    }
}
```

### Convenciones obligatorias

| Elemento | Regla |
|---|---|
| Namespace | `Modules\{Modulo}\Tests\Feature` |
| Clase base | `extends Tests\TestCase` |
| Trait | `use DatabaseTransactions` — siempre, sin excepción |
| Nombres de métodos | `test_` + descripción en `snake_case` |
| Un método = una responsabilidad | No probar crear + listar + borrar en un solo método |

### Usar sellers en lugar de admin

Para rutas bajo el panel de sellers (`/seller/...`), autenticar con un usuario vendedor:

```php
use Modules\Seller\Entities\Seller;

$seller = Seller::where('is_approved', 1)->first();
$this->actingAs($seller->user);
```

---

## 4. Ejecutar tests

### Tests del módulo que estás desarrollando

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage Modules/{Modulo}/Tests/Feature/{Clase}Test.php
```

### Un test específico por nombre

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --filter test_puede_crear_holiday Modules/Attendance/Tests/Feature/HolidayRepositoryTest.php
```

### Suite completa (todos los módulos)

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage
```

> **¿Por qué `-d xdebug.mode=off`?**
> Xdebug en modo `develop` añade overhead pero no crashea.
> En modo `coverage` causa un `STATUS_ACCESS_VIOLATION` en esta máquina.
> Desactivarlo acelera la ejecución ~3x y evita crashes.

### Con output detallado por test

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --testdox
```

### Detener al primer fallo

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --stop-on-failure
```

### Solo los tests que fallaron en la última ejecución

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --order-by=defects --stop-on-failure
```

---

## 5. Generar cobertura de código

> **IMPORTANTE:** Usar siempre **PCOV**, nunca Xdebug para cobertura.
> Xdebug 3.4.7 en modo `coverage` crashea con exit `-1073741819` (Access Violation)
> al combinarse con ionCube en esta máquina. PCOV no tiene este problema.

### Generar `coverage.xml` (formato Clover — lo que lee SonarQube)

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml
```

Duración aproximada: **5-10 minutos**. El archivo se guarda en la raíz del proyecto.

> **NOTA:** Se requiere `-d memory_limit=2G` porque PCOV rastrea todos los archivos del
> proyecto (incluidos los módulos). Sin esta flag puede ocurrir un crash (`Access Violation`).
> El `pcov.directory=.` está ya configurado en `php.ini`.

### Ver cobertura en consola (sin generar archivo)

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-text
```

### Ver cobertura en HTML (reporte navegable)

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-html storage/coverage-report
# Luego abrir: storage/coverage-report/index.html
```

### Verificar que PCOV está activo

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -r "echo extension_loaded('pcov') ? 'pcov: OK' : 'pcov: NO DISPONIBLE';"
```

### ¿Cuándo regenerar `coverage.xml`?

Solo es necesario cuando:
- Se escribieron tests nuevos
- Se modificaron tests existentes
- Se quiere actualizar las métricas de cobertura en SonarQube

Para el trabajo diario (verificar que los tests pasan) usa siempre `--no-coverage`.

---

## 6. Enviar análisis a SonarQube desde local

Útil para ver resultados antes de hacer push, o para actualizar métricas inmediatamente.

### Paso 1 — Asegurarse de que los tests pasan

```powershell
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage
# Debe terminar: X tests, Y assertions, 0 failures
```

### Paso 2 — (Opcional) Regenerar coverage.xml si hay tests nuevos

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml
```

### Paso 3 — Cargar el token y ejecutar el scanner

```powershell
# Leer el token del .env y asignarlo a la sesión actual
$env:SONAR_TOKEN = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=", 2)[1].Trim()

# Ejecutar el análisis con timeout extendido
& "C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat" "--define" "sonar.scanner.socketTimeout=120"
```

> **¿Por qué `--define` y no `-D`?**
> PowerShell interpreta `-D` como un parámetro propio antes de pasarlo al proceso.
> `--define` es la forma larga equivalente y funciona correctamente.

### Paso 4 — Verificar el resultado

Al finalizar debe aparecer:

```
INFO  ANALYSIS SUCCESSFUL, you can find the results at:
      https://sonar.igni-soft.com/dashboard?id=DaruinHerreraIgniweb_...
INFO  EXECUTION SUCCESS
```

El servidor tarda ~1 minuto adicional en procesar el reporte tras la subida.

### Duración esperada del análisis completo

| Fase | Tiempo aproximado |
|---|---|
| Indexación de ~4,800 archivos | 60 s |
| Análisis PHP (3,850 archivos) | 90 s |
| Análisis JS/CSS/HTML | 30 s |
| Compresión del reporte (34.9 MB → 15.9 MB) | 150 s |
| Subida al servidor | 5 s |
| **Total** | **~5 minutos** |

### Solución al timeout en el primer intento

Si el análisis falla con `Connect timed out` / `Fail to request url: .../api/metrics/search`,
es un corte de conexión temporal. Simplemente reintentar — el segundo intento funciona.
El parámetro `sonar.scanner.socketTimeout=120` ya aumenta el tiempo de espera.

---

## 7. Análisis automático vía GitHub Actions

El análisis se ejecuta **automáticamente** en cada push a `main` o `develop` y en Pull Requests
hacia `develop`. No se requiere ninguna acción manual.

### Workflow: `.github/workflows/sonarqube.yml`

El pipeline ejecuta:
1. `composer install`
2. `php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml`
3. `sonar-scanner` con `SONAR_TOKEN` inyectado como secret de GitHub

### Secrets requeridos en GitHub

Ir a *Settings → Secrets and variables → Actions* y verificar:

| Secret | Descripción |
|---|---|
| `SONAR_TOKEN` | Token de autenticación (tipo User o Project Analysis) |
| `SONAR_HOST_URL` | `https://sonar.igni-soft.com` |

---

## 8. Interpretar el dashboard de SonarQube

### Quality Gate

El semáforo principal del proyecto. Puede ser:

| Estado | Significado |
|---|---|
| ✅ **PASSED** | El código cumple todos los umbrales de calidad definidos |
| ❌ **FAILED** | Al menos una métrica supera el umbral permitido |

Un Quality Gate fallido **bloquea el merge** en el flujo de CI configurado.

### Métricas principales

| Métrica | Qué mide | Dónde mejorarla |
|---|---|---|
| **Bugs** | Errores detectados estáticamente (null pointers, lógica incorrecta) | Corregir el código señalado |
| **Vulnerabilities** | Fallos de seguridad (SQL injection, XSS, etc.) | Revisión prioritaria — siempre corregir |
| **Security Hotspots** | Código que *podría* ser inseguro — requiere revisión manual | Marcar como *Reviewed* o corregir |
| **Code Smells** | Malas prácticas: funciones largas, código duplicado, variables no usadas | Refactors graduales |
| **Coverage** | % de líneas ejecutadas por los tests | Escribir más tests |
| **Duplications** | % de líneas duplicadas | Extraer código común a helpers/traits |
| **Lines of Code** | Tamaño total del código analizado | Solo informativo |

### Navegar a los issues

1. En el dashboard, hacer click en el número de Bugs/Vulnerabilities/Code Smells
2. Filtrar por **Severity** (Blocker > Critical > Major > Minor > Info)
3. Hacer click en un issue → ver el código exacto y la explicación de por qué es un problema
4. Opciones: *Open*, *Confirm*, *Resolve as Fixed*, *Won't Fix*, *False Positive*

### Severidades a priorizar

| Severidad | Acción recomendada |
|---|---|
| **Blocker** | Corregir antes del próximo deploy |
| **Critical** | Corregir en el sprint actual |
| **Major** | Planificar en el backlog próximo |
| **Minor / Info** | Corregir cuando se toque ese archivo |

---

## 9. Métricas objetivo del proyecto

| Métrica | Objetivo actual | Meta a largo plazo |
|---|---|---|
| Quality Gate | PASSED | PASSED siempre |
| Bugs nuevos por análisis | 0 | 0 |
| Vulnerabilidades | 0 | 0 |
| Coverage global | > 0.31% (base actual) | > 30% |
| Cobertura de módulos nuevos | > 60% | > 80% |
| Duplicaciones | < 5% | < 3% |

> La cobertura base (medida con la configuración corregida de PCOV) cubre sentencias de
> `app/` y `Modules/` correctamente. El objetivo no es cubrirlo todo de golpe, sino que
> **cada módulo nuevo o modificado** incluya sus tests desde el inicio.

---

## 10. Referencia rápida de comandos

```powershell
# ─── TESTS DIARIOS ──────────────────────────────────────────────────────────

# Correr tests de un módulo específico
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage Modules/{Modulo}/Tests/Feature/{Clase}Test.php

# Correr un test por nombre
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --filter test_nombre_del_test

# Suite completa
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage

# Suite completa, detener al primer fallo
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --stop-on-failure

# Suite completa con detalle por test
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --testdox

# Répetir solo los tests que fallaron
php -d xdebug.mode=off vendor/bin/phpunit --no-coverage --order-by=defects --stop-on-failure


# ─── COBERTURA ──────────────────────────────────────────────────────────────

# Generar coverage.xml para SonarQube (formato Clover)
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml

# Ver cobertura en consola (sin archivo)
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-text

# Reporte HTML navegable
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-html storage/coverage-report


# ─── SONARQUBE LOCAL ────────────────────────────────────────────────────────

# Cargar token del .env y lanzar el scanner
$env:SONAR_TOKEN = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=", 2)[1].Trim()
& "C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat" "--define" "sonar.scanner.socketTimeout=120"

# Ver versión del scanner instalado
& "C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat" "--version"


# ─── VERIFICACIONES ─────────────────────────────────────────────────────────

# Verificar que PCOV está disponible
php -d xdebug.mode=off -d pcov.enabled=1 -r "echo extension_loaded('pcov') ? 'PCOV: OK' : 'PCOV: NO';"

# Verificar conectividad con SonarQube
$token = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=",2)[1].Trim()
Invoke-WebRequest -Uri "https://sonar.igni-soft.com/api/system/status" -Headers @{"Authorization"="Bearer $token"} -UseBasicParsing | Select-Object StatusCode

# Ver el estado del último análisis enviado
$token = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=",2)[1].Trim()
Invoke-WebRequest -Uri "https://sonar.igni-soft.com/api/qualitygates/project_status?projectKey=DaruinHerreraIgniweb_amazingsite-erp_0adb0749-69e5-4fb8-b95b-218ab3f0c548" -Headers @{"Authorization"="Bearer $token"} -UseBasicParsing | Select-Object -ExpandProperty Content
```

---

## Ver también

- [TESTING.md](TESTING.md) — Guía completa de escritura y ejecución de tests
- [README.md](README.md) — Documentación general del proyecto
- [sonar-project.properties](sonar-project.properties) — Configuración del análisis
- [Dashboard SonarQube](https://sonar.igni-soft.com/dashboard?id=DaruinHerreraIgniweb_amazingsite-erp_0adb0749-69e5-4fb8-b95b-218ab3f0c548) — Resultados en tiempo real
