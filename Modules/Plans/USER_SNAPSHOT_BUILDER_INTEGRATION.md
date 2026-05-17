# UserSnapshotBuilder: Estado de Integración

Este documento deja explícito qué partes de `UserSnapshotBuilder` ya quedaron integradas, cuáles están funcionando bajo una regla provisional y cuáles siguen pendientes por depender de otros módulos o APIs.

Archivo principal:
- `Modules/Plans/Services/UserSnapshotBuilder.php`

## Objetivo del Snapshot

El snapshot es la fuente única de datos que consumen los checkers del motor de planes. Su responsabilidad es entregar un contexto listo para evaluar reglas, evitando que cada checker haga consultas directas a base de datos.

Contexto esperado:
- `current_plan_child_id`
- `personal_points`
- `children_points`
- `total_points`
- `closed_cycles`
- `downline`
- `has_formalized_documentation`

## Integración realizada

### 1. `resolveCurrentPlan()`

Estado:
- Integrado

Fuente:
- `customer_profiles.plan_child_id`

Uso:
- Determinar el nivel actual del empresario.

### 2. `resolvePersonalPoints()`

Estado:
- Integrado de forma provisional

Regla provisional implementada:
- Se toma la fecha de fin del último ciclo cerrado.
- La ventana actual empieza al día siguiente de ese cierre.
- Si no existe ningún ciclo cerrado, la ventana actual empieza en el primer día del mes actual.
- Se suman los `total_points` de órdenes calificadas del usuario dentro de esa ventana.

Órdenes calificadas:
- `is_cancelled = 0`
- y además:
  - `is_completed = 1`
  - o `order_status in ('delivered', 'invoiced', 'completed')`

Motivo de provisionalidad:
- Todavía no existe una fuente explícita de “puntos del ciclo abierto” o un ledger dedicado de puntos por ciclo en tiempo real.

Checkers impactados:
- `POINTS_THRESHOLD`
- `POINTS_RANGE`
- cualquier helper que use `total_points`

### 3. `resolveChildrenPoints()`

Estado:
- Integrado de forma provisional

Fuente:
- `network_paths`
- `orders`

Regla provisional implementada:
- Se suman los `total_points` de todas las órdenes calificadas de la red descendente del usuario en la misma ventana actual inferida para `personal_points`.

Motivo de provisionalidad:
- La red usada es la red actual en `network_paths`; no existe todavía una reconstrucción histórica de red por ciclo.

Checkers impactados:
- `POINTS_THRESHOLD` cuando incluye hijos
- `POINTS_RANGE` cuando incluye hijos

### 4. `resolveClosedCycles()`

Estado:
- Integrado de forma mixta: funcional, con parte histórica provisional

Qué hace:
- Toma todos los ciclos cerrados desde `cycles`.
- Determina qué `plan_child_id` tenía el usuario al cierre de cada ciclo usando `entrepreneur_plan_history`.
- Calcula para cada ciclo:
  - `personal_points`
  - `life_points`
  - `no_life_points`

Qué parte quedó sólida:
- existencia y orden de ciclos cerrados
- plan del usuario al cierre de cada ciclo
- puntos personales del usuario dentro del rango del ciclo

Qué parte quedó provisional:
- `life_points` y `no_life_points` se clasifican usando la red actual (`network_paths`) y el plan actual del descendiente (`customer_profiles -> plan_child -> plan.is_life_title`)
- esto permite operar el motor hoy, pero no representa todavía una reconstrucción histórica exacta de cómo estaba la red en cada ciclo pasado

Por qué no quedó como una fotografía exacta:
- `network_paths` describe la red actual, no la red histórica de cada cierre
- el campo `is_life_title` en planes sirve para clasificar el estado actual del plan del descendiente, no necesariamente el título que tenía en el ciclo pasado
- hoy no existe una tabla histórica que diga, para cada ciclo cerrado:
  - quiénes eran descendientes válidos en ese momento
  - qué título exacto tenía cada descendiente en ese momento
  - cómo debía clasificarse cada punto de red en ese cierre

Entonces, en este momento:
- `personal_points` por ciclo sí es una suma histórica bastante confiable dentro del rango de fechas del ciclo
- `life_points` y `no_life_points` son una proyección histórica basada en la red actual y en el título actual del descendiente

Eso significa que `POINTS_PER_CYCLE` ya puede correr, pero con esta precisión:
- es útil para operar y desbloquear la evaluación
- no garantiza exactitud retroactiva absoluta si la red o los títulos cambiaron después del ciclo evaluado

Checkers impactados:
- `CYCLE_COMPLETION`
- `POINTS_PER_CYCLE`

### 5. `resolveDownline()`

Estado:
- Integrado de forma parcial

Qué devuelve ahora:
- `user_id`
- `plan_child_id`
- `generation`
- `personal_points`
- `is_life_title`
- `ancestor_plan_child_ids`
- `benefit_total = 0`

Qué parte quedó sólida:
- la generación viene de `network_paths.depth`
- el plan actual del descendiente viene de `customer_profiles.plan_child_id`
- `is_life_title` viene del plan padre (`plan.is_life_title`)
- `ancestor_plan_child_ids` se arma con la cadena de ancestros dentro del subárbol actual

Qué parte quedó provisional:
- `personal_points` del descendiente usa la misma ventana actual inferida del snapshot
- la cadena de ancestros es la red actual, no una red histórica

Qué parte quedó pendiente:
- `benefit_total`

Motivo:
- `benefit_total` depende de beneficios monetizados/liquidados de red para cada descendiente.
- En este momento ese dato todavía no existe integrado para el caso del beneficio de red tipo B26.
- Por esa razón, temporalmente se devuelve `0.0`.

Checkers impactados:
- `LIFE_TITLE_COUNT`: sí queda bastante destrabado
- `DOWNLINE_TITLE_COUNT`: sigue incompleto mientras `benefit_total` no exista

### 6. `resolveDocumentationStatus()`

Estado:
- Pendiente

Valor actual:
- `true`

Motivo:
- El dato real vendrá de una API de contratos que todavía no está integrada.

Checker impactado:
- `DOCUMENTATION_FORMALIZATION`

## Resumen por checker

## Categorías con conflicto real hoy

Esta es la lista corta de lo que sigue pendiente de verdad, sin ambigüedad.

### `DOWNLINE_TITLE_COUNT`

Estado:
- No operativa

Conflicto real:
- El checker necesita `benefit_total` por descendiente.

Por qué tiene conflicto:
- `benefit_total` todavía no existe integrado en el sistema.
- Ese dato depende de la futura integración del beneficio B26 y de su cálculo real para los empresarios hijos.
- Mientras `benefit_total` siga en `0.0`, la regla fallará para configuraciones reales.

Impacto:
- No debe considerarse lista para producción.

### `POINTS_PER_CYCLE`

Estado:
- Operativa, pero provisional

Conflicto real:
- La separación histórica entre `life_points` y `no_life_points` no es exacta.

Por qué tiene conflicto:
- Hoy se reconstruye usando:
  - la red actual en `network_paths`
  - el título actual del descendiente (`is_life_title`)
- No existe una fotografía histórica congelada por ciclo que diga exactamente cómo estaba la red y cómo debía clasificarse cada punto en ese momento.

Impacto:
- La categoría puede usarse.
- Pero sus puntos de red históricos deben entenderse como una aproximación operativa, no como una reconstrucción retroactiva exacta.

### `DOCUMENTATION_FORMALIZATION`

Estado:
- No operativa

Conflicto real:
- El checker depende de un dato externo que no existe todavía en el sistema.

Por qué tiene conflicto:
- `resolveDocumentationStatus()` sigue devolviendo `true`.
- El estado real de documentación vendrá desde una API de contratos que aún no está integrada.

Impacto:
- No debe considerarse validación real todavía.

### `LIFE_TITLE_COUNT`

Estado:
- Casi operativa, con una reserva semántica

Conflicto real:
- El checker hoy usa `personal_points`, pero la regla de negocio habla de “personales o de su red No Life”.

Por qué tiene conflicto:
- La estructura de `downline` ya existe y `is_life_title` ya puede resolverse.
- Lo pendiente es confirmar si el mínimo por empresario debe evaluarse con:
  - solo `personal_points`
  - o `personal_points + no_life_network_points`
- Si el negocio exige la segunda interpretación, el checker todavía necesita ese ajuste.

Impacto:
- La categoría está mucho más cerca que las otras.
- Pero no debe marcarse como cerrada hasta confirmar esa semántica.

### Quedan razonablemente funcionales con esta integración
- `POINTS_THRESHOLD`
- `POINTS_RANGE`
- `CYCLE_COMPLETION`
- `LIFE_TITLE_COUNT`

### Quedan funcionales, pero con historia reconstruida de forma provisional
- `POINTS_PER_CYCLE`
  - La regla ya puede evaluarse porque `closed_cycles` ahora existe.
  - Lo sólido es:
    - qué ciclos están cerrados
    - cuántos puntos personales tuvo el usuario en cada ciclo
  - Lo provisional es:
    - la separación entre `life_points` y `no_life_points`
  - Esa separación todavía no sale de una fotografía histórica real del cierre, sino de una reconstrucción hecha con:
    - la red actual en `network_paths`
    - y el título actual del descendiente (`is_life_title`)
  - Por eso el checker funciona, pero sus puntos de red históricos todavía deben entenderse como una aproximación operativa, no como una liquidación histórica exacta.

### Quedan pendientes por integración externa
- `DOWNLINE_TITLE_COUNT`
  - falta `benefit_total`
- `DOCUMENTATION_FORMALIZATION`
  - falta API de contratos

## Decisiones provisionales asumidas

1. Los puntos actuales ya no se calculan sobre todo el histórico, sino sobre una ventana actual inferida desde el último ciclo cerrado.
2. La clasificación `Life / No Life` de puntos de red históricos se resuelve con el plan actual del descendiente.
3. La topología usada para `children_points`, `closed_cycles` y `downline` es la red actual de `network_paths`.
4. `benefit_total` se omite por ahora devolviendo `0.0`.
5. `resolveDocumentationStatus()` permanece fijo en `true` hasta que exista la API.

## Qué habría que tener para una fotografía histórica exacta

Para que `closed_cycles` y especialmente `POINTS_PER_CYCLE` fueran históricamente exactos al 100%, haría falta al menos una de estas dos cosas:

1. Un ledger histórico por ciclo ya consolidado
- Una tabla que, al cerrar cada ciclo, guarde explícitamente por usuario:
  - `personal_points`: los puntos personales del usuario
  - `life_points`: los puntos que el usuario obtiene de su red Life
  - `no_life_points`: los puntos que el usuario obtiene de su red No Life
  - y cualquier otra métrica de red necesaria

2. O una fotografía histórica completa de la red y del plan por fecha
- Una forma de reconstruir para un momento exacto:
  - quién pertenecía a la red de quién
  - qué `plan_child` tenía cada usuario
  - si ese plan debía contar como Life o No Life en esa fecha

Mientras eso no exista, el sistema puede reconstruir una versión útil para operar, pero no una imagen histórica exacta del cierre.

## Qué falta para cerrar el builder al 100%

1. Una fuente oficial de puntos del ciclo abierto actual.
2. Una fuente histórica de red por ciclo, si el negocio exige exactitud retroactiva.
3. La integración real de `benefit_total` por descendiente.
4. La integración de contratos para documentación.














Quedó resumido así, sin relleno:

DOWNLINE_TITLE_COUNT

Conflicto: falta benefit_total
Motivo: depende de la integración futura del beneficio B26 para hijos
Estado: no operativa
POINTS_PER_CYCLE

Conflicto: life_points y no_life_points no salen de una foto histórica exacta
Motivo: hoy se reconstruyen con la red actual y el título actual del descendiente
Estado: operativa, pero provisional
DOCUMENTATION_FORMALIZATION

Conflicto: falta el dato real
Motivo: depende de una API de contratos aún no integrada
Estado: no operativa
LIFE_TITLE_COUNT

Conflicto: falta cerrar la semántica del mínimo de puntos
Motivo: hoy usa personal_points, pero la regla de negocio puede exigir personales + red No Life
Estado: casi operativa, con esa reserva
Si quieres, el siguiente paso lo podemos dejar todavía m
