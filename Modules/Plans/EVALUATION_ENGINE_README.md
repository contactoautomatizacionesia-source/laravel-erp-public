# Módulo de Planes (Motor de Calificación y Rangos)

Este documento detalla la arquitectura, el flujo de ejecución, las validaciones y el funcionamiento interno del **Motor de Evaluación de Planes** para los empresarios dentro del sistema ERP.

## Índice
1. [Arquitectura General](#arquitectura-general)
2. [Componentes Principales](#componentes-principales)
3. [Flujo de Ejecución (Paso a Paso)](#flujo-de-ejecución-paso-a-paso)
4. [Abstracción y Patrón Strategy (Checkers)](#abstracción-y-patrón-strategy-checkers)
5. [Catálogo de Reglas Actuales](#catálogo-de-reglas-actuales)
6. [Cómo Añadir Nuevas Reglas](#cómo-añadir-nuevas-reglas)
7. [Consideraciones de Ordenamiento y Optimización](#consideraciones-de-ordenamiento-y-optimización)

---

## Arquitectura General

El motor de planes está construido con base en métricas sólidas de mantenibilidad y escalabilidad. Utiliza el **Patrón Strategy** para las reglas individuales de evaluación y adopta un modelo de extracción de datos centralizada (Snapshot) para evitar el temido problema de N+1 consultas a la base de datos durante evaluaciones masivas o complejas.

La asignación de rangos no ocurre evaluando largos y enredados bloques de condicionales `if/else`. En su lugar, existe un motor de reglas "ciego" e independiente que simplemente recorre los planes y delega la validación de cada requisito individual a pequeñas clases especializadas llamadas **Checkers**.

---

## Componentes Principales

- **`EntrepreneurPlanService`** (Capa de Aplicación): Fachada principal y gestor transaccional encargado de invocar upgrades/asignaciones, registrar el historial en `entrepreneur_plan_history` y limpiar las cachés del árbol.
- **`PlanEvaluationService`** (Orquestador): Servicio que coordina el orden de evaluación descendente, invoca el builder de contexto y procesa si el usuario cumple con la totalidad de exigencias (`passed == true`) requeridas por cada Plan.
- **`UserSnapshotBuilder`** (Data Builder): Centralizador de consultas SQL. Previene que cada iteración de regla individual sature la Base de Datos con cientos de consultas repetitivas. Pre-empaca el estado completo del usuario.
- **`RuleCheckerRegistry`** (Registro IoC): Contenedor vital que correlaciona el Identificador o Llave de una regla configurada en BD (ej. `POINTS_THRESHOLD`) con su clase especializada procesadora en PHP (`PointsThresholdChecker`).
- **`AbstractRuleChecker`** (Contrato Abstracto): La clase madre que define la firma del método `check()` y posee helpers (métodos utilitarios) para leer respuestas de formularios dinámicos JSON (`formAnswers`).
- **`RuleResult`**: Objeto inmutable (Value Object) que acarrea de forma estructurada la respuesta de cada evaluación (`passed`, llaves intervinientes, descripción detallada del por qué falló o pasó).

---

## Flujo de Ejecución (Paso a Paso)

Cuando se evalúa si un empresario califica para un nuevo rango general, el flujo es el siguiente (iniciado comúnmente desde `runUpgrade($userId)`):

1. **Captura del Contexto (User Snapshot)**:
   El `PlanEvaluationService` le solicita a `UserSnapshotBuilder` construir el "Snapshot" del usuario (`$userId`). La base de datos se consulta **una sola vez** para recopilar:
   - Puntos personales totales en el ciclo abierto.
   - Puntos generados por la red descendente.
   - Estado de formalización (bandera de contratos firmados conectada a API).
   - Estructura plana de la línea descendente (downline).
   - Historial de ciclos anteriores cerrados.
   Todo esto se empaqueta y retorna en un arreglo asociativo en memoria (`$context`).

2. **Carga y Ordenamiento de Planes Activos**:
   Se consultan los planes disponibles (`PlanChild` y parent `Plan`). Se ordenan de **Mayor a Menor** nivel de dificultad (`order DESC`, `level_order DESC`). Esto garantiza que el sistema siempre intente otorgar el rango más alto posible primero, disminuyendo drásticamente el esfuerzo computacional al encontrar la primera coincidencia válida.

3. **Inyección de Puntos Efectivos (Pre-pass)**:
   Para cada plan recorrido, se separan sus reglas hijas vinculadas. Primero siempre se ejecuta la regla condicional `POINTS_SOURCE` si existe. Esta regla NO aprueba ni rechaza el plan de por sí; solo lee la configuración particular del rango (si debe incluir puntos de red o solo personales, y a cuántos niveles de profundidad) y genera la bolsa de puntos "Efectivos", sobrescribiendo e inyectando este valor en la variable virtual `total_points` del snapshot local.

4. **Ejecución de "Checkers" Puros (Main-pass)**:
   Se itera a través de todas las demás reglas obligatorias del plan de forma secuencial. A cada regla se le inyecta el `$context` preparado. Cada regla retorna un objeto `RuleResult`. 
   Si **todas** las reglas requeridas del plan retornan `passed == true`, el bucle se detiene inmediatamente. Se asume que el usuario califica para ese PlanChild.

5. **Aplicación y Persistencia (Post-evaluación)**:
   El `EntrepreneurPlanService` compara el ID del plan calificado devuelto con el que actual del usuario (`$profile->plan_child_id`). Si difieren, se registra en base de datos como Upgrade o Downgrade invocando `assignPlan()`. Este método opera transaccionalmente: cierra el historial anterior (`ended_at = now()`), crea el nuevo paso histórico y finalmente **invalida todas las cachés** ligadas al árbol de la red para que la línea ascendente del individuo y los reportes, reflejen instantáneamente el nuevo rango adquirido.

---

## Abstracción y Patrón Strategy (Checkers)

Diseñar bajo el patrón Strategy significa que el servicio matriz `PlanEvaluationService` jamás hace la matemática. Él simplemente le dice al Registro global de la aplicación: *"Tengo esta regla que acaba de venir de la BD con la llave X, devuélveme al trabajador especializado encargado de esta llave"*:

```php
$checker = $this->registry->for($rule->category->key);
$result  = $checker->check($rule, $userId, $localContext, $isRequired);
```

Si el día de mañana el negocio requiere incorporar una regla exótica sin precedentes como *"Haber reclutado a 3 personas un domingo"*, **ni los servicios centrales ni el orquestador deberán alterarse una sola línea**. Simplemente se creará el nuevo `SundayRecruitChecker` extendiendo de `AbstractRuleChecker` y el motor lo procesará naturalmente.

---

## Catálogo de Reglas Actuales

El sistema cuenta en este momento con los siguientes evaluadores especializados altamente testeables y aislados:

2. **`PointsThresholdChecker`** (`POINTS_THRESHOLD`): Frontera estándar. Evalúa sencillamente que el total de puntos rebase el mínimo esperado configurado en las respuestas del formulario de la regla (`MIN_POINTS`).
3. **`PointsRangeChecker`** (`POINTS_RANGE`): Validante estricto que exige que el volumen final encaje herméticamente entre un `MIN_POINTS` y un `MAX_POINTS`.
4. **`CycleCompletionChecker`** (`CYCLE_COMPLETION`): Evaluador histórico enfocado a la solidez. Asegura que hubieron al menos N ciclos cerrados de forma consecutiva (o no) ostentando un Plan pre-requerido específico.
5. **`PersonalSalesChecker`** (`PERSONAL_SALES`): Asegura un volumen comercial propio y purista (exento de apalancamiento de red), además de soportar matrices complejas de puntos intercalados repetitivos entre el Ciclo 1, Ciclo 2, etc.
6. **`DownlineTitleCountChecker`** (`DOWNLINE_TITLE_COUNT`): Métrica fundamental de liderazgo. Cuantifica un número explícito de empresarios descendientes exactos en cierta Generación (`GENERATION`) con un rango mínimo (`REQUIRED_PLAN`) y verificando un volumen calificado.
8. **`LifeTitleCountChecker`** (`LIFE_TITLE_COUNT`): Verifica a nivel descendencia la creación de líderes vitalicios bajo un umbral de puntaje.
9. **`MaintenanceChecker`** (`MAINTENANCE`): Regla Composite/Anidada ("Meta Regla"). Combina dependientes y reglas hijas con lógicas AND/OR puras de manera recursiva/jerárquica para determinar mantenciones bi-mensuales o complejas.
10. **`DocumentationFormalizationChecker`** (`DOCUMENTATION_FORMALIZATION`): Bandera lógica estricta acoplada a la API de contratos. Comprueba pasivamente en el Snapshot si el individuo cuenta con toda la documentación real material o digital firmada, la cual es legalmente indispensable para reclamar o exhibir públicamente el título.

---

## Cómo Añadir Nuevas Reglas

Agregar requerimientos comerciales es un proceso quirúrgico y seguro con **4 simples pasos** de desacoplamiento:

1. **Construir y Migrar la Regla (Base de Datos)**:
   Añadiendo un nuevo registro en la tabla `rule_category` con una llave identificadora única (ej. `NUEVA_REGLA_KEY`) y sus traducciones correspondientes de nombre y descripción, además de un `rule_category_type_id`.

2. **Capturar Data Requerida (`UserSnapshotBuilder.php`)**:
   De ser necesario recolectar datos inéditos (que actualmente el ecosistema del motor ignora en memoria), se debe agregar esa recolección a uno de los arreglos asociativos retornados por el método `build()` del Snapshot. **Siempre se debe agotar todo recurso para evitar agresivamente que un *Checker* instancie modelos o ejecute consultas repetitivas a la base de datos de manera independiente**.

3. **Crear el Checker Específico (`NuevareglaChecker.php`)**:
   En el directorio `Modules\Plans\Pipeline\Checkers`, extender de `AbstractRuleChecker`. Definir el alias de cadena exacto que devuelve el método `categoryKey()`. Escribir la evaluación lógica matemática/comparativa dentro del método `check()` retornando sin excepción un envoltorio `RuleResult::pass` o `RuleResult::fail`. 

4. **Registrarlo en el Container (`PlansServiceProvider.php`)**:
   Inyectar globalmente la nueva clase creada suministrándola al constructor del singleton del `RuleCheckerRegistry` dentro del método `register()` del Service Provider. ¡Y nada más! El orquestador la compilará por sí solo en la próxima validación de planes.

---

## Consideraciones de Ordenamiento y Optimización

- **Rendimiento Cacheado**: El servicio purga las cachés de red topológicas (desde Nivel 2 hasta Nivel 5) ante cualquier actualización estructural para optimizar la carga agnóstica del árbol visual al frontend o a procesadores futuros de comisiones monetarias.
- **Fail Early (Abandono Temprano)**: Como la estructura ahora evalúa desde el límite máximo hacia el límite base (`order DESC`), frena abruptamente al hallar la primera gran coincidencia. Eso significa que si hay 10 rangos disponibles, y un _Diamante_ asombroso entra a evaluación para actualización, recorrerá el test general nivel Diamante, validará las N reglas pre-obtenidas en milisegundos y detendrá ahí el proceso global. Jamás malgastará memoria iterando y evaluando requisitos para rangos _Esmeralda_, _Rubí_ o subordinados.
- **FormAnswers Dinámicos Agnosos**: Múltiples *Checkers* acceden transparentemente a parámetros JSON generados desde la interfaz web frontal (vía el recolector `$this->answers($rule)`). Esto evita tener que adherir columnas duras ("hard codings") en SQL. Otorga al corporativo la capacidad de cambiar metas numéricas paramétricas "al vuelo" desde el Panel Administrativo sin requerir ventanas de despliegues (Deploys) de código de programación.
