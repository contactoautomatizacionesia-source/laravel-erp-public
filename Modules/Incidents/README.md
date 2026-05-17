# Módulo Incidents — Novedades de Inventario

> **Ubicación:** `Modules/Incidents/`
> **Estado:** Implementado — pendiente de ejecutar migraciones.
> **Documentación de negocio:** `Modules/Incidents/Docs/`

---

## Qué hace este módulo

Centraliza el registro, seguimiento y resolución de **novedades de inventario**: diferencias detectadas entre lo esperado y lo real, ya sea en transferencias entre sedes o en conteos físicos. Garantiza trazabilidad completa, asigna responsabilidades y fuerza una resolución formal con consecuencias reales en el inventario o en las cuentas.

---

## Dos casos de creación automática

### Caso 1 — Transferencia con faltante

El asesor **destino** acepta una transferencia y reporta menos unidades de las despachadas.

**Flujo:**
1. El módulo CostCenter detecta la discrepancia en `CostCenterInventoryService::recordDiscrepancy()`.
2. Dispara el evento `TransferDiscrepancyCreated`.
3. El listener `CreateIncidentFromTransfer` llama a `IncidentCreationService::createFromTransfer()`.
4. Se crea la novedad con `status = awaiting_statement`.
5. La sede **origen** tiene un plazo configurable (default 48 h) para pronunciarse.

### Caso 2 — Conteo con diferencia

El asesor realiza un conteo físico y el sistema detecta faltante vs. stock teórico.

**Flujo:**
1. `InventoryCountService::runR1R2()` determina `status = 'incorrect'`.
2. Dispara el evento `InventoryCountDifferenceDetected`.
3. El listener `CreateIncidentFromInventoryCount` itera cada línea con faltante y llama a `IncidentCreationService::createFromInventoryCount()`.
4. Se crea una novedad **por producto** con `status = pending` (va directo al administrador).

---

## Ciclo de vida de una novedad

```
[Transferencia]                    [Conteo]
      │                               │
      ▼                               ▼
awaiting_statement  ──────────────► pending
      │                               │
      │  Origen se pronuncia          │  Admin investiga
      ▼                               ▼
  acknowledged ──► closed       under_investigation
  (auto-cierre)                       │
                                      ├── advisor    ──► closed
                                      ├── organization ► closed
                                      └── voided     ──► voided
```

**Estados posibles:**

| Estado | Descripción |
|--------|-------------|
| `pending` | Generada desde conteo. Esperando que el admin investigue. |
| `awaiting_statement` | Generada desde transferencia. Esperando pronunciamiento de sede origen. |
| `under_investigation` | El admin está investigando. Origen rechazó o plazo venció. |
| `closed` | Resuelta formalmente. |
| `voided` | Anulada por el administrador con justificación. |

---

## Estructura de archivos

```
Modules/Incidents/
│
├── Console/
│   ├── EscalateIncidentsCommand.php       → php artisan incidents:escalate
│   └── SendIncidentRemindersCommand.php   → php artisan incidents:send-reminders
│
├── Database/Migrations/
│   ├── 2026_04_13_000001_create_incident_settings_table.php
│   ├── 2026_04_13_000002_create_incidents_table.php
│   ├── 2026_04_13_000003_create_incident_evidences_table.php
│   ├── 2026_04_13_000004_create_incident_audit_logs_table.php
│   ├── 2026_04_13_000005_create_cash_closing_incidents_table.php
│   └── 2026_04_13_000006_create_incidents_menu_and_permissions.php
│
├── Docs/                                  → Documentación de negocio original
│
├── Entities/
│   ├── Traits/HasUuid.php                 → Genera UUID automáticamente al crear
│   ├── Incident.php                       → Modelo principal (UUID PK)
│   ├── IncidentEvidence.php               → Archivos adjuntos (solo insert)
│   ├── IncidentAuditLog.php               → Log inmutable (solo insert)
│   ├── IncidentSetting.php                → Configuración singleton
│   └── CashClosingIncident.php            → Vínculo con cierre de caja
│
├── Http/
│   ├── Controllers/
│   │   ├── IncidentController.php         → CRUD + acciones de flujo
│   │   ├── IncidentSettingController.php  → Configuración (singleton)
│   │   └── EvidenceController.php         → Upload de evidencias
│   └── Requests/
│       ├── SubmitStatementRequest.php
│       ├── ResolveIncidentRequest.php
│       └── UpdateIncidentSettingRequest.php
│
├── Jobs/
│   ├── EscalateOverdueStatementsJob.php   → Escala novedades vencidas
│   └── SendStatementReminderJob.php       → Envía recordatorio antes del plazo
│
├── Listeners/
│   ├── CreateIncidentFromTransfer.php     → Escucha TransferDiscrepancyCreated
│   └── CreateIncidentFromInventoryCount.php → Escucha InventoryCountDifferenceDetected
│
├── Providers/
│   ├── IncidentsServiceProvider.php       → Boot principal del módulo
│   ├── EventServiceProvider.php           → Registra listeners de eventos
│   └── RouteServiceProvider.php           → Registra rutas web/api
│
├── Repositories/
│   ├── IncidentRepository.php             → Queries + generateSequentialCode()
│   ├── IncidentSettingRepository.php      → Acceso al singleton
│   └── IncidentAuditLogRepository.php     → Solo inserción (nunca update/delete)
│
├── Resources/
│   ├── lang/es/ y lang/en/
│   │   ├── menu.php                       → Traducciones del menú lateral
│   │   └── messages.php                   → Todos los textos de la UI
│   └── views/
│       ├── index.blade.php                → Lista con métricas + filtros + DataTable
│       ├── show.blade.php                 → Detalle completo + acciones
│       ├── settings.blade.php             → Formulario de configuración
│       └── components/
│           ├── status_badge.blade.php
│           ├── type_badge.blade.php
│           ├── statement_modal.blade.php  → Modal para pronunciamiento de origen
│           ├── resolve_modal.blade.php    → Modal para resolución del admin
│           ├── void_modal.blade.php       → Modal para anulación
│           ├── evidence_modal.blade.php   → Modal para subir evidencias
│           └── closing_modal.blade.php    → Modal para vincular cierre de caja
│
├── Routes/
│   └── web.php                            → Todas las rutas del módulo
│
└── Services/
    ├── IncidentCreationService.php        → Crea novedades desde eventos
    ├── StatementService.php               → Pronunciamiento de sede origen
    ├── ResolutionService.php              → Resolución del administrador
    ├── CashClosingLinkService.php         → Vincula novedad a cierre de caja
    └── EvidenceService.php                → Upload y registro de evidencias
```

---

## Base de datos

### `incident_settings` — Configuración global (singleton)

| Campo | Default | Descripción |
|-------|---------|-------------|
| `statement_deadline_hours` | 48 | Horas que tiene origen para pronunciarse |
| `auto_escalate_on_deadline` | true | Escala automáticamente al vencer el plazo |
| `send_email_notifications` | true | Activa notificaciones por correo |
| `send_system_notifications` | true | Activa notificaciones internas |
| `send_deadline_reminder` | true | Envía recordatorio antes del vencimiento |
| `reminder_hours_before` | 24 | Horas antes del plazo para el recordatorio |
| `price_reference` | `public_price` | Precio usado para calcular el valor de la novedad |

### `incidents` — Tabla principal

- **PK:** UUID generado automáticamente con el trait `HasUuid`.
- **`sequential_code`:** Formato `NOV-XXXX`, generado en `IncidentRepository::generateSequentialCode()`.
- **`total_value`:** Columna generada por MySQL (`storedAs`): `missing_units * public_price_snapshot`. No se puede modificar.
- **`public_price_snapshot`:** Precio capturado al momento de crear la novedad. **Inmutable.**
- **`source_type / source_id`:** Referencia polimórfica al documento fuente (`cost_center_transfer` o `inventory_count`). **Sin FK directa** para no acoplar módulos.
- **`responsible_branch_id / origin_branch_id`:** FK a `cost_centers.id` (no `branches`).
- **`cash_closing_id`:** FK opcional a `cash_closings.id`. Sin constraint explícita hasta confirmar que esa tabla existe.

### `incident_audit_logs` — Log inmutable

Protegida por **dos triggers MySQL** (`prevent_incident_audit_log_update` y `prevent_incident_audit_log_delete`) creados en la migración. Cualquier intento de `UPDATE` o `DELETE` directo sobre la tabla lanza un error `SQLSTATE 45000`. El repositorio `IncidentAuditLogRepository` usa `DB::table()->insert()` directo para evitar que cualquier Observer intente un update posterior.

### `incident_evidences` — Evidencias adjuntas

Sin `updated_at`. Los archivos se guardan en el disco `public` de Laravel bajo `incidents/evidences/{incident_id}/`. Accesibles vía `Storage::disk('public')->url($path)`.

---

## Rutas disponibles

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| GET | `/incidents` | `incidents.index` | Lista con métricas y DataTable |
| GET | `/incidents/get-data` | `incidents.get-data` | JSON para DataTables (AJAX) |
| GET | `/incidents/metrics` | `incidents.metrics` | JSON con conteos por estado |
| GET | `/incidents/settings` | `incidents.settings` | Vista de configuración |
| POST | `/incidents/settings` | `incidents.settings.update` | Guarda configuración |
| GET | `/incidents/{id}` | `incidents.show` | Detalle completo |
| POST | `/incidents/{id}/statement` | `incidents.statement` | Pronunciamiento de origen |
| POST | `/incidents/{id}/resolve` | `incidents.resolve` | Resolución del admin |
| POST | `/incidents/{id}/void` | `incidents.void` | Anulación |
| POST | `/incidents/{id}/link-closing` | `incidents.link-closing` | Vincula a cierre de caja |
| POST | `/incidents/{id}/evidence` | `incidents.evidence.store` | Sube evidencia |

Todas las rutas tienen middleware `['auth', 'admin']`.

---

## Servicios de negocio

### `IncidentCreationService`

**Guard de duplicados:** antes de crear, verifica con `IncidentRepository::hasActiveForSource()` que no exista ya una novedad activa para el mismo `source_type + source_id`. Si ya existe, retorna la existente sin crear duplicado.

```php
// Desde transferencia
$service->createFromTransfer([
    'transferId', 'originBranchId', 'originUserId',
    'destinationBranchId', 'destinationUserId',
    'productId', 'productName', 'publicPrice', 'missingUnits'
]);

// Desde conteo (una llamada por producto con faltante)
$service->createFromInventoryCount([
    'countId', 'costCenterId', 'userId',
    'productId', 'productName', 'publicPrice', 'missingUnits'
]);
```

### `StatementService`

Procesa el pronunciamiento de la sede origen (solo aplica a novedades de tipo `transfer`).

- **`acknowledged`:** Requiere al menos una evidencia con `actor_role = origin` ya adjunta. Cierra la novedad automáticamente. Marca `resolution_party = organization` (reversión de inventario pendiente de implementar como evento separado).
- **`rejected`:** Pasa la novedad a `under_investigation` y notifica al admin.
- Valida que `NOW() < statement_expires_at` antes de aceptar cualquier pronunciamiento.

### `ResolutionService`

Solo para administradores. Acepta `resolution_party`:
- `advisor` → Cerrada. El asesor debe subsanar comprando el producto.
- `organization` → Cerrada. La organización asume la pérdida (salida de inventario — stub pendiente).
- `voided` → Anulada. Equivalente a llamar `void()`.

Requiere `resolution_notes` no vacío (mínimo 10 caracteres via `ResolveIncidentRequest`).

### `CashClosingLinkService`

Vincula novedades con `status = closed` a un cierre de caja. Crea el registro en `cash_closing_incidents` con `value_snapshot = incident.total_value` y actualiza `incidents.cash_closing_id`. Una novedad solo puede pertenecer a un cierre (restricción UNIQUE en `cash_closing_incidents.incident_id`).

### `EvidenceService`

Guarda el archivo en `storage/app/public/incidents/evidences/{incident_id}/` y crea el registro en `incident_evidences`. Solo acepta subidas sobre novedades abiertas (`isOpen() = true`).

---

## Jobs programados

Ambos jobs deben registrarse en `app/Console/Kernel.php`:

```php
$schedule->command('incidents:escalate')->hourly()->withoutOverlapping();
$schedule->command('incidents:send-reminders')->hourly()->withoutOverlapping();
```

| Command | Job | Qué hace |
|---------|-----|----------|
| `incidents:escalate` | `EscalateOverdueStatementsJob` | Busca novedades con `status = awaiting_statement` y `statement_expires_at <= NOW()`. Las pasa a `under_investigation` y registra en el log con `actor_label = 'Sistema'`. |
| `incidents:send-reminders` | `SendStatementReminderJob` | Busca novedades con `statement_reminder_sent = false` cuyo vencimiento está dentro de `reminder_hours_before`. Marca `statement_reminder_sent = true`. El envío real de notificaciones tiene un TODO pendiente de integrar con el servicio de notificaciones del CRM. |

---

## Integración con módulos externos

### Módulos que disparan eventos (modificados de forma aditiva)

**`Modules/CostCenter/Services/CostCenterInventoryService.php`**
- Método modificado: `recordDiscrepancy()` (línea ~638)
- Adición: `Event::dispatch(new TransferDiscrepancyCreated(...))`
- Evento creado: `Modules/CostCenter/Events/TransferDiscrepancyCreated.php`

**`Modules/InventoryCount/Services/InventoryCountService.php`**
- Método modificado: `runR1R2()` (línea ~151)
- Adición: `Event::dispatch(new InventoryCountDifferenceDetected(...))` cuando `$hasDifferences = true`
- Evento creado: `Modules/InventoryCount/Events/InventoryCountDifferenceDetected.php`

Ambos dispatches están envueltos en `try/catch` para que un fallo en el módulo Incidents **nunca rompa** el flujo original de transferencias ni conteos.

### Módulos que este módulo lee (sin modificar)

| Módulo | Para qué |
|--------|----------|
| `CostCenter` | Entidades `CostCenterTransfer`, `CostCenterTransferItem`, `CostCenter` |
| `InventoryCount` | Entidades `InventoryCount`, `InventoryCountDetail` |
| `Product` | Resolución de `ProductSku` para precio público en los listeners |
| `CashManager` / global | Trait `HasUuid` (copiado a `Entities/Traits/HasUuid.php`) |

### Módulos pendientes de integrar (stubs)

| Integración | Dónde | Estado |
|-------------|-------|--------|
| Reversión de inventario al `acknowledged` | `StatementService::handleAcknowledged()` | TODO comentado |
| Salida de inventario cuando `organization` asume | `ResolutionService::resolve()` | TODO comentado |
| Notificaciones (correo + sistema) | `SendStatementReminderJob` y listeners | TODO comentado |

---

## Menú y permisos

El módulo se registra bajo el grupo `product.product_manage` (mismo que InventoryCount).

**Menú lateral:**
```
Novedades (ti-alert-circle)
├── Lista        → incidents.index
└── Configuración → incidents.settings
```

**Permisos granulares (type 3):**
- `Incident View` → `incidents.show`
- `Incident Resolve` → `incidents.resolve`
- `Incident Void` → `incidents.void`
- `Incident Settings Save` → `incidents.settings.update`

---

## Control de acceso (diseño)

| Acción | Asesor | Admin |
|--------|--------|-------|
| Ver novedades de su sede | ✅ | ✅ |
| Ver novedades de todas las sedes | ✗ | ✅ |
| Adjuntar evidencias | ✅ | ✅ |
| Pronunciarse como origen | ✅ | ✗ |
| Resolver / anular | ✗ | ✅ |
| Vincular a cierre de caja | ✗ | ✅ |
| Gestionar configuración | ✗ | ✅ |

> #TODO: La lógica de filtrado por sede para asesores está pendiente de implementar en `IncidentRepository::getBaseQuery()` tomando como referencia el patrón de `InventoryCountController`.

---

## Pasos para poner en producción

```bash
# 1. Ejecutar migraciones (el usuario las revisa y las corre manualmente)
php artisan migrate

# 2. Crear el symlink de storage si no existe
php artisan storage:link

# 3. Verificar que el módulo carga sin errores
php artisan route:list | grep incidents

# 4. Registrar los jobs en el scheduler (app/Console/Kernel.php)
# $schedule->command('incidents:escalate')->hourly()->withoutOverlapping();
# $schedule->command('incidents:send-reminders')->hourly()->withoutOverlapping();

# 5. Probar manualmente
php artisan incidents:escalate
php artisan incidents:send-reminders
```

---

## Decisiones de diseño relevantes

| Decisión | Razón |
|----------|-------|
| UUID como PK en `incidents` | Coherencia con otros módulos del CRM que usan UUIDs para registros sensibles. |
| `total_value` como columna generada por MySQL | El valor es inmutable por regla de negocio. La columna generada garantiza que nunca se pueda actualizar sin cambiar `missing_units` o `public_price_snapshot`, que tampoco deberían cambiar. |
| Triggers en `incident_audit_logs` | La inmutabilidad del log es un requisito de auditoría. El trigger actúa como última línea de defensa incluso ante accesos directos a la base de datos. |
| Eventos en lugar de llamadas directas | Desacopla el módulo Incidents de CostCenter e InventoryCount. Si el módulo Incidents está desactivado, los eventos simplemente no tienen listeners y los flujos originales no se ven afectados. |
| `try/catch` alrededor de cada `Event::dispatch()` | Un fallo en la creación de novedades **nunca debe bloquear** una transferencia o un conteo. La novedad puede crearse manualmente después. |
| Referencia polimórfica sin FK en `source_type/source_id` | Evita acoplamientos de migración entre módulos que pueden no existir en todos los entornos. |
| `cash_closing_id` sin FK constraint | La tabla `cash_closings` pertenece a otro módulo y puede no existir aún. La FK se puede agregar posteriormente con una migración separada. |
