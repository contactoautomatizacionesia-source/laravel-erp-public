# ProtecData — Firma Electrónica de Contratos

## ¿Qué es ProtecData?

ProtecData (VozData en sus comunicaciones al usuario final) es un servicio externo de **firma electrónica**. Permite enviar un contrato PDF al cliente para que lo firme remotamente. La notificación llega por SMS, WhatsApp o Email según configuración.

---

## Interruptor maestro — PROTECDATA_ENABLED

El servicio tiene un flag que lo desconecta completamente sin tocar código:

```env
# .env
PROTECDATA_ENABLED=false   # QA / staging — no consume API, no genera costos
PROTECDATA_ENABLED=true    # Producción — flujo completo activo
```

**Comportamiento con `false`:**
- `iniciarLote()` crea el lote y los documentos en BD, pero **no llama a la API**. Los documentos quedan con `protecdata_process_id = null`.
- El callback responde `200 OK` inmediatamente sin procesar nada.
- El log registra el evento en ambos casos para trazabilidad.

La configuración completa vive en [Config/config.php](Config/config.php):

```php
'protecdata' => [
    'url'          => env('PROTECDATA_URL', ''),
    'username'     => env('PROTECDATA_USERNAME', ''),
    'password'     => env('PROTECDATA_PASSWORD', ''),
    'callback_url' => env('PROTECDATA_CALLBACK_URL', ''),
    'company_name' => env('PROTECDATA_COMPANY_NAME', ''),
    'notification' => env('PROTECDATA_NOTIFICATION', '4'), // 1=Email 2=SMS 3=WA 4=SMS+WA
],
```

El flag `enabled` se lee desde `general_settings.protecdata_enabled` (BD) y se controla desde el panel admin, sin necesidad de tocar el `.env`.

---

## Modelo de datos

### El problema de N contratos

Un usuario puede tener múltiples contratos que firmar, uno por cada empresa/plantilla registrada en `contract_templates`. ProtecData trata cada PDF como un proceso independiente con su propio `id_proceso` y su propio callback. Se necesitan tres niveles:

```
contract_templates              ← catálogo de plantillas por empresa
users
  └── signature_batches         ← lote que agrupa todos los contratos del usuario
        └── signature_documents ← un registro por contrato/empresa
```

### Regla de completitud del lote

| `signed_docs` | `status` del batch |
|---|---|
| 0 | `pending` |
| > 0 pero < `total_docs` | `partial` |
| == `total_docs` | `completed` |

El status se recalcula con `COUNT` en BD (no con incremento manual) para evitar race conditions cuando dos callbacks llegan casi simultáneamente.

---

## Tablas

### `contract_templates`

Catálogo de plantillas de contrato. Cada fila representa un contrato que debe generar y enviarse a firma para todo usuario que se registre.

```sql
id               BIGINT PK
company_name     VARCHAR       -- nombre de la empresa emisora del contrato
contract_type    ENUM('register')  -- tipo de contrato (lista cerrada)
blade_view       VARCHAR       -- vista Blade que renderiza el HTML, ej: 'customer::contracts.sagrilaft'
filename_prefix  VARCHAR       -- prefijo del PDF, ej: 'empresa_a_sagrilaft'
                               --   → genera: empresa_a_sagrilaft_{userId}.pdf
                               --   →  firmado: empresa_a_sagrilaft_{userId}_firmado.pdf
is_active        BOOLEAN DEFAULT true  -- false = desactivada sin eliminar
created_at, updated_at

INDEX (is_active, company_name)
```

**Tipos de contrato válidos** (constantes en `ContractTemplate`):

| Constante | Valor | Descripción |
|---|---|---|
| `TYPE_REGISTER` | `'REGISTER'` | Contrato de registro / vinculación inicial |

Para añadir un nuevo tipo: agregar la constante en el modelo, añadir el valor al `ENUM` en una nueva migración, y crear la vista Blade correspondiente.

### `signature_batches`

```sql
id            BIGINT PK
user_id       FK → users (cascade delete)
trigger       VARCHAR(50)  -- 'registration' | 'data_update' | 'manual'
status        ENUM('pending', 'partial', 'completed')  DEFAULT 'pending'
total_docs    TINYINT UNSIGNED  -- cuántos documentos componen el lote
signed_docs   TINYINT UNSIGNED  -- cuántos han sido firmados (actualizado por recalculateStatus)
created_at, updated_at

INDEX (user_id, status)
```

### `signature_documents`

```sql
id                     BIGINT PK
signature_batch_id     FK → signature_batches (cascade delete)
company_name           VARCHAR  -- nombre de la empresa del contrato
contract_type          VARCHAR(100)  -- ej: 'register'
original_filename      VARCHAR  -- PDF sin firmar,  ej: empresa_a_sagrilaft_42.pdf
signed_filename        VARCHAR  -- PDF firmado,     ej: empresa_a_sagrilaft_42_firmado.pdf
protecdata_process_id  VARCHAR  NULLABLE UNIQUE  -- NULL si ENABLED=false o si el envío falló
status                 ENUM('pending', 'signed', 'rejected')  DEFAULT 'pending'
signed_at              TIMESTAMP NULLABLE
pdf_local_path         VARCHAR NULLABLE  -- ruta en storage del PDF firmado (llega en callback)
created_at, updated_at

INDEX (signature_batch_id, status)
INDEX (protecdata_process_id)  -- búsqueda rápida en el callback
```

---

## Archivos del módulo

```
Modules/Customer/
├── Config/
│   └── config.php                                    ← credenciales ProtecData
├── Database/Migrations/
│   ├── 2026_04_08_000001_create_signature_batches_table.php
│   ├── 2026_04_08_000002_create_signature_documents_table.php
│   └── 2026_04_09_000001_create_contract_templates_table.php
├── Entities/
│   ├── ContractTemplate.php                          ← catálogo de plantillas (TYPE_* + CONTRACT_TYPES)
│   ├── SignatureBatch.php                            ← lote de firmas, recalculateStatus()
│   └── SignatureDocument.php                         ← documento individual
├── Services/
│   ├── ContractBuilderService.php                    ← genera PDFs y devuelve array $contratos
│   └── ProtecdataService.php                         ← API wrapper + iniciarLote()
├── Http/Controllers/
│   └── ProtecdataCallbackController.php              ← receptor del POST de ProtecData
├── Resources/views/contracts/
│   ├── base_layout.blade.php                         ← layout HTML/CSS compartido
│   └── sagrilaft.blade.php                           ← plantilla de ejemplo (SAGRILAFT/PTEE)
└── Routes/
    └── api.php                                       ← POST /api/protecdata/callback
```

---

## API de ProtecData — Endpoints

Base URL: `PROTECDATA_URL` en `.env`.

### 1. Autenticación

```
POST {url}/api/users/authenticate
Content-Type: application/json

{ "username": "...", "password": "..." }

→ { "token": "eyJ..." }
```

El token tiene vida útil limitada. Se obtiene uno fresco antes de cada lote (`iniciarLote` lo llama internamente).

### 2. Subir PDF

```
POST {url}/api/Transaccion/upload/doc
Authorization: Bearer {token}
Content-Type: multipart/form-data

campo "file" = binario del PDF

→ { "id": "uuid-documento" }   // documentoid
```

El archivo se lee desde Laravel Storage (`Storage::get($storagePath)`) — no se envía una URL, sino el contenido binario.

### 3. Crear proceso de firma

```
POST {url}/api/Transaccion/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "documentoid":   "uuid-documento",
  "Firmantes":     [{ "Email", "Indicativo", "Telefono", "Nombre", "Pais" }],
  "NombreCreador": "Nombre Empresa",
  "Modificar":     "false",
  "Callback":      "https://tusitio.com/api/protecdata/callback",
  "Notificacion":  "4"
}

→ { "id": "uuid-proceso" }   // protecdata_process_id — guardar en signature_documents
```

**`Notificacion`:** `1`=Email · `2`=SMS · `3`=WhatsApp · `4`=SMS+WhatsApp

---

## Callback — Recepción del resultado

ProtecData hace `POST` a `PROTECDATA_CALLBACK_URL` cuando el usuario termina de firmar **un documento específico**. No hay un callback global de lote — cada PDF tiene el suyo.

**Ruta registrada:** `POST /api/protecdata/callback` → `ProtecdataCallbackController@handle`  
Sin middleware de autenticación de sesión (es una petición entrante de servicio externo).

**Payload recibido:**
```json
{
  "id":     "uuid-proceso",
  "estado": "True",
  "url":    "https://storage.azure.../documento-firmado.pdf"
}
```

**Lógica del handler:**

```
1. Si PROTECDATA_ENABLED=false → responder 200, no procesar
2. Buscar SignatureDocument WHERE protecdata_process_id = input.id
3. Si estado == "True":
   a. Resolver carpeta Contratos/ del usuario en el File Explorer
      (ensureUserFolder → ensureYearFolder → ensureStandardSubfolder(FolderType::Contracts))
   b. Descargar PDF firmado desde input.url
   c. Guardar en storage: {contratosFolder.getPhysicalPath()}/{signed_filename}
   d. Crear registro en folder_files (visible en File Explorer del usuario)
   e. signature_document.status = 'signed', signed_at = now(), pdf_local_path = path
   f. batch.recalculateStatus() → COUNT en BD → actualiza signed_docs y status
4. Responder { "ok": true }
```

---

## Dónde se guardan los archivos

Los PDFs viven dentro de la carpeta digital del usuario en el File Explorer:

```
Master/
└── Empresarios/
    └── {nombre usuario}/
        └── {año}/
            ├── Registro/    ← cédula, RUT (ya existente)
            └── Contratos/   ← PDFs de contratos ← AQUÍ
```

**Ruta física** (basada en IDs, inmutable aunque se renombre la carpeta):
```
storage/app/fileExplorer/{master_id}/{empresarios_id}/{user_folder_id}/{year_id}/{contratos_id}/
```

**Archivos por plantilla** (2 por empresa → 2N archivos para N plantillas activas):

| Momento | Nombre | Descripción |
|---|---|---|
| Al registrar | `{filename_prefix}_{userId}.pdf` | Sin firmar — generado por ContractBuilderService y subido a ProtecData |
| Al recibir callback | `{filename_prefix}_{userId}_firmado.pdf` | Firmado — descargado de Azure |

`signature_documents.pdf_local_path` y `folder_files.path` apuntan al mismo archivo físico. No hay duplicación.

---

## Flujo completo (ENABLED=true)

```
registerCustomer()
  └── createCompleteCustomer()       → usuario creado en BD
  └── handleFilesUpload()            → carpeta digital + Registro/ creada
  └── DB::commit()
  └── iniciarProcesoFirma()          → fuera de la transacción
        └── ContractBuilderService::buildContratos($user)
              ├── ContractTemplate::active()->get()       → plantillas de la BD
              ├── resolveContratosFolder()                → Master/.../Contratos/
              └── por cada plantilla:
                    ├── view(blade_view)->render()         → HTML del contrato
                    ├── PDF::loadHTML(...)                 → mPDF
                    ├── Storage::put(path, pdf)            → archivo en storage
                    ├── FolderFile::create(...)            → visible en File Explorer
                    └── $contratos[] = [...]
        └── ProtecdataService::iniciarLote($user, $contratos, TRIGGER_REGISTRATION)
              ├── SignatureBatch::create()                → lote en BD
              ├── authenticate()                         → token JWT
              └── por cada contrato:
                    ├── uploadDocument()                  → POST PDF binario → documentoid
                    ├── createProcess()                   → POST create → protecdata_process_id
                    └── SignatureDocument::create()        → documento en BD

[tiempo después — usuario firma desde su dispositivo]

POST /api/protecdata/callback
  └── ProtecdataCallbackController::handle()
        └── procesarFirmaCompletada()
              ├── ensureUserFolder / yearFolder / contratosFolder
              ├── Storage::put(path, pdfContent)
              ├── FolderFile::create()
              ├── document.update(status=signed)
              └── batch.recalculateStatus()
```

## Flujo con ENABLED=false (QA)

```
registerCustomer()
  └── [igual que arriba hasta iniciarProcesoFirma()]
  └── iniciarProcesoFirma()
        └── ContractBuilderService::buildContratos($user)
              └── [genera PDFs y los guarda en storage igualmente]
        └── ProtecdataService::iniciarLote()
              ├── SignatureBatch::create()         → lote en BD (trazabilidad)
              └── [guard: enabled=false]
                    └── SignatureDocument::create() × N  → todos con process_id=NULL
                    → NO se llama a la API

POST /api/protecdata/callback (si llegara alguno)
  └── [guard: enabled=false]
        └── Log::info(...)
        └── return { "ok": true }   ← sin procesar nada
```

---

## Añadir un nuevo tipo de contrato

1. Agregar la constante en `ContractTemplate`:
   ```php
   public const TYPE_VINCULACION = 'vinculacion';
   // Añadirla también a CONTRACT_TYPES
   ```

2. Crear una migración que extienda el `ENUM`:
   ```php
   DB::statement("ALTER TABLE contract_templates MODIFY contract_type ENUM('register','vinculacion')");
   ```

3. Crear la vista Blade en `Resources/views/contracts/vinculacion.blade.php` extendiendo `base_layout`.

4. Insertar la fila en `contract_templates` (seeder o migración de datos):
   ```php
   ContractTemplate::create([
       'company_name'    => 'Empresa B',
       'contract_type'   => ContractTemplate::TYPE_VINCULACION,
       'blade_view'      => 'customer::contracts.vinculacion',
       'filename_prefix' => 'empresa_b_vinculacion',
       'is_active'       => true,
   ]);
   ```

---

## Queries útiles

```php
use Modules\Customer\Entities\SignatureBatch;

// Usuarios que firmaron todo
SignatureBatch::where('status', SignatureBatch::STATUS_COMPLETED)->with('user')->get();

// Usuarios con firma incompleta
SignatureBatch::where('status', SignatureBatch::STATUS_PARTIAL)->with('user', 'documents')->get();

// Usuarios que no han firmado nada
SignatureBatch::where('status', SignatureBatch::STATUS_PENDING)->with('user')->get();

// Documentos pendientes de un usuario específico
$batch = SignatureBatch::where('user_id', $userId)->latest()->first();
$pendientes = $batch->documents()->where('status', SignatureDocument::STATUS_PENDING)->get();

// Documentos cuyo envío a ProtecData falló (candidatos a reintento)
SignatureDocument::whereNull('protecdata_process_id')
    ->where('status', SignatureDocument::STATUS_PENDING)
    ->with('batch.user')
    ->get();
```

---

## Notas de seguridad

- **El callback no lleva autenticación de sesión** — es una petición entrante de ProtecData. Considerar agregar IP whitelist o un secret token en la URL del callback para verificar la autenticidad.
- **Fallo de un documento no aborta el lote** — si `uploadDocument` o `createProcess` falla para un contrato específico, ese `SignatureDocument` queda con `protecdata_process_id = null` y el resto del lote continúa.
- **Fallo de una plantilla no aborta la generación** — si `ContractBuilderService` falla al generar un PDF individual, registra el error en el log y continúa con las demás plantillas.
- **El proceso de firma es llamado fuera de la transacción DB** — si ProtecData falla, el usuario ya quedó creado correctamente. El error se registra en el log sin propagarse.
