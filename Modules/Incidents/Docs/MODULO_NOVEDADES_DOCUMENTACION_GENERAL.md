📌 Novedades

Productos  extraviados o faltantes en los procesos de transferencias o conteos. Este debe centralizar el listado de novedades provenientes tanto de transferencias de inventario como de diferencias en conteos aceptados, con posibilidad de escalarse a cierres de caja.

🎯 Contexto

El módulo busca resolver un problema de trazabilidad y accountability de inventario. En términos simples: cuando un producto desaparece del sistema (por transferencia o conteo), alguien tiene que responder por él.

Fuente A — Transferencias de inventario: La sede origen envía X unidades, la sede destino reporta haber recibido menos. Esa diferencia queda "en el aire" y no tiene un responsable claro en el sistema.

Fuente B — Conteos de inventario: El sistema dice que la sede debería tener X unidades de un producto, pero el asesor al hacer el conteo reporta tener menos. Esa diferencia tampoco tiene resolución formal.

Actores y roles identificados

Actor

Rol en el módulo

Asesor / Responsable de sede

Quien generó la operación (transferencia o conteo). Es el primer imputado de la novedad.

Administrador del sistema

Quien investiga, valida y toma la decisión final: si el asesor paga o lo asume la organización.

La organización

Puede asumir la pérdida si el administrador así lo determina.

Lo que el sistema hace automáticamente: genera la novedad sin intervención humana en dos momentos precisos — cuando se acepta una transferencia con diferencia (lado destino) y cuando un administrador aprueba un conteo con faltante. Esto es clave porque elimina el riesgo de que las novedades "se pierdan" por omisión manual.

La cadena de responsabilidad es lineal y clara: siempre cae sobre el asesor de la sede destino o del conteo, sin ambigüedad. La única excepción es cuando ese asesor ya no está vinculado, momento en que el administrador puede reasignar la carga a la organización. Esto hay que manejar con cuidado en UI para que no se haga por error.

El cierre tiene dos caminos distintos con consecuencias diferentes en el sistema:

El asesor subsana comprando el producto al precio público — esto genera una transacción real en el CRM y cruza contra la novedad.

La organización asume — esto genera una salida tipo cortesía, con su factura y sus cruces financieros. Esto implica que el módulo debe poder disparar ese proceso de salida de inventario hacia el sistema existente.

La configuración de plazos vive dentro del módulo en una sección propia (Configuraciones), lo que ya define la estructura del menú.



📋 Definiciones cerradas del módulo Novedades

Valor económico de la novedad: se toma el precio público del producto en el momento en que se genera la novedad. Este valor no cambia aunque el precio del producto cambie después. Esto implica que el módulo debe capturar y guardar el precio público vigente al momento del evento, no consultarlo dinámicamente.

Visibilidad por rol:

Rol

Qué ve

Asesor

Todas las novedades de su sede (no solo las suyas)

Administrador

Todas las novedades de todas las sedes

Notificaciones: doble canal — correo electrónico y notificación interna en el sistema. Los eventos que disparan notificación son: creación de novedad (al asesor responsable y al administrador), cambio de estado (a los involucrados), y resolución final (al asesor y al administrador).

🔑 Atributos mínimos de una novedad

Cada registro de novedad debe guardar al menos:

Identificación: ID único, fecha y hora de creación, origen (transferencia o conteo) con referencia al documento fuente.

Producto: referencia al producto, unidades faltantes, precio público capturado al momento de la novedad, valor total de la novedad (unidades × precio público).

Responsabilidad: sede donde ocurrió el evento, asesor responsable asignado.

Resolución: estado actual, tipo de resolución (asesor / organización / anulada), justificación del administrador, fecha de cierre.

Auditoría: log de todos los cambios de estado con usuario, fecha y acción.

Reglas de negocio consolidadas

Sobre el plazo del origen: el sistema tendrá un parámetro configurable (por ejemplo, 48 horas por defecto) dentro del cual la sede origen puede pronunciarse. Si el plazo vence sin respuesta, la novedad escala automáticamente al administrador. Esto implica que el módulo necesita un job o tarea programada que evalúe novedades vencidas y cambie su estado.

Sobre el reconocimiento voluntario del origen: si el origen asume el error, la novedad se cierra directamente sin pasar por el administrador, siempre que queden registradas las notas y evidencias. El administrador no interviene, pero sí puede ver el registro cerrado en modo auditoría.

Sobre las evidencias del destino: el asesor destino debe poder subir evidencias (fotos, observaciones escritas) en el momento de reportar la diferencia, o dentro de un período posterior. Estas evidencias quedan visibles para el origen y para el administrador.

Regla de negocio cerrada

Cuando el origen reconoce el error:

El sistema cierra la novedad (con notas y evidencias obligatorias).

El sistema ejecuta automáticamente la reversión de inventario: las unidades de diferencia regresan al stock de la sede origen.

El módulo de Novedades no hace seguimiento de la transferencia correcta posterior. Esa es responsabilidad operativa del asesor de origen.



Flujo del inventario en este escenario

Estado inicial (antes de la transferencia):
  Origen:  50 unidades
  Destino:  0 unidades

Transferencia registrada (origen declara envío):
  Origen:  40 unidades  (−10 en tránsito)
  Destino:  8 unidades  (recibe 8, reporta diferencia de 2)
  En novedad: 2 unidades sin ubicación

Origen reconoce error → reversión automática:
  Origen:  42 unidades  (+2 regresan al stock)
  Destino:  8 unidades  (sin cambio)
  Novedad: cerrada

Origen hace transferencia limpia (acción separada):
  Origen:  40 unidades
  Destino: 10 unidades

✅ Funcionalidades requeridas

🎨 Frontend

Adicione un nuevo módulo Novedades, este submodulo debera contener los items [Lista, Configuración], el item lista permitira ver el listados de novendades en una grilla de datos con acciones, el item configuración presentara una vista de configuraciones propias de las novendades.

Construya las vistas con base en el siguiente mockup aplicando la línea grafica del proyecto. 

https://claude.ai/public/artifacts/b883b571-4730-4da7-bd85-e4386051b339

 Aplique validación de campos en los formularios dispuestos para que los inputs solo reciban el dato que piden, asegurese de que las vistas sean responsivas.

Adiciones este módulo a la sección de permisos granulares para gestionar solo por asignación

Implemente las traducciones necesarias según el mockup

Subsanación de novendades https://project-development-team.atlassian.net/browse/LIF-130 

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

Acceda a resto de la tarea aquí 

https://project-development-team.atlassian.net/browse/LIF-129

 Esta actividad explica toda la tarea de back end esperada.

