📌 Gestión de sanciones a empresarios

El sistema permite al administrador aplicar sanciones a los empresarios conforme a las Políticas Lifehuni, registrando de manera obligatoria el motivo, la fecha, el usuario que la aplicó y las políticas incumplidas. Las sanciones generan efectos inmediatos sobre el acceso o las operaciones del empresario, según el alcance definido (total, parcial o llamado de atención), y se mantienen registradas en un historial asociado al empresario.

🎯 Contexto

El sistema busca automatizar y normalizar la aplicación de sanciones y reglamentos, transformando un manual de convivencia en una estructura de datos lógica. Permite que Lifehuni pase de una gestión manual de quejas a un flujo de trabajo auditable que puede restringir automáticamente las capacidades operativas de un usuario en la plataforma (como hacer pedidos o cobrar bonos) según su comportamiento.

Pilares del Modelo

A. Gestión del "Debido Proceso" (Trazabilidad)

El modelo registra la sanción y todo el camino legal para llegar a ella:

Origen: Identifica cómo se detectó la falta (cat_complaint_source).

Evidencia y Defensa: Tablas dedicadas a pruebas (investigation_evidence) y al derecho de réplica del empresario (eui_defense).

Notificaciones: Un registro estricto de comunicaciones (process_notification) para evitar nulidades por falta de comunicación.

B. Parametrización y Reincidencia

El modelo es altamente flexible. Mediante las tablas de "Zona 2" (catálogos), el administrador podra cambiar qué se considera una falta o qué castigo merece sin tocar el código.

Lógica de Reincidencia: La tabla investigation incluye un offense_count, lo que permite al sistema escalar automáticamente la severidad de la sanción (de un llamado de atención a la terminación del contrato) según el historial del usuario.

C. Ejecución Operativa (Enforcement)

A diferencia de un simple registro de texto, la tabla sanction_enforcement tiene un impacto técnico real. Define "flags" o banderas booleanas que el resto del ecosistema de software de Lifehuni debe leer para:

Bloquear compras (block_orders).

Congelar pagos de comisiones (freeze_earnings).

Suspender el crecimiento en el plan de carrera (block_qualification).

D. Instancia de Apelación

Incluye una capa de gobierno corporativo mediante committee_review, permitiendo que las decisiones sean revisadas por un comité superior, manteniendo el estado de suspensión mientras se decide.

Resumen de Flujo de Datos

Apertura: Se crea una investigation vinculada a un users.

Instrucción: Se recopilan evidence, se envían notification y se reciben eui_defense.

Resolución: Se emite una sanction_resolution que puede incluir applied_mitigating (atenuantes).

Ejecución: Se activan los efectos en sanction_enforcement y se audita el cambio en eui_status_log.

Cierre/Apelación: El caso se cierra o pasa a committee_review.

Este modelo separa los datos maestros (usuarios/planes) de la lógica de negocio configurable (catálogos) y la actividad transaccional. Es un diseño preparado para auditorías legales y para una integración profunda con los módulos de e-commerce y pagos de la empresa.

  HU

https://project-development-team.atlassian.net/wiki/x/jYC



Reglas.

https://drive.google.com/file/d/13m0g1NInpRl7ux2llcKuQJcFpOGSVX4L/view?usp=sharing 

✅ Funcionalidades requeridas

El desarrollo se encapsulará en Modules/Sanctions

🎨 Frontend

El enfoque debe ser la usabilidad ergonómica, considerando que el diseño es prioritario para móviles y requiere claridad en procesos legales.

🛠️ Menú y Navegación

Se integrará una nueva sección en el menú lateral izquierdo, respetando la jerarquía visual del portal:

Sanciones (Menú Padre): Ubicado debajo de "Gestión de Usuarios".

🔍 Casos Activos: Enlace directo a la grilla de investigaciones en curso.

📜 Historial de Fallos: Consulta histórica de casos finalizados o archivados.

⚙️ Configuración: Acceso exclusivo para administradores para gestionar catálogos (cat_*) y tipos de faltas.

Vistas

Casos Activos

1. Vista de "Casos Activos" (Grilla de Datos)

Esta vista utilizará el estándar de tablas del proyecto, con capacidades de exportación y filtrado avanzado.

Filtros de Búsqueda: Input de búsqueda rápida por Código de EUI (identificador único del empresario).

Columnas de la Grilla:

ID de Caso: Referencia única de la investigación.

Empresario (EUI): Nombre completo del empresario vinculado.

Tipo de Falta: Basado en la gravedad (Leve, Moderada, Grave).

Estado (Badge Semántico): * 🔴 Rojo: Severo (Sanción crítica aplicada).

🟠 Naranja: En proceso (Investigación abierta).

⚪ Gris: Cerrado (Caso finalizado).

Acciones: Botón "Seleccionar" (verde con dropdown) para ver detalles o gestionar el caso.

2. Vista de Detalle del Caso (Timeline Modal)

Para garantizar la trazabilidad del "Debido Proceso", se implementará un componente modal con una línea de tiempo vertical.

Puntos de Trazabilidad:

Apertura: Fecha y usuario que inició el caso.

Notificación: Registro de comunicaciones enviadas al EUI.

Descargos: Visualización de la defensa presentada por el empresario.

Resolución: Documento final con los efectos aplicados.

3. Formulario de Aplicación de Sanción

Interfaz obligatoria para registrar una acción disciplinaria con impacto técnico.

Campos Requeridos:

Motivo detallado de la sanción.

Políticas incumplidas (Multiselect basado en el manual).

Alcance de la sanción (Total, Parcial o Llamado de atención).

Acciones de Ejecución (Enforcement): Checkboxes para activar bloqueos de compras, congelar comisiones o suspender avance de rango.

Todos los documentos cargados deben ir a la carpeta digital del empresario y ser visibles por este.

Historial de fallos (Resoluciones)

El objetivo desde una perspectiva de UX es proporcionar una herramienta de auditoría rápida y legalmente sólida. A diferencia de "Casos Activos", esta vista es de consulta para entender el precedente disciplinario de un empresario, esta sección centraliza todos los procesos que han llegado a una resolución en firme, ya sea con sanción aplicada, exoneración o cierre por apelación.

1. Filtros de Auditoría (UX)

Ubicados en la parte superior para segmentar años de datos:

Rango de Fechas: Filtro por closed_at para reportes mensuales o anuales.

Tipo de Resolución: Dropdown para filtrar por "Sancionado", "Exonerado" o "Contrato Terminado".

Búsqueda por EUI: Input persistente para buscar el historial específico de un empresario mediante su código único.

2. Grilla de Datos (Estructura)

Utilizar la tabla estándar del ERP, pero con columnas enfocadas en el desenlace del proceso.

Columna

Componente UI

Propósito Legal/Técnico

ID Caso

Texto Monospace

Referencia para expedientes físicos.



Empresario

Avatar + Nombre

Identificación visual rápida del EUI.



Falta Cometida

Etiqueta de texto

Indica la infracción del manual (ej. Competencia Desleal).



Sanción Final

Badge de color

Refleja el sanction_code (ej. Suspensión 15 días).



Fecha Cierre

Fecha corta

Cuándo se emitió la resolución final.



Estado

Badge Gris

Siempre marcará "Cerrado" o "Archivado" en esta vista.



Acción

Botón Icono (Ojo) “Detalle“

Abre modal con la Resolución Motivada, “El detalle de a que se llego“



📄 Detalle de Registro (Expediente Digital)

Al seleccionar un registro del historial, se abre una vista de lectura (Read-only) con la siguiente estructura ergonómica:

A. Resumen Ejecutivo (Top)

Contador de Reincidencia: Indica si esta fue la 1ª, 2ª o 3ª falta del EUI al momento del cierre.

Instructor Responsable: Nombre del usuario administrativo que firmó la resolución.

B. Cuerpo del Fallo

Hechos Probados: Bloque de texto con la descripción de los hallazgos.

Atenuantes Aplicados: Lista de factores que redujeron la severidad (si aplicaron).

Efectos Operativos: Lista de lo que se ejecutó (ej. "Se bloquearon compras del 01/03 al 15/03").

C. Repositorio de Evidencia

Galería de miniaturas de las pruebas que sustentaron el fallo (fotos, capturas de pantalla, documentos firmados), esto obtenido de la carpeta digital de el empresario en especifico.

Configuración

Siguiendo la arquitectura de Laravel Modular y el diseño de Lifehuni, esta vista gestionará las tablas paramétricas (cat_*) de la "Zona 2" del modelo de datos.

⚙️ Vista: Configuración del Módulo

Esta interfaz se divide en pestañas (Tabs) para organizar los diferentes catálogos de manera limpia y evitar el scroll infinito en dispositivos móviles y resoluciones de portatil.

1. Pestaña: Tipos de Faltas (cat_offense_type)

Permite definir qué conductas violan el manual de convivencia.

Grilla de Gestión: Lista el nombre de la falta, el nivel de severidad (Leve, Moderada, Grave) y su estado (Activo/Inactivo).

Formulario de Edición:

Nivel Numérico: Asignación de peso (1, 2 o 3) para cálculos de reincidencia.

Descripción Legal: Campo de texto enriquecido para copiar el artículo exacto del manual de políticas.

2. Pestaña: Matriz de Sanciones (cat_sanction_type)

Aquí se configura la lógica de reincidencia que usará el SanctionService.

Configuración de Reincidencia: Campos para definir el texto y la consecuencia según el número de veces que se cometa la falta (first_offense_text, second_offense_text, etc.).

Vinculación de Códigos: Selector para asociar la falta con un código de sanción (ej. SUSPENSION_1_5_DAYS).

3. Pestaña: Acciones Operativas (cat_action_type)

Define qué "llaves" se cierran en el ERP cuando un EUI es sancionado.

Banderas de Control (Flags): Interruptores UI para activar/desactivar:

🚫 Bloqueo de Pedidos: Impide que el EUI genere nuevas compras.

💰 Congelar Billetera: Retiene utilidades y bonos.

💎 Suspensión de Rango: Detiene el avance en el plan de carrera.

4. Pestaña: Atenuantes (cat_mitigating_factor)

Lista de factores que el instructor puede seleccionar para reducir una pena.

Ejemplos: "Aceptación de cargos", "Cooperación activa" o "Evitó daños mayores".

Seguridad: Al ser datos sensibles que afectan la operación, cada cambio en esta vista debe generar un registro de auditoría.

Colores Semánticos: Uso de etiquetas de color para los niveles de severidad (Verde para Leve, Amarillo para Moderado, Rojo para Grave) para facilitar el escaneo visual.

🧱 Backend

Migraciones y Modelos:

Crear las migraciones para las tablas paramétricas (cat_*) y transaccionales en el nuevo módulo.

Definir los modelos con sus respectivos Enums de PHP (aprovechando los Enums nativos de Laravel para coincidir con los de la BD).

Establecer las relaciones Eloquent (ej. Investigation -> hasMany -> InvestigationEvidence).

Capa de Servicios (Business Logic):

Crear un SanctionService para procesar la lógica de reincidencia (offense_count).

Implementar la lógica de "Enforcement": métodos que se comuniquen con otros módulos para bloquear pedidos o congelar ganancias si existe una sanción activa.

API & Controladores:

Desarrollar los Endpoints para el flujo del proceso (Apertura, carga de pruebas, registro de descargos).

Implementar validaciones robustas mediante FormRequests.

Controllers: * InvestigationController: CRUD de casos y cambio de estados.

EvidenceController: Gestión de carga de archivos (S3 o Local) con validación de MIME-types.

ResolutionController: Generación de PDF (usando dompdf o snappy) para la resolución formal.

Middlewares de Bloqueo: * Crear CheckSanctionStatus: Este middleware se aplicará a los módulos de Checkout y Wallet. Si el EUI tiene un enforcement_type activo, retornará un 403 Forbidden con el motivo de la sanción.

Sistema de Notificaciones y Eventos:

Configurar Listeners para que, al operar las sanciones, se dispare automáticamente un correo al EUI, esto usando la funcionalidad de notificaciones existente.

Programar un Command de Artisan que verifique diariamente la expiración de sanciones (effect_end_date).



Configuración

Cache Management: Dado que estos datos cambian poco pero se consultan en cada pedido (vía Middleware), se debe implementar Cache::remember en el SanctionService para no saturar la base de datos.

Validación de Integridad: El backend debe impedir la eliminación de un tipo de falta si existen investigaciones activas vinculadas a ella, sugiriendo en su lugar la "Desactivación" (Soft Delete/Status toggle).

🏗️ Infraestructura

Para este proceso ya se ha establecido la logica de bd según la necesidad y alcance del manual lifehuni de reglas, accesa a la carpeta que se muestra la imagen, es importante que la logica que se dejo allí de implemente en el nuevo módulo, para visualizar correctamente el archivo sanctions.dbml use la extensión de VS *dbdiagram*

Extensión

Infraestructura

1. Integridad de los Datos (Migraciones)

Zona Paramétrica: Las tablas cat_offense_type, cat_sanction_type, cat_action_type, entre otras, permiten que el sistema sea dinámico.

Trazabilidad Transaccional: La tabla investigations centraliza el proceso, conectando al empresario (eui_id) con el instructor y el estado actual.

Evidencia y Defensa: Las tablas investigation_evidence y eui_defense aseguran el cumplimiento del "Debido Proceso" al permitir adjuntar archivos y registrar descargos.

Impacto Real: La tabla sanction_enforcement es la que ejecutará los bloqueos de compras (block_orders) y comisiones (freeze_earnings)

2. Carga Inicial (Seeders)

Cargas preconfigurado los niveles de faltas (Leve, Moderada, Grave) en CatOffenseTypeSeeder.

En CatSanctionTypeSeeder, ya están definidas las escalas de sanciones, desde amonestaciones escritas hasta la terminación de contrato, lo cual es vital para la lógica de reincidencia (offense_count).

🛠️ Tareas Técnicas Específicas (Post-Migración)

Backend (Laravel Modular)

Modelos con Enums: Crear los modelos en Modules/Sanctions/Entities y usar los Enums de PHP para mapear columnas como offense_level y sanction_code, asegurando que la lógica del código coincida con los tipos de la BD.

SanctionService: Este servicio debe consumir los datos de cat_sanction_type para determinar automáticamente qué sanción aplicar cuando el offense_count del empresario aumente.

API Endpoints: * POST /api/sanctions/investigations: Para abrir un nuevo caso.

POST /api/sanctions/investigations/{id}/evidence: Para que el instructor suba pruebas.

GET /api/sanctions/investigations/history: Para alimentar la vista de Historial de Fallos.

🧪 Criterios de aceptación

Bloqueo Efectivo: Un empresario con block_orders = true en sanction_enforcement no debe poder completar el checkout.

Notificación Automática: Al crear un registro en process_notifications, se debe disparar un evento de Laravel que envíe el correo electrónico al EUI.

Auditoría: Cada cambio de estado del empresario debe quedar registrado en eui_status_log para fines legales.

