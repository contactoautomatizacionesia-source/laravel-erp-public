# Guía de Testing — AmazCart ERP

> Esta guía está pensada para desarrolladores **sin experiencia previa en testing**.
> Léela de principio a fin la primera vez. Después úsala como referencia rápida.

---

## Tabla de contenidos

1. [¿Por qué hacemos tests?](#1-por-qué-hacemos-tests)
2. [Requisitos previos](#2-requisitos-previos)
3. [Configuración del entorno de pruebas](#3-configuración-del-entorno-de-pruebas)
4. [Estructura de archivos de test](#4-estructura-de-archivos-de-test)
5. [Cómo escribir un test paso a paso](#5-cómo-escribir-un-test-paso-a-paso)
6. [Anatomía de un test real del proyecto](#6-anatomía-de-un-test-real-del-proyecto)
7. [Comandos de ejecución](#7-comandos-de-ejecución)
8. [Generar reporte de cobertura](#8-generar-reporte-de-cobertura)
9. [Rutina de trabajo diario](#9-rutina-de-trabajo-diario)
10. [Qué hacer cuando un test falla](#10-qué-hacer-cuando-un-test-falla)
11. [Estándares de calidad esperados](#11-estándares-de-calidad-esperados)
12. [Referencia rápida de assertions](#12-referencia-rápida-de-assertions)
13. [Preguntas frecuentes](#13-preguntas-frecuentes)
14. [Análisis SonarQube desde local](#14-análisis-sonarqube-desde-local)
15. [Historial de correcciones de tests (2026)](#15-historial-de-correcciones-de-tests-2026)

---

## 1. ¿Por qué hacemos tests?

Un test es código que **verifica automáticamente que otro código funciona correctamente**.

Sin tests, cada cambio que haces requiere probar manualmente en el navegador que nada se rompió. Con tests, una sola ejecución de un comando verifica todo el sistema en segundos.

**Beneficios concretos en este proyecto:**
- Detectas si tu cambio rompe un módulo que no tocaste.
- SonarQube mide la calidad del código usando la cobertura de tests como métrica clave.
- Facilita el onboarding de nuevos desarrolladores.

---

## 2. Requisitos previos

| Herramienta | Versión mínima | Verificar con |
|---|---|---|
| PHP | 8.3 | `php --version` |
| Composer | 2.x | `composer --version` |
| MySQL | 8.0+ | WampServer activo |
| ionCube Loader | 15.x (PHP 8.3 TS) | `php -m \| findstr ioncube` |
| PCOV (extensión PHP) | 1.0.12 | `php -m \| findstr pcov` |

> **Nota sobre PHP:** Este proyecto usa **PHP 8.3** de WampServer.
> Si ves PHP 7.4 en tu consola, revisa la sección de [Preguntas frecuentes](#13-preguntas-frecuentes).

---

### ⚠️ Requisito crítico: ionCube Loader en PHP CLI

Este proyecto usa paquetes del vendor de SpondonIt que están **protegidos con ionCube**.
Sus archivos helper se cargan de forma **eager** (inmediata) al inicializar el autoloader de Composer.
Sin el ionCube Loader configurado en PHP CLI, **ningún test puede ejecutarse**.

**Para instalar el ionCube Loader en PHP CLI 8.3 (WampServer):**

**Paso 1** — Descargar el loader desde la URL directa (Windows VC16 x86-64):

```
https://downloads.ioncube.com/loader_downloads/ioncube_loaders_win_vc16_x86-64.zip
```

> También está disponible en la página oficial: https://www.ioncube.com/loaders.php  
> Seleccionar: **Windows** · **PHP 8.3** · **64bits (x86-64)** · **TS (Thread Safe)**

Extraer el ZIP y localizar el archivo `ioncube_loader_win_8.3.dll`.

**Paso 2** — Copiar el archivo al directorio de extensiones de PHP:
```powershell
# Después de extraer el ZIP, copiar la DLL:
Copy-Item "ruta\extraida\ioncube_loader_win_8.3.dll" "C:\wamp64\bin\php\php8.3.28\ext\"
```

**Paso 3** — Agregar el loader al php.ini CLI.
Abrir `C:\wamp64\bin\php\php8.3.28\php.ini` y agregar esta línea como la **primera** entrada
de `zend_extension` (antes de OPcache y Xdebug):

```ini
[ioncube]
zend_extension = "c:/wamp64/bin/php/php8.3.28/ext/ioncube_loader_win_8.3.dll"
```

**Paso 4** — Verificar la instalación:
```bash
php -m | findstr ioncube
# Debe mostrar: ionCube Loader
```

**Paso 5** — Ejecutar los tests para confirmar:
```bash
php artisan test --stop-on-failure
```

> **¿Por qué no alcanza con que esté en Apache?**
> WampServer usa dos php.ini separados: `phpForApache.ini` (para páginas web vía Apache)
> y `php.ini` (para el CLI). Los tests siempre usan el CLI. Ambos deben tener el loader.
> Si ves el error *"Script error: the ionCube Loader for PHP needs to be installed"* al correr
> `php artisan test`, significa que el loader no está en el `php.ini` del CLI.

---

## 3. Configuración del entorno de pruebas

Los tests usan un archivo `.env.testing` separado del `.env` de desarrollo. Esto garantiza que los tests nunca afecten datos reales.

### 3.1 Verificar que existe `.env.testing`

```bash
# Desde la raíz del proyecto
cat .env.testing
```

Debe contener al menos:

```ini
APP_NAME=AmazCartTesting
APP_ENV=testing
APP_KEY=base64:...           # debe estar configurado
APP_URL=http://localhost
APP_MODE=test                # desactiva verificación de licencia UXSeven
LICENSE_CHECK_ENABLED=false  # bypass del middleware de licencia

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sql_amazingsite_igni_soft_com   # misma BD de desarrollo
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

> **¿Por qué la misma BD de desarrollo?**
> Los tests usan `DatabaseTransactions` — cada test crea sus datos y los deshace al terminar
> (rollback automático). Nunca dejan datos basura ni alteran registros existentes.

### 3.2 Verificar configuración de PHPUnit

El archivo `phpunit.xml` en la raíz controla cómo PHPUnit descubre y ejecuta los tests:

```xml
<testsuites>
    <testsuite name="ModulesFeature">
        <!-- Descubre automáticamente todos los archivos *Test.php en Modules/ -->
        <directory suffix="Test.php">./Modules</directory>
    </testsuite>
</testsuites>
```

No necesitas modificar este archivo al crear tests nuevos — se descubren automáticamente.

---

## 4. Estructura de archivos de test

El proyecto tiene **dos lugares** donde se escriben tests:

### Tests de Módulos (para código en `Modules/`)

```
Modules/
├── Attendance/
│   └── Tests/
│       └── Feature/
│           └── HolidayRepositoryTest.php   ← test de ejemplo
├── Product/
│   └── Tests/
│       └── Feature/
│           └── ProductRepositoryTest.php   ← test de ejemplo
├── TuModulo/
│   └── Tests/
│       └── Feature/
│           └── TuClaseTest.php             ← así debes crearlo
```

### Tests del núcleo de la aplicación (para código en `app/`)

Para código que vive en `app/` (servicios, repositorios globales, traits, etc.),
los tests se colocan en `tests/Feature/` siguiendo una estructura que espeje `app/`:

```
tests/
└── Feature/
    └── Services/
        └── DoubleApprovalServiceTest.php   ← test de ejemplo
```

> **¿Cuándo usar `tests/Feature/` vs `Modules/*/Tests/Feature/`?**
> - Si el código está en `app/` → `tests/Feature/`
> - Si el código está en `Modules/` → `Modules/{Modulo}/Tests/Feature/`

### Convenciones de nombres obligatorias

| Elemento | Módulo | App core |
|---|---|---|
| Carpeta | `Modules/{Modulo}/Tests/Feature/` | `tests/Feature/{Subcarpeta}/` |
| Namespace | `Modules\{Modulo}\Tests\Feature` | `Tests\Feature\{Subcarpeta}` |
| Clase | `{Lo que prueba}Test` | `{Lo que prueba}Test` |
| Método | `test_{descripcion_en_snake_case}` | `test_{descripcion_en_snake_case}` |

> El prefijo `test_` es **obligatorio** — PHPUnit solo ejecuta métodos que empiezan con `test`.

---

## 5. Cómo escribir un test paso a paso

### Paso 1 — Crea el archivo

Crea `Modules/Blog/Tests/Feature/PostTest.php`:

```php
<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostTest extends TestCase
{
    use DatabaseTransactions; // ← SIEMPRE incluir esto

}
```

> `use DatabaseTransactions` es la línea más importante.
> Sin ella, tus tests modificarían la base de datos permanentemente.

### Paso 2 — Agrega el usuario admin

La mayoría de rutas del ERP requieren autenticación como administrador:

```php
public function test_can_list_posts()
{
    $user = User::find(1); // usuario admin con ID 1
    $this->actingAs($user);

    // ... resto del test
}
```

### Paso 3 — Haz la acción que quieres probar

```php
$response = $this->get('/blog/posts');
```

### Paso 4 — Verifica el resultado esperado

```php
$response->assertStatus(200);
```

### Paso 5 — Ejecuta solo tu test para verificar

```bash
php artisan test Modules/Blog/Tests/Feature/PostTest.php
```

### Ejemplo completo

```php
<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Blog\Entities\Post;
use Tests\TestCase;

class PostTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Verifica que el listado de posts devuelve 200.
     */
    public function test_can_list_posts()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/blog/posts');

        $response->assertStatus(200);
    }

    /**
     * Verifica que se puede ver el detalle de un post existente.
     */
    public function test_can_view_post_detail()
    {
        $user = User::find(1);
        $this->actingAs($user);

        // Crea un registro de prueba (se borrará al terminar el test)
        $post = Post::create([
            'title' => 'Post de Prueba',
            'slug'  => 'post-de-prueba-' . time(),
            'status' => 1,
        ]);

        // Verifica que existe en la BD
        $this->assertDatabaseHas('posts', ['title' => 'Post de Prueba']);

        // Verifica la ruta
        $response = $this->get('/blog/posts/' . $post->id);
        $response->assertStatus(200);
    }
}
```

---

## 6. Anatomía de un test real del proyecto

Este es `HolidayRepositoryTest.php` con explicaciones:

```php
<?php

namespace Modules\Attendance\Tests\Feature;  // ← namespace = ubicación del archivo

use App\Models\User;                          // ← modelo de usuario para autenticación
use Carbon\Carbon;                            // ← para fechas
use Illuminate\Foundation\Testing\DatabaseTransactions; // ← rollback automático
use Modules\Attendance\Entities\Holiday;      // ← modelo que vamos a probar
use Tests\TestCase;                           // ← clase base de tests del proyecto

class HolidayRepositoryTest extends TestCase
{
    use DatabaseTransactions; // ← cada test es aislado: crea y deshace sus datos

    public function test_can_list_holidays()
    {
        // ARRANGE: preparar el escenario
        $user = User::find(1);
        $this->actingAs($user); // simular login como admin

        // ACT: ejecutar la acción
        $response = $this->get('/attendance/holidays');

        // ASSERT: verificar el resultado
        $response->assertStatus(200); // esperamos HTTP 200
    }

    public function test_create_single_day_holiday()
    {
        // ARRANGE
        $user = User::find(1);
        $this->actingAs($user);

        // ACT: crear un registro directamente en la BD
        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Día de Prueba',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        // ASSERT: verificar que quedó en la BD
        $this->assertDatabaseHas('holidays', ['name' => 'Día de Prueba']);

        // DatabaseTransactions lo borrará automáticamente al terminar este método
    }
}
```

### El patrón AAA (Arrange - Act - Assert)

Todo test bien escrito sigue esta estructura:

```
ARRANGE  → Preparar datos y condiciones iniciales
ACT      → Ejecutar la acción que queremos probar
ASSERT   → Verificar que el resultado es el esperado
```

---

## 7. Comandos de ejecución

### Ejecutar todos los tests

```bash
php artisan test
```

> Si PHPUnit no produce output y sale con código negativo (`-1073741819`), Xdebug está
> causando un crash. Usa directamente `vendor/bin/phpunit` con Xdebug desactivado:
>
> ```bash
> php -d xdebug.mode=off vendor/bin/phpunit
> ```

### Ejecutar un módulo específico

```bash
php artisan test Modules/Attendance/Tests/Feature/HolidayRepositoryTest.php
# o con phpunit directamente (recomendado si hay problemas con Xdebug):
php -d xdebug.mode=off vendor/bin/phpunit Modules/Attendance/Tests/Feature/HolidayRepositoryTest.php
```

### Ejecutar un test específico por nombre

```bash
php artisan test --filter test_can_list_holidays
```

### Ver detalle de cada test (verbose)

```bash
php artisan test --testdox
```

### Detener al primer fallo

```bash
php artisan test --stop-on-failure
```

### Ejecutar solo tests de un módulo completo

```bash
php artisan test --filter "Modules\\Attendance"
```

> **Nota sobre `--parallel`:** Este proyecto usa archivos ionCube que requieren
> el servidor web de WampServer. Los tests paralelos lanzan procesos PHP hijos sin
> ese contexto y fallan. **No usar `--parallel`.**

---

## 8. Generar reporte de cobertura

La cobertura mide qué porcentaje del código fuente es ejecutado por los tests.
SonarQube la usa para evaluar la calidad del proyecto.

### Requisitos

- **PCOV** debe estar instalado (ya configurado en este proyecto).
- Ejecutar desde una consola nueva en VS Code (PHP 8.3 en el PATH).

### ⚠️ Xdebug NO se puede usar para generar cobertura en esta máquina

Xdebug 3.4.7 con `xdebug.mode=coverage` causa un **crash silencioso** en PHP 8.3 + Windows
(código de salida `-1073741819` = `STATUS_ACCESS_VIOLATION`) al cargar el bootstrap de Laravel
junto con los archivos protegidos por ionCube. **Siempre usar PCOV.**

### Comando completo

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml
```

| Parámetro | Qué hace |
|---|---|
| `-d pcov.enabled=1` | Activa PCOV para instrumentar el código |
| `-d xdebug.mode=off` | Desactiva Xdebug (no pueden coexistir generando coverage) |
| `-d memory_limit=2G` | **Obligatorio** — PCOV rastrea todo el proyecto (app/ + Modules/) |
| `--coverage-clover coverage.xml` | Genera el archivo que lee SonarQube |

> **Nota:** `pcov.directory=.` ya está en `php.ini`. Sin esa configuración, PCOV solo
> rastraría `app/` y dejaría todos los módulos con 0% de cobertura.

### Ver el reporte en consola (sin generar XML)

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-text
```

### ¿Cuándo ejecutarlo?

Solo antes de hacer push al repositorio cuando quieres actualizar las métricas de SonarQube.
**No es necesario ejecutarlo en cada cambio** — usa `php artisan test` para el día a día.

### El archivo `coverage.xml`

- Se genera en la raíz del proyecto.
- Está en `.gitignore` — no se sube al repositorio.
- SonarQube lo lee cuando se ejecuta el análisis local o desde GitHub Actions.

---

## 9. Rutina de trabajo diario

### Al empezar a programar

```bash
# Opcional: correr los tests para confirmar que todo está verde antes de tocar código
php artisan test --stop-on-failure
```

### Mientras programas

```bash
# Después de cada cambio significativo, corre solo los tests del módulo que tocaste
php artisan test Modules/TuModulo/Tests/Feature/TuClaseTest.php
```

### Antes de hacer commit

```bash
# Asegurarse de que toda la suite pasa
php artisan test
```

### Antes de hacer push (para actualizar SonarQube)

```powershell
# 1. Generar el reporte de cobertura
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml

# 2. Hacer el push normalmente — GitHub Actions ejecutará el análisis de SonarQube
git add .
git commit -m "feat: ..."
git push
```

---

## 10. Qué hacer cuando un test falla

### Paso 1 — Leer el mensaje de error completo

```bash
php artisan test --stop-on-failure
```

El output muestra exactamente qué línea falló y por qué:

```
FAIL  Modules\Blog\Tests\Feature\PostTest
  ✕ can list posts                                        0.45s

  Expected response status code [200] but received [500].
  │ PostTest.php:25
```

### Paso 2 — Identificar el tipo de fallo

| Mensaje | Causa probable | Solución |
|---|---|---|
| `Script error: the ionCube Loader for PHP needs to be installed` | Los archivos helper del vendor SpondonIt son ionCube-protegidos y no pueden cargarse sin el loader en CLI | Instalar ionCube Loader para PHP 8.3 CLI. Ver [sección de requisitos](#️-requisito-crítico-ioncube-loader-en-php-cli) |
| `Expected status 200 but received 302` | El usuario no está autenticado o redirige al login | Verificar `$this->actingAs($user)` |
| `Expected status 200 but received 500` | Error en el controlador/repositorio | Revisar logs en `storage/logs/laravel.log` |
| `Target class [current_lang] does not exist` | La página intenta renderizar una vista antes de que el middleware configure los singletons | Asegurarse de hacer `assertStatus()` antes de `assertSee()` en vistas complejas |
| `SQLSTATE: Table doesn't exist` | La BD de testing no tiene las tablas necesarias | Ejecutar `php artisan migrate` |
| `User::find(1) returns null` | El usuario admin no existe en la BD | Verificar que la BD está poblada con seeders |
| `No application encryption key` | Falta `APP_KEY` en `.env.testing` | Ejecutar `php artisan key:generate --env=testing` |
| `Fatal error: Cannot redeclare setEnv()` | La función `setEnv()` en `app/Helpers/Functions.php` no tiene guarda `function_exists` y se carga más de una vez en tests | Envolver la función: `if (!function_exists('setEnv')) { function setEnv(...) {...} }` |
| `SQLSTATE[42S02]: Table 'xxx.staffs' doesn't exist` | El modelo `Staff` usa el nombre plural (`staffs`) por convención de Laravel, pero la tabla real es `staff` | Agregar `protected $table = 'staff'` en `App\Models\Staff` o corregir la consulta que usa `DB::table('staffs')` |
| PHPUnit no produce ningún output y sale con código `-1073741819` | Crash de acceso a memoria (STATUS_ACCESS_VIOLATION). Suele ocurrir cuando Xdebug está activo en modo `develop` junto con tests pesados | Ejecutar con `-d xdebug.mode=off`: `php -d xdebug.mode=off vendor/bin/phpunit ...` |

### Paso 3 — Ver los logs

```bash
# Ver los últimos errores del log
php artisan test 2>&1; Get-Content storage/logs/laravel.log | Select-Object -Last 50
```

### Paso 4 — Aislar el test que falla

```bash
# Ejecutar solo ese método específico
php artisan test --filter test_nombre_del_metodo_que_falla
```

### Paso 5 — Verificar el estado de la BD

```bash
# Confirmar que la BD está accesible y tiene datos
php artisan tinker
>>> \App\Models\User::find(1)
>>> \DB::table('holidays')->count()
```

### Paso 6 — Si el test falló después de tu cambio

Esto significa que **tu cambio rompió algo**. El test está cumpliendo su función.
Opciones:
1. **Corregir el código** que cambióte para que el comportamiento esperado se mantenga.
2. **Actualizar el test** si el comportamiento cambió intencionalmente (el endpoint ahora responde 201 en lugar de 200, por ejemplo).

> **Regla de oro:** nunca elimines un test porque falla. Primero entiende por qué falla.

---

## 11. Estándares de calidad esperados

### Cobertura mínima por módulo

| Tipo de código | Cobertura objetivo |
|---|---|
| Repositorios (`Repositories/`) | ≥ 80% |
| Controladores (`Http/Controllers/`) | ≥ 60% |
| Servicios (`Services/`) | ≥ 70% |
| Modelos (`Entities/`) | ≥ 50% |
| **Cobertura global del proyecto** | **≥ 30%** |

### Un test bien escrito cumple estos criterios

- [ ] Tiene el namespace correcto (`Modules\{Modulo}\Tests\Feature`)
- [ ] Extiende `Tests\TestCase`
- [ ] Usa `DatabaseTransactions` (nunca `RefreshDatabase`)
- [ ] Cada método tiene **un solo objetivo** claro
- [ ] El nombre del método describe exactamente qué verifica
- [ ] Usa `$this->actingAs($user)` cuando la ruta requiere autenticación
- [ ] No depende del orden de ejecución (cada test es independiente)
- [ ] No usa `sleep()` ni esperas fijas

### Lo que NO se debe hacer

```php
// ❌ MAL: un test que prueba múltiples cosas
public function test_holiday()
{
    // crea, lista, actualiza y elimina en el mismo test
    // si falla, no sabes qué parte específica falló
}

// ✅ BIEN: un test por responsabilidad
public function test_can_create_holiday() { ... }
public function test_can_list_holidays() { ... }
public function test_can_delete_holiday() { ... }
```

```php
// ❌ MAL: usar RefreshDatabase (borra y recrea toda la BD)
use RefreshDatabase;

// ✅ BIEN: usar DatabaseTransactions (rollback automático, BD intacta)
use DatabaseTransactions;
```

```php
// ❌ MAL: hardcodear IDs que pueden no existir
$post = Post::find(99999);

// ✅ BIEN: crear los datos que necesitas dentro del test
$post = Post::create([...]);
```

### SonarQube — métricas monitoreadas

SonarQube evalúa automáticamente en cada push:

| Métrica | Descripción |
|---|---|
| **Coverage** | % de líneas ejecutadas por los tests |
| **Duplications** | Código duplicado |
| **Bugs** | Errores detectados estáticamente |
| **Code Smells** | Malas prácticas de código |
| **Security Hotspots** | Posibles vulnerabilidades |
| **Quality Gate** | Evaluación global (passed / failed) |

Ver el dashboard en: `https://sonar.igni-soft.com`

---

## 12. Referencia rápida de assertions

### Respuestas HTTP

```php
$response->assertStatus(200);           // código HTTP exacto
$response->assertOk();                  // alias de assertStatus(200)
$response->assertCreated();             // 201
$response->assertNoContent();           // 204
$response->assertRedirect('/ruta');     // 302 a una ruta específica
$response->assertUnauthorized();        // 401
$response->assertForbidden();           // 403
$response->assertNotFound();            // 404
```

### Contenido de la respuesta

```php
$response->assertSee('texto');                          // el texto aparece en la respuesta
$response->assertDontSee('texto');                      // el texto NO aparece
$response->assertJson(['key' => 'value']);               // JSON contiene estos datos
$response->assertExactJson(['key' => 'value']);          // JSON exacto
$response->assertJsonStructure(['id', 'name', 'email']); // JSON tiene estas claves
```

### Base de datos

```php
$this->assertDatabaseHas('tabla', ['campo' => 'valor']);    // registro existe
$this->assertDatabaseMissing('tabla', ['campo' => 'valor']); // registro NO existe
$this->assertDatabaseCount('tabla', 5);                     // cantidad exacta de registros
```

### Valores generales

```php
$this->assertTrue($condicion);
$this->assertFalse($condicion);
$this->assertEquals('esperado', $actual);
$this->assertNotNull($valor);
$this->assertNull($valor);
$this->assertCount(3, $coleccion);
$this->assertEmpty($coleccion);
$this->assertNotEmpty($coleccion);
$this->assertContains('valor', $array);
$this->assertInstanceOf(Holiday::class, $objeto);
```

---

## 13. Preguntas frecuentes

### ¿Por qué veo PHP 7.4 en lugar de 8.3?

Este proyecto requiere PHP 8.3. Si tu consola muestra 7.4, Laragon está tomando precedencia en el PATH del sistema.

**Solución permanente** (ya configurada en este proyecto):
En *Variables de entorno del usuario*, la entrada `C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64` fue reemplazada por `C:\wamp64\bin\php\php8.3.28`. Si trabajas en una máquina nueva, debes hacer este cambio.

### ¿Por qué no puedo usar `--parallel`?

Los tests en paralelo lanzan procesos PHP hijos independientes que no cargan ionCube (extensión de licencia del proyecto). ionCube solo está disponible en el contexto de Apache/WampServer, no en el PHP CLI hijo. Usar `--parallel` genera errores de ionCube en todos los tests.

### ¿Puedo usar SQLite en memoria para los tests?

No en este proyecto. Los tests usan `User::find(1)` y otros registros que solo existen en la BD MySQL poblada con los seeders. SQLite en memoria estaría vacía.

### ¿`DatabaseTransactions` vs `RefreshDatabase`, cuál es la diferencia?

| | `DatabaseTransactions` | `RefreshDatabase` |
|---|---|---|
| Qué hace | Envuelve cada test en una transacción y hace rollback | Borra y recrea toda la BD antes de cada test |
| Velocidad | Rápido | Muy lento |
| BD después del test | Intacta (como estaba antes) | Vacía o con solo seeders |
| Ideal para | BD con datos existentes (este proyecto) | BD dedicada solo a tests |

### ¿Tengo que crear el directorio `Tests/Feature/` manualmente?

Sí. PHPUnit lo descubrirá automáticamente gracias a la configuración del `phpunit.xml`, pero debes crear la carpeta y el archivo manualmente (o con `php artisan make:test` si el módulo lo soporta).

### ¿Con qué frecuencia debo ejecutar los tests?

- **Al menos una vez antes de cada commit.**
- Idealmente después de cada cambio sobre código que toca lógica de negocio.

### ¿Qué hago si un test existente que yo no escribí está fallando?

1. Verifica que la BD tiene los datos necesarios (`User::find(1)` existe, tablas migradas).
2. Revisa si alguien cambió el comportamiento de la ruta o modelo que ese test prueba.
3. Si el comportamiento cambió intencionalmente, actualiza el test para reflejar el nuevo comportamiento y documenta el cambio en el commit.

---

## 14. Análisis SonarQube desde local

El análisis de SonarQube se puede ejecutar desde la máquina de desarrollo para ver resultados
obtenidos antes de hacer push, o para actualizar métricas de cobertura inmediatamente después
de regenerar `coverage.xml`.

### 14.1 Requisitos

| Herramienta | Ubicación en esta máquina |
|---|---|
| sonar-scanner 7.x | `C:\sonar-scanner-7.0.2.4839-windows-x64\` |
| SonarQube server | `https://sonar.igni-soft.com` |
| Token de autenticación | `.env` → `SONAR_TOKEN` |
| Java (incluido con el scanner) | `C:\sonar-scanner-7.0.2.4839-windows-x64\jre\` |

### 14.2 Token de autenticación

El token de tipo **Project Analysis Token** está guardado en `.env` (nunca en el código fuente):

```env
# .env
SONAR_TOKEN=sqa_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

`.env` está en `.gitignore` — el token **nunca se sube al repositorio**.

Si necesitas generar uno nuevo: `https://sonar.igni-soft.com` → *My Account* → *Security* → *Generate Token*.
Seleccionar tipo **Project Analysis Token** para el proyecto `amazingsite-erp`.

### 14.3 Proceso completo (paso a paso)

#### Paso 1 — Regenerar `coverage.xml` (solo si hay tests nuevos o modificados)

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml
```

Duración aproximada: 5-10 minutos. Al terminar muestra `249 tests, X assertions`.
Si no hay cambios en tests, puedes saltar este paso y reusar el `coverage.xml` existente.

#### Paso 2 — Leer el token del .env y lanzar el scanner

```powershell
# Leer el token del .env y asignarlo a la variable de entorno de la sesión
$env:SONAR_TOKEN = (Get-Content .env | Select-String "^SONAR_TOKEN=").ToString().Split("=", 2)[1].Trim()

# Ejecutar el scanner con timeout extendido para evitar cortes en informes grandes
& "C:\sonar-scanner-7.0.2.4839-windows-x64\bin\sonar-scanner.bat" "--define" "sonar.scanner.socketTimeout=120"
```

> **¿Por qué `--define` en lugar de `-D`?**
> PowerShell interpreta `-D` como un parámetro propio del shell antes de pasarlo al proceso.
> El argumento `--define` es la forma larga equivalente y funciona correctamente en PowerShell.
> Intentar `-Dsonar.scanner.socketTimeout=120` produce `ERROR Unrecognized option: .scanner.socketTimeout=120`.

#### Paso 3 — Monitorear el progreso

El scanner imprime en consola los pasos en tiempo real. Secuencia normal esperada:

```
INFO  Preprocessing files...             ← ~7 s: descubre los 4759 archivos
INFO  Loading plugins...                  ← ~4 s: carga plugins PHP, JS, texto
INFO  Indexing files...                   ← ~60 s: indexa ~2700 archivos fuente
INFO  3850/3850 source files analyzed     ← análisis PHP completo
INFO  Importing coverage.xml              ← importa cobertura de tests
INFO  Analysis report generated 34.9 MB  ← reporte local generado
INFO  Analysis report compressed 15.9 MB ← compresión (~150 s en red lenta)
INFO  Analysis report uploaded           ← subida al servidor
INFO  ANALYSIS SUCCESSFUL                ← éxito
INFO  EXECUTION SUCCESS                  ← fin
```

Duración total aproximada: **4-5 minutos** (la mayor parte es compresión y subida del reporte).

#### Paso 4 — Ver los resultados en el dashboard

```
https://sonar.igni-soft.com/dashboard?id=DaruinHerreraIgniweb_amazingsite-erp_0adb0749-69e5-4fb8-b95b-218ab3f0c548
```

> El servidor tarda ~1 minuto adicional en procesar el reporte después de subido.
> Si los resultados no aparecen de inmediato, espera un momento y recarga el dashboard.

### 14.4 Advertencias de parseo esperadas (no son errores)

Durante el análisis aparecen estos `WARN` que son **falsos positivos** y no afectan el resultado:

| Archivo | Causa |
|---|---|
| `Modules/Shipping/.../label_pdf.blade.php` | Blade con `str_replace('<?xml ...')` — el parser PHP lo interpreta como inicio de XML |
| `Modules/Shipping/.../label_pdf_dummy.blade.php` | Mismo caso |
| `Modules/Utilities/.../xml_sitemap.blade.php` | Blade que emite `<?xml ...?>` mediante `{!! '...' !!}` |

Estos archivos son vistas que generan XML/QR dinámicamente; su lógica es correcta.

### 14.5 Problemas conocidos y soluciones

#### Timeout durante la subida (`Connect timed out`)

**Síntoma:** El scanner falla con `Fail to request url: .../api/metrics/search` en el primer intento.

**Causa:** El servidor SonarQube cortó la conexión durante el procesamiento de un reporte grande mientras el scanner esperaba respuesta con el timeout por defecto.

**Solución:** Reintentar con `sonar.scanner.socketTimeout=120` como se muestra en el paso 2.
El primer intento puede fallar; el segundo suele completarse sin problemas.

#### La propiedad `sonar.ws.timeout` está deprecada

Usar `sonar.scanner.socketTimeout` en su lugar. `sonar.ws.timeout` produce un `WARN` de deprecación y será eliminada en futuras versiones del scanner.

---

## 15. Historial de correcciones de tests (2026)

Registro de las correcciones aplicadas para llevar la suite de **14 fallos a 0 fallos** (249 tests totales).

### Contexto

Al retomar el proyecto en marzo de 2026, la suite presentaba 14 fallos distribuidos en 5 archivos.
Los fallos no eran bugs en el código de producción sino desincronizaciones entre los tests
y el estado actual de la aplicación: rutas renombradas, campos nuevos en formularios, cambios
en la lógica de validación y registros de base de datos que ya existían.

### 15.1 Correcciones aplicadas

#### `Modules/Language/Tests/Feature/LanguageTest.php`

**Test fallido:** `test_for_create_language` → HTTP 404.

**Causa:** El prefijo de ruta del módulo Language había cambiado. El test usaba la URL anterior.

**Corrección:** Actualizar la URL del endpoint al prefijo de ruta vigente en `web.php` del módulo.

---

#### `Modules/Product/Tests/Feature/AttributeTest.php`

**Test fallido:** `test_for_edit_variant_values` → HTTP 422 o datos no procesados.

**Causa:** El controlador de atributos esperaba los valores de variante en una estructura de array
anidada (`variant_values[]` con índices) que el test no enviaba correctamente.

**Corrección:** Reestructurar el payload para enviar el formato de array validado por `AttributeRequest`.

---

#### `Modules/FrontendCMS/Tests/Feature/PricingPlanPageTest.php`

**Test fallido:** `test_for_update_pricing_plan_page` → HTTP 422.

**Causa:** El formulario de planes de precio incorporó el campo `plan_price` como requerido
posteriormente a la escritura del test. El payload no lo incluía.

**Corrección:** Agregar `'plan_price' => [...]` al array de datos del test.

---

#### `Modules/Product/Tests/Feature/ProductTest.php` (controlador Admin)

**Tests fallidos:** `test_for_create_single_product` y `test_for_update_single_product`.

**Causas:**
- El campo `stock_manage` se volvió requerido en `ProductRequest`.
- Faltaban `use` imports de clases de Eloquent usadas en el test.
- El payload no incluía todos los campos marcados como requeridos en la validación actual.

**Correcciones:**
- Agregar `'stock_manage' => 1` al payload de creación y actualización.
- Agregar los imports faltantes (`use Modules\Product\Entities\Product`, etc.).
- Completar el array de datos con todos los campos requeridos por `ProductRequest`.

---

#### `Modules/Seller/Tests/Feature/ProductTest.php`

**Tests fallidos:** Varios tests de productos de sellers (creación, actualización, mis productos).

**Causas:**
- El test intentaba `create()` registros de `business_info` y `bank_info` para el seller base,
  pero esos registros ya existían → error de clave primaria duplicada en MySQL.
- El payload del campo `thumbnail` no coincidía con el formato esperado por el controlador
  del seller (array de rutas vs. objeto con metadata).
- Campos de precio mayorista (`wholesale_price_min_qty`, `wholesale_price`) generaban
  error de validación cuando el módulo WholeSale no está activo para ese seller.

**Correcciones:**
- Cambiar `create()` por `updateOrCreate()` en la preparación de `business_info` y `bank_info`.
- Ajustar la estructura de `thumbnail` en el payload al formato que el controlador deserializa.
- Envolver los campos de wholesale en una condición que verifique si el módulo está activo.

---

### 15.2 Solución al crash de Xdebug durante generación de cobertura

**Fecha de diagnóstico:** Marzo 2026

**Problema:** Al ejecutar `php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-clover coverage.xml`,
PHPUnit se iniciaba pero moría silenciosamente con código de salida `-1073741819`
(`STATUS_ACCESS_VIOLATION` en Windows — acceso a memoria no autorizado).

**Investigación:** El crash ocurría consistentemente al cargar el bootstrap de Laravel junto
con los archivos del vendor protegidos por ionCube bajo instrumentación de Xdebug 3.4.7.
En modo `off` o `develop`, Xdebug funciona correctamente — el crash es exclusivo del modo `coverage`.

**Solución definitiva:** Usar la extensión **PCOV** (ya instalada en el entorno) como driver
de cobertura alternativo. PCOV no interfiere con ionCube y no tiene el problema de acceso a memoria:

```powershell
php -d xdebug.mode=off -d pcov.enabled=1 -d memory_limit=2G vendor/bin/phpunit --coverage-clover coverage.xml
```

**Resultado:** Cobertura generada exitosamente — 249 tests, 0 fallos, 13 skipped.

> **Problema adicional descubierto:** `pcov.directory` tenía el valor `auto` que detectaba
> solo `./app`, dejando todos los `Modules/` con 0% cobertura. Corregido con `pcov.directory=.`
> en `php.ini`. Ahora el coverage.xml captura ~482 archivos cubiertos (449 en Modules).

---

### 15.3 Estado final de la suite tras las correcciones

| Métrica | Antes | Después |
|---|---|---|
| Tests totales | 249 | 249 |
| Fallos | 14 | **0** |
| Errores | 0 | 0 |
| Skipped | 13 | 13 |
| Cobertura (statements) | Sin datos válidos | ~0.31% (1,448 / 473,640) |
| `coverage.xml` | Desactualizado | Regenerado con pcov (11 MB, formato Clover) |
| Último análisis SonarQube | Desactualizado | Subido exitosamente (marzo 2026) |

> La cobertura del 0.31% refleja la escala del proyecto (~474K sentencias en un ERP
> completo), no la calidad de los tests. El objetivo es incrementar este porcentaje
> gradualmente escribiendo tests para cada módulo nuevo o modificado.
