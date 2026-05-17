🧱 Backend

Construya e implemente el modelo de datos necesario para el funcionamiento del mockup base.

Nota: La tabla cash_closings o cierre de caja ya existe https://project-development-team.atlassian.net/browse/LIF-75?atlOrigin=eyJpIjoiMGY2MDkxYTA3ODgzNDM1ZThhZWZiN2QyYjY5MThmZDEiLCJwIjoiaiJ9  en el CRM. Este módulo no la reemplaza; únicamente agrega una extensión (cash_closing_incidents) para vincular novedades a un cierre existente.

Diagrama de relaciones (ERD simplificado)

[products] ──────────────────────────────────────────────────┐
[branches] ──────────────────────────────────────────────────┤
[users]    ──────────────────────────────────────────────────┤
                                                             ▼
[transfers] ──► (evento) ──┐                         [incidents]
[inventory_counts] ─(evt)──┴──► crea ──────────────►  (tabla principal)
                                                             │
                              ┌──────────────────────────────┤
                              │                              │
                              ▼                              ▼
                   [incident_evidences]            [incident_audit_logs]
                   (archivos adjuntos)             (log inmutable)

[incidents] ──► (FK opcional) ──► [cash_closings]  (tabla existente)
                                         │
                                         ▼
                               [cash_closing_incidents]
                               (extensión: novedades de un cierre)

[incident_settings]  (configuración global, registro único)

Tablas

incidents

Descripción: Tabla principal del módulo. Registra cada novedad detectada por diferencia en transferencias de inventario o conteos. Es el núcleo de todo el flujo de control y responsabilidad.

Columna

Tipo

Restricciones

Descripción

id

UUID

PK, NOT NULL

Identificador único del registro. Formato de presentación: NOV-XXXX.

sequential_code

VARCHAR(20)

NOT NULL, UNIQUE

Código legible generado secuencialmente. Ejemplo: NOV-0041.

incident_type

ENUM

NOT NULL

Origen de la novedad. Valores: transfer (transferencia de inventario) · inventory_count (conteo de inventario).

status

ENUM

NOT NULL, DEFAULT 'pending'

Estado actual en el ciclo de vida. Valores: pending · awaiting_statement · under_investigation · closed · voided.

source_type

ENUM

NOT NULL

Tipo del documento origen. Valores: transfer_id · inventory_count_id. Permite polimorfismo con source_id.

source_id

UUID

NOT NULL

ID del documento del CRM que generó la novedad (transferencia o conteo). Referencia polimórfica, no FK directa.

product_id

UUID

NOT NULL, FK → products.id

Producto al que corresponde la novedad.

product_name_snapshot

VARCHAR(255)

NOT NULL

Nombre del producto en el momento de crear la novedad. Inmutable para preservar historial.

public_price_snapshot

DECIMAL(18,2)

NOT NULL, CHECK > 0

Precio público del producto capturado en el momento de creación. No varía aunque el precio cambie después.

missing_units

INT

NOT NULL, CHECK > 0

Cantidad de unidades faltantes detectadas.

total_value

DECIMAL(18,2)

NOT NULL, GENERATED

Valor económico total de la novedad. Calculado como missing_units × public_price_snapshot. Inmutable tras creación.

responsible_branch_id

UUID

NOT NULL, FK → branches.id

Sede que responde por la novedad (sede destino en transferencias, sede del conteo en conteos).

responsible_user_id

UUID

NOT NULL, FK → users.id

Asesor asignado como responsable principal de la novedad.

origin_branch_id

UUID

NULLABLE, FK → branches.id

Sede origen del despacho. Solo aplica cuando incident_type = transfer.

origin_user_id

UUID

NULLABLE, FK → users.id

Asesor de la sede origen. Solo aplica cuando incident_type = transfer.

statement_deadline_hours

INT

NOT NULL

Copia del parámetro de plazo vigente al momento de creación. Se preserva para auditoría aunque la configuración cambie.

statement_expires_at

TIMESTAMP

NULLABLE

Fecha y hora límite para el pronunciamiento del origen. Calculado: created_at + statement_deadline_hours. Solo aplica cuando incident_type = transfer.

statement_reminder_sent

BOOLEAN

NOT NULL, DEFAULT false

Indica si ya se envió el recordatorio previo al vencimiento del plazo. Evita duplicados en el job de recordatorios.

statement_submitted_at

TIMESTAMP

NULLABLE

Fecha en que el origen se pronunció.

statement_type

ENUM

NULLABLE

Resultado del pronunciamiento. Valores: acknowledged (reconoce el error) · rejected (rechaza la responsabilidad).

resolution_party

ENUM

NULLABLE

Quién asume la responsabilidad según decisión del administrador. Valores: advisor · organization · voided.

resolution_notes

TEXT

NULLABLE

Justificación del administrador al momento de resolver. Requerido antes de cerrar.

resolved_at

TIMESTAMP

NULLABLE

Fecha y hora en que el administrador cerró la novedad.

resolved_by

UUID

NULLABLE, FK → users.id

Usuario administrador que ejecutó la resolución.

inventory_reversal_id

UUID

NULLABLE

ID del movimiento de inventario generado por el CRM al revertir unidades al origen. Referencia externa, no FK.

cash_closing_id

UUID

NULLABLE, FK → cash_closings.id

Referencia al cierre de caja al que pertenece esta novedad, si fue escalada.

created_at

TIMESTAMP

NOT NULL, DEFAULT NOW()

Fecha y hora de creación del registro.

updated_at

TIMESTAMP

NOT NULL

Fecha y hora de la última modificación. Actualizado automáticamente

Índices recomendados:

idx_incidents_status sobre status

idx_incidents_responsible_branch sobre responsible_branch_id

idx_incidents_source sobre (source_type, source_id)

idx_incidents_cash_closing sobre cash_closing_id

idx_incidents_expires_at sobre statement_expires_at (para el job de escalado)



incident_evidences

Descripción: Archivos y observaciones adjuntadas por cualquier actor durante el ciclo de vida de la novedad. Soporta múltiples adjuntos por novedad en distintos momentos. El registro es de solo inserción: no se eliminan evidencias una vez cargadas.

Columna

Tipo

Restricciones

Descripción

id

UUID

PK, NOT NULL

Identificador único del archivo adjunto.

incident_id

UUID

NOT NULL, FK → incidents.id ON DELETE RESTRICT

Novedad a la que pertenece esta evidencia.

uploaded_by

UUID

NOT NULL, FK → users.id

Usuario que adjuntó el archivo.

actor_role

ENUM

NOT NULL

Rol del actor al momento de adjuntar. Valores: destination (asesor destino) · origin (asesor origen) · admin (administrador).

file_url

VARCHAR(2048)

NOT NULL

URL del archivo en el servicio de almacenamiento (S3 o equivalente).

file_name

VARCHAR(255)

NOT NULL

Nombre original del archivo tal como fue subido por el usuario.

file_mime_type

VARCHAR(100)

NOT NULL

Tipo MIME del archivo. Ejemplo: image/jpeg, application/pdf.

file_size_bytes

BIGINT

NOT NULL

Tamaño del archivo en bytes.

notes

TEXT

NULLABLE

Observación escrita que acompaña al archivo.

created_at

TIMESTAMP

NOT NULL, DEFAULT NOW()

Fecha y hora de carga.

Índices recomendados:

idx_incident_evidences_incident sobre incident_id



incident_audit_logs

Descripción: Registro cronológico e inmutable de todos los eventos del ciclo de vida de una novedad. Ningún registro puede modificarse ni eliminarse. Es la fuente de verdad para auditoría y trazabilidad. Cada cambio de estado, acción de usuario o evento del sistema debe generar al menos una entrada.

Columna

Tipo

Restricciones

Descripción

id

UUID

PK, NOT NULL

Identificador único de la entrada de log.

incident_id

UUID

NOT NULL, FK → incidents.id ON DELETE RESTRICT

Novedad a la que corresponde este evento.

actor_label

VARCHAR(100)

NOT NULL

Nombre del actor tal como se mostrará en la UI. Ejemplo: Sistema, Carlos Ríos, Administrador.

user_id

UUID

NULLABLE, FK → users.id

Usuario que realizó la acción. Nulo cuando el actor es el sistema.

action

VARCHAR(255)

NOT NULL

Descripción corta del evento ocurrido. Ejemplo: Novedad generada automáticamente.

previous_status

ENUM

NULLABLE

Estado de la novedad antes del evento. Nulo si es el evento de creación.

new_status

ENUM

NULLABLE

Estado resultante tras el evento. Nulo si el evento no cambia el estado.

metadata

JSONB

NULLABLE

Datos adicionales del evento en formato JSON. Ejemplo: ID del movimiento de inventario, nombre del archivo adjunto.

created_at

TIMESTAMP

NOT NULL, DEFAULT NOW()

Fecha y hora del evento. Inmutable.

Restricciones adicionales: aplicar RULE o trigger de base de datos que bloquee UPDATE y DELETE sobre esta tabla.

Índices recomendados:

idx_incident_audit_logs_incident sobre incident_id

idx_incident_audit_logs_created_at sobre created_at



cash_closing_incidents

Descripción: Extensión de la tabla existente cash_closings. Vincula una novedad resuelta con el cierre de caja del período en el que se le hace seguimiento al cobro. No reemplaza ni altera la tabla cash_closings; agrega únicamente la relación y el snapshot del valor al momento de incluirse. Una novedad puede pertenecer a un solo cierre de caja.

Columna

Tipo

Restricciones

Descripción

id

UUID

PK, NOT NULL

Identificador único de la relación.

cash_closing_id

UUID

NOT NULL, FK → cash_closings.id ON DELETE RESTRICT

Cierre de caja al que se vincula la novedad. Referencia a tabla existente del CRM.

incident_id

UUID

NOT NULL, FK → incidents.id ON DELETE RESTRICT, UNIQUE

Novedad incluida. La restricción UNIQUE garantiza que una novedad no se duplique en varios cierres.

value_snapshot

DECIMAL(18,2)

NOT NULL

Valor de la novedad en el momento de vincularla al cierre. Copia de incidents.total_value para preservar el dato ante futuras modificaciones.

included_at

TIMESTAMP

NOT NULL, DEFAULT NOW()

Fecha y hora en que la novedad fue vinculada al cierre.

included_by

UUID

NOT NULL, FK → users.id

Usuario (sistema o administrador) que ejecutó la vinculación.

Índices recomendados:

idx_cci_cash_closing sobre cash_closing_id

idx_cci_incident sobre incident_id (ya cubierto por UNIQUE)



incident_settings

Descripción: Configuración global del módulo. Debe existir un único registro activo en todo momento. Los cambios quedan registrados para trazabilidad. Si la tabla está vacía, el sistema debe usar los valores por defecto documentados.

Columna

Tipo

Restricciones

Descripción

id

UUID

PK, NOT NULL

Identificador único. Solo debe existir un registro.

statement_deadline_hours

INT

NOT NULL, DEFAULT 48, CHECK > 0

Horas que tiene la sede origen para pronunciarse tras la creación de una novedad de transferencia.

auto_escalate_on_deadline

BOOLEAN

NOT NULL, DEFAULT true

Si es true, el sistema escala automáticamente al administrador cuando vence el plazo sin pronunciamiento.

send_email_notifications

BOOLEAN

NOT NULL, DEFAULT true

Habilita el envío de notificaciones por correo electrónico en todos los eventos del módulo.

send_system_notifications

BOOLEAN

NOT NULL, DEFAULT true

Habilita las notificaciones internas dentro del CRM.

send_deadline_reminder

BOOLEAN

NOT NULL, DEFAULT true

Habilita el envío de recordatorio previo al vencimiento del plazo de pronunciamiento.

reminder_hours_before

INT

NOT NULL, DEFAULT 24, CHECK > 0

Horas antes del vencimiento del plazo para disparar el recordatorio.

price_reference

ENUM

NOT NULL, DEFAULT 'public_price'

Precio que se usa para calcular el valor de la novedad. Valores: public_price · cost_price · transfer_price.

updated_at

TIMESTAMP

NOT NULL

Fecha y hora de la última modificación de la configuración.

updated_by

UUID

NOT NULL, FK → users.id

Usuario administrador que realizó el último cambio.

Relaciones entre tablas

incidents
├── FK: product_id              → products.id               (N:1) Un producto puede tener muchas novedades
├── FK: responsible_branch_id   → branches.id               (N:1) Una sede puede ser responsable de muchas novedades
├── FK: responsible_user_id     → users.id                  (N:1) Un asesor puede tener muchas novedades asignadas
├── FK: origin_branch_id        → branches.id               (N:1, nullable) Solo en novedades de tipo transfer
├── FK: origin_user_id          → users.id                  (N:1, nullable) Solo en novedades de tipo transfer
├── FK: resolved_by             → users.id                  (N:1, nullable) Admin que resolvió
├── FK: cash_closing_id         → cash_closings.id          (N:1, nullable) Cierre al que pertenece
│
├── HAS MANY: incident_evidences     (1:N) Una novedad tiene muchas evidencias
├── HAS MANY: incident_audit_logs    (1:N) Una novedad tiene muchos registros de log
└── HAS ONE:  cash_closing_incidents (1:1) Una novedad puede estar en un solo cierre de caja

incident_evidences
├── FK: incident_id  → incidents.id   (N:1)
└── FK: uploaded_by  → users.id       (N:1)

incident_audit_logs
├── FK: incident_id  → incidents.id   (N:1)
└── FK: user_id      → users.id       (N:1, nullable)

cash_closing_incidents
├── FK: cash_closing_id  → cash_closings.id   (N:1) Tabla existente del CRM
├── FK: incident_id      → incidents.id       (1:1, UNIQUE)
└── FK: included_by      → users.id           (N:1)

incident_settings
└── FK: updated_by  → users.id  (N:1)

 Endpoints esperados

Novedades

Método

Ruta

Descripción

GET

/api/v1/incidents

Listado paginado con filtros.

GET

/api/v1/incidents/metrics

Métricas para la cabecera del listado: conteo por estado y valor total pendiente.

GET

/api/v1/incidents/:id

Detalle completo de una novedad con sus relaciones.

POST

/api/v1/incidents

Crear novedad manualmente (solo administrador).

PATCH

/api/v1/incidents/:id/statement

Registrar el pronunciamiento de la sede origen (acknowledged o rejected).

PATCH

/api/v1/incidents/:id/resolve

Registrar la resolución del administrador.

PATCH

/api/v1/incidents/:id/void

Anular una novedad con justificación.

GET

/api/v1/incidents/:id/evidences

Listar todas las evidencias de una novedad.

POST

/api/v1/incidents/:id/evidences

Adjuntar archivo(s) a una novedad. Multipart/form-data.

GET

/api/v1/incidents/:id/audit-log

Obtener el log de auditoría completo de la novedad.

Parámetros de filtro para GET /incidents:

Parámetro

Tipo

Descripción

status

string

Filtrar por estado.

incident_type

string

transfer o inventory_count.

branch_id

UUID

Filtrar por sede responsable.

user_id

UUID

Filtrar por asesor responsable.

product_id

UUID

Filtrar por producto.

date_from

date

Fecha de creación desde.

date_to

date

Fecha de creación hasta.

q

string

Búsqueda libre sobre sequential_code, product_name_snapshot, nombre de sede o asesor.

page

int

Página actual. Default: 1.

per_page

int

Registros por página. Default: 20, máximo: 100.

order_by

string

Campo de ordenamiento. Default: created_at.

order_dir

string

asc o desc. Default: desc.

4.2 Cierres de caja – extensión de novedades

Método

Ruta

Descripción

GET

/api/v1/cash-closings/:id/incidents

Listar las novedades vinculadas a un cierre de caja existente.

POST

/api/v1/cash-closings/:id/incidents

Vincular una o varias novedades resueltas a un cierre de caja existente.

DELETE

/api/v1/cash-closings/:id/incidents/:incident_id

Desvincular una novedad de un cierre (solo si el cierre está en estado editable).

Los endpoints de gestión del cierre de caja (crear, aprobar, objetar) pertenecen al módulo existente del CRM y no son responsabilidad de este módulo.

4.3 Configuración

Método

Ruta

Descripción

GET

/api/v1/incidents/settings

Obtener la configuración activa del módulo.

PUT

/api/v1/incidents/settings

Actualizar la configuración. Solo administrador. Registra en incident_audit_logs con actor_label = Sistema.

Servicios de lógica de negocio

5.1 IncidentCreationService

Responsabilidad: Crear novedades automáticamente a partir de eventos del CRM.

Flujo:

Escuchar evento transfer.reception_confirmed del módulo de transferencias.

Escuchar evento inventory_count.difference_accepted del módulo de conteos.

Verificar que la diferencia de unidades sea mayor a cero. Si es cero, ignorar el evento.

Consultar el precio de referencia configurado en incident_settings.price_reference y obtener el valor del producto desde el módulo de productos.

Calcular total_value = missing_units × price_snapshot.

Determinar el estado inicial: awaiting_statement si incident_type = transfer; pending si incident_type = inventory_count.

Crear el registro en incidents con todos los campos requeridos.

Insertar entrada en incident_audit_logs con action = 'Incident created automatically'.

Emitir evento interno incident.created para el servicio de notificaciones.

Validaciones previas:

No crear una novedad si ya existe una activa (status != closed y status != voided) para el mismo source_type y source_id.

5.2 StatementService

Responsabilidad: Gestionar el pronunciamiento de la sede origen ante una novedad de transferencia.

Flujo para acknowledged (reconoce el error):

Validar que incident.status = awaiting_statement.

Validar que el usuario pertenece a origin_branch_id.

Validar que NOW() < statement_expires_at. Si venció, retornar error STATEMENT_DEADLINE_EXPIRED.

Validar que exista al menos una evidencia adjunta y una nota en el campo notes de la evidencia.

Actualizar statement_submitted_at, statement_type = 'acknowledged', status = 'closed'.

Llamar al servicio de inventario del CRM para ejecutar movimiento de reversión: acreditar missing_units a origin_branch_id con reference_type = 'incident' y reference_id = incident.id. Guardar el ID del movimiento en inventory_reversal_id.

Insertar en incident_audit_logs.

Emitir evento incident.acknowledged_and_closed.

Flujo para rejected (rechaza el error):

Validar status = awaiting_statement y plazo vigente.

Actualizar statement_submitted_at, statement_type = 'rejected', status = 'under_investigation'.

Insertar en incident_audit_logs.

Emitir evento incident.escalated_to_admin.

5.3 ResolutionService

Responsabilidad: Registrar la decisión del administrador sobre una novedad.

Flujo:

Validar que el usuario tiene rol administrator.

Validar que incident.status sea under_investigation o pending.

Validar que resolution_party y resolution_notes no sean nulos ni vacíos.

Actualizar resolution_party, resolution_notes, resolved_at, resolved_by, status = 'closed' (o voided si resolution_party = voided).

Si resolution_party = organization: emitir evento al módulo contable del CRM para registrar la baja correspondiente.

Insertar en incident_audit_logs.

Emitir evento incident.resolved.

5.4 DeadlineEscalationJob

Responsabilidad: Escalar automáticamente novedades cuyo plazo de pronunciamiento venció.
Frecuencia sugerida: cada hora.

Flujo:

Verificar que incident_settings.auto_escalate_on_deadline = true.

Consultar incidents donde status = 'awaiting_statement' y statement_expires_at <= NOW().

Para cada registro encontrado:

Actualizar status = 'under_investigation'.

Insertar en incident_audit_logs con actor_label = 'Sistema', action = 'Deadline expired. Automatically escalated to administrator'.

Emitir evento incident.deadline_expired.

5.5 DeadlineReminderJob

Responsabilidad: Enviar recordatorio al asesor origen antes de que venza el plazo.
Frecuencia sugerida: cada hora.

Flujo:

Verificar que incident_settings.send_deadline_reminder = true.

Consultar incidents donde status = 'awaiting_statement', statement_reminder_sent = false y statement_expires_at <= NOW() + reminder_hours_before.

Para cada registro encontrado:

Emitir evento incident.deadline_reminder.

Actualizar statement_reminder_sent = true para evitar duplicados.

Insertar entrada en incident_audit_logs.

5.6 CashClosingLinkService

Responsabilidad: Vincular novedades resueltas al cierre de caja del período correspondiente.

Flujo (vinculación manual o automática al generar cierre):

Verificar que el cierre de caja existe y está en estado editable (depende del estado definido por el módulo existente del CRM).

Verificar que cada incident_id recibido tenga status = 'closed' y resolution_party = 'advisor'.

Verificar que ninguna novedad tenga ya un cash_closing_id asignado.

Insertar registros en cash_closing_incidents con value_snapshot = incident.total_value.

Actualizar cash_closing_id en cada incident vinculado.

Insertar en incident_audit_logs por cada novedad vinculada.

El sistema debe generar notificaciones por correo y en el sistema, para esto integre el servicio de notificaciones actual y en conjunto con @Maria Fernanda L defina una plantilla para usar en este proceso.

Esta plantilla de correo debe ser alimentada con lo siguiente:

Consumidor de eventos que despacha notificaciones por los canales activos según incident_settings. Cada notificación enviada debe registrarse en incident_audit_logs.

Evento

Destinatarios

Canales

incident.created

Asesor responsable + administrador

Correo + sistema

incident.deadline_reminder

Asesor origen (origin_user_id)

Correo + sistema

incident.deadline_expired

Administrador

Correo + sistema

incident.acknowledged_and_closed

Administrador

Sistema

incident.escalated_to_admin

Administrador

Correo + sistema

incident.resolved

Asesor responsable

Correo + sistema

incident.voided

Asesor responsable

Sistema

incident.linked_to_cash_closing

Asesor responsable + administrador

Correo + sistema

Integraciones con módulos existentes del CRM

Módulo

Tipo

Descripción

Transfers

Consumidor de evento

Escuchar transfer.reception_confirmed. El módulo de novedades no modifica lógica de transferencias.

Inventory counts

Consumidor de evento

Escuchar inventory_count.difference_accepted. El módulo de novedades no modifica lógica de conteos.

Products

Llamada de lectura

GET /products/:id/active-public-price para capturar el precio al momento de la creación.

Inventory

Llamada de escritura

Ejecutar movimiento de reversión al reconocer error de origen. Payload: { product_id, branch_id, units, movement_type: 'reversal', reference_type: 'incident', reference_id }.

Cash closings

Extensión por FK

El módulo agrega la tabla cash_closing_incidents que referencia cash_closings.id. No modifica la tabla existente.

Users / Branches

Llamada de lectura

Consultar datos del asesor (email, nombre, rol) y sede para notificaciones y validaciones.

Este módulo debe estar y sus items debe estar limitados por rol.

Control de acceso por rol

Acción

Asesor destino

Asesor origen

Administrador

Ver novedades de su sede

✅

✅

✅

Ver novedades de todas las sedes

✗

✗

✅

Adjuntar evidencias como destino

✅

✗

✅

Pronunciarse como origen

✗

✅

✗

Crear novedad manual

✗

✗

✅

Investigar y resolver novedad

✗

✗

✅

Anular novedad

✗

✗

✅

Ver cierres de caja

✅ (propios)

✅ (propios)

✅

Vincular novedades a cierre

✗

✗

✅

Gestionar configuración del módulo

✗

✗

✅

Validaciones críticas

No crear novedad si ya existe una activa para el mismo source_type y source_id.

public_price_snapshot no puede ser nulo ni cero al momento de creación.

El pronunciamiento solo es válido si status = awaiting_statement y NOW() < statement_expires_at.

El pronunciamiento de tipo acknowledged requiere mínimo una evidencia adjunta con nota.

La resolución del administrador requiere resolution_party y resolution_notes no vacíos.

Una novedad solo puede vincularse a un cierre de caja si tiene status = closed y resolution_party = advisor.

Una novedad no puede vincularse a más de un cierre de caja (restricción UNIQUE en cash_closing_incidents.incident_id).

La tabla incident_audit_logs es de solo inserción. Aplicar restricción a nivel de base de datos.

El campo total_value no puede modificarse tras la creación del registro.

Orden de implementación sugerido

#

Tarea

1

Crear migraciones: tablas incidents, incident_evidences, incident_audit_logs, incident_settings, cash_closing_incidents.

2

Configurar restricciones, índices y trigger de solo inserción en incident_audit_logs.

3

Implementar GET /incidents y GET /incidents/:id (consulta sin lógica de negocio).

4

Implementar suscripción a eventos del módulo de transferencias y conteos.

5

Implementar IncidentCreationService con integración al módulo de productos.

6

Implementar StatementService con integración al módulo de inventario (reversión).

7

Implementar DeadlineEscalationJob y DeadlineReminderJob.

8

Implementar ResolutionService.

9

Implementar servicio de notificaciones con correo y notificación interna.

10

Implementar CashClosingLinkService y endpoints de extensión de cierres de caja.

11

Implementar endpoints de configuración (GET y PUT /incidents/settings).

12

Implementar control de acceso por rol en todos los endpoints.

13

Pruebas de integración: flujo completo de transferencia, conteo, pronunciamiento y resolución.

14

Pruebas de integración: jobs de escalado y recordatorio.

Artefactos 

Valide e implemente los siguientes artefactos, estos le permitiran construir la tablas y setear datos para trabajar con las mismas, tenga en cuenta que para este proceso ya existen varias tablas en el sistema la cuales deben relacionarse por lo que usted debera separar y ajustar migraciones así como los sedders.

Aquí lo más importante a tener en cuenta antes de ejecutar:

Orden de ejecución obligatorio. Las migraciones deben correr en este orden exacto porque cada tabla depende de la anterior: incident_settings → incidents → incident_evidences → incident_audit_logs → cash_closing_incidents. Los prefijos de fecha en los nombres de archivo garantizan ese orden automáticamente con php artisan migrate.

El trigger de solo inserción en incident_audit_logs tiene implementación MySQL. El driver se detecta en runtime con DB::getDriverName().

El IncidentSeeder cubre los 6 escenarios del mockup: pronunciamiento urgente, en investigación, cerrada por la organización, pendiente, cerrada vinculada a cierre de caja, y anulada. Cada uno genera evidencias y log de auditoría coherentes con su estado. El seeder es idempotente en la configuración y tiene guards que lo bloquean en producción.

El linkToCashClosing en el seeder busca un cierre de caja existente en lugar de crear uno, respetando que cash_closings ya pertenece al CRM. Si no encuentra ninguno, lanza un warning y continúa sin romper la ejecución.

Dos puntos que debe ajustar antes de ejecutar:

El nombre del campo role en la tabla users (el seeder asume role = 'administrator'). Ajustar al nombre real que usa el CRM.

El nombre del campo name en la tabla products. Ajustar según la estructura real del CRM.

 Características modernas de los artefactos. Las principales actualizaciones que aplican a estas migraciones y seeders son: tipado explícito con readonly classes, enums nativos de PHP en lugar de arrays de strings, y la sintaxis de json_encode puede reemplazarse por json helpers de Laravel. 