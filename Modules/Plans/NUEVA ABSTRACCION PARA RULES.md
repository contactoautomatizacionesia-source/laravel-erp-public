# Resumen de Tablas — Rules & Benefits

---

## DOMINIO: PLANS (base compartida)

| Tabla | Columnas clave | Propósito |
|-------|---------------|-----------|
| `plans` | `id, code, name, level_order, active` | Catálogo de títulos/niveles del sistema (Life, Platino, Zafiro…). `level_order` define jerarquía. |

---

## DOMINIO: RULES

| Tabla | Columnas clave | Propósito |
|-------|---------------|-----------|
| `rule_category_type` | `id, label, key` | Clasificación de alto nivel de las reglas: Puntos, Cumplimiento, Red. |
| `rule_category` | `id, name, key, description, rule_category_type_id` | Los 9 tipos de regla disponibles (POINTS_RANGE, PERSONAL_SALES, etc.). Define qué formulario se renderiza. |
| `rule` | `id, code, title, description, rule_category_id, times_triggered, is_active` | Cada regla de negocio instanciada. `code` es el identificador de negocio (R1, R17…). |
| `rule_form_sections` | `id, rule_category_id, section_label, section_key, section_order, is_repeatable, is_active` | Secciones del formulario agrupadas por categoría. `is_repeatable` habilita listas dinámicas (ej. combinaciones de ciclos). |
| `rule_form_fields` | `id, rule_form_section_id, field_label, field_key, field_type, is_required, help_text, validation_rules, is_active` | Campos individuales del formulario. `field_type`: number, select, boolean, text. `validation_rules` en JSON. |
| `rule_form_answers` | `id, rule_id, rule_form_field_id, answer, repeat_index` | Respuestas concretas de cada regla. `repeat_index` agrupa filas de secciones repetibles (ej. varias combinaciones de ciclos). |
| `rule_dependencies` | `id, parent_rule_id, child_rule_id, operator, order_index` | Árbol de dependencias para reglas compuestas (MAINTENANCE). Permite AND/OR entre reglas referenciadas. |
| `plan_rules` | `plan_id, rule_id, is_required` | N:M entre planes y reglas. Define qué reglas aplican a qué plan y si son obligatorias. |

---

## DOMINIO: BENEFITS

| Tabla | Columnas clave | Propósito |
|-------|---------------|-----------|
| `benefit_category_type` | `id, label, key` | Clasificación de alto nivel de beneficios: Económico, Descuento, Permiso, Referidos, Premio. |
| `benefit_category` | `id, name, key, description, benefit_category_type_id` | Los 8 tipos de beneficio disponibles (MONETARY_BONUS, DISCOUNT_ON_NEXT_PURCHASE, etc.). |
| `benefit` | `id, title, description, benefit_category_id, times_triggered, is_cumulative, is_active` | Cada beneficio instanciado. `is_cumulative` indica si se acumula con otros beneficios. |
| `benefit_form_sections` | `id, benefit_category_id, section_label, section_key, section_order, is_active` | Secciones del formulario agrupadas por categoría de beneficio. |
| `benefit_form_fields` | `id, benefit_form_section_id, field_label, field_key, field_type, is_required, help_text, validation_rules, is_active` | Campos del formulario. `validation_rules` soporta opciones estáticas o dinámicas vía `METHOD[fn]`. |
| `benefit_form_answers` | `id, benefit_id, benefit_form_field_id, answer` | Respuestas concretas de cada beneficio creado. |

---

## Vista de relaciones

```
plans ─────────────────────────── plan_rules ──── rule
                                                    │
rule_category_type ── rule_category                 │
                           │                        │
                   rule_form_sections        rule_form_answers
                           │                        │
                   rule_form_fields ────────────────┘
                                             rule_dependencies
                                            (self-join en rule)


benefit_category_type ── benefit_category
                               │
                       benefit_form_sections        benefit
                               │                      │
                       benefit_form_fields ── benefit_form_answers
```

---

## Conteo total

| Dominio | # Tablas |
|---------|----------|
| Plans (base) | 1 |
| Rules | 8 |
| Benefits | 6 |
| **Total** | **15** |


# DETALLE DE CADA ABSTRACCIÓN
## Abstracción CRUD para Rules

---

## TABLA: rule_category
`id | name | key | description | rule_form_sections (nullable) | rule_category_type_id`

| id | name | key | description | rule_form_sections | rule_category_type_id |
|----|------|-----|-------------|--------------------|-----------------------|
| 1 | "Umbral de Puntos" | `POINTS_THRESHOLD` | "Valida si los puntos acumulados superan un mínimo para estar activo" | 1 | 1 |
| 2 | "Rango de Puntos" | `POINTS_RANGE` | "Valida si los puntos acumulados están dentro de un rango para pertenecer a un nivel" | 2 | 1 |
| 3 | "Fuente de Puntos" | `POINTS_SOURCE` | "Define de qué compradores de la red se toman los puntos que cuentan para el cálculo" | 3 | 1 |
| 4 | "Cierre de Ciclo Previo" | `CYCLE_COMPLETION` | "Valida haber cerrado al menos N ciclos completos del plan anterior" | 4 | 2 |
| 5 | "Ventas Personales por Ciclos" | `PERSONAL_SALES` | "Valida que las ventas personales lleguen a un total usando combinaciones válidas de ciclos" | 5 | 2 |
| 7 | "Mantenimiento del Plan" | `MAINTENANCE` | "Condición compuesta: el empresario debe cumplir un conjunto de reglas unidas por AND u OR" | null | 2 |
| 8 | "Conteo de Títulos en Primera Generación" | `DOWNLINE_TITLE_COUNT` | "Valida que existan N empresarios de un título específico en la primera generación (hijos directos)" | 7 | 3 |
| 9 | "Conteo de Títulos Life en Red" | `LIFE_TITLE_COUNT` | "Valida que existan N empresarios con título Life debajo de los Platinos de la red" | 8 | 3 |

---

## TABLA: rule_category_type
`id | label | key`

| id | label | key |
|----|-------|-----|
| 1 | Puntos | `POINTS` |
| 2 | Cumplimiento | `COMPLIANCE` |
| 3 | Red | `NETWORK` |

---

## TABLA: rule
`id | code | title | description | rule_category_id | times_triggered | is_active`

> `code` es el identificador de negocio (R1, R17…).
> `times_triggered` aplica igual que en benefits: cuántas veces puede dispararse esta regla en la vida del empresario.
> Una misma `code` puede tener varias filas si aplica distinto por plan (e.g. R20 para Platino vs Diamante).

---

## TABLA: rule_form_sections
`id | rule_category_id | section_label | section_key | section_order | is_active`

| id | rule_category_id | section_label | section_key | section_order | is_active |
|----|-----------------|---------------|-------------|---------------|-----------|
| 1 | 1 | "Configuración del umbral" | `THRESHOLD_CONFIG` | 1 | 1 |
| 2 | 2 | "Configuración del rango" | `RANGE_CONFIG` | 1 | 1 |
| 3 | 3 | "Configuración de fuente" | `SOURCE_CONFIG` | 1 | 1 |
| 4 | 4 | "Configuración del ciclo" | `CYCLE_CONFIG` | 1 | 1 |
| 6 | 5 | "Combinaciones de ciclos válidas" | `CYCLE_COMBINATIONS` | 1 | 1 |
| 7 | 8 | "Configuración de generación" | `DOWNLINE_CONFIG` | 1 | 1 |
| 8 | 9 | "Configuración de conteo Life" | `LIFE_COUNT_CONFIG` | 1 | 1 |

> `rule_category_id = 7` (MAINTENANCE) no tiene secciones porque su formulario es especial:
> sólo selecciona otras reglas existentes con un operador AND/OR. Se resuelve con `rule_dependencies`.

---

## TABLA: rule_form_fields
`id | rule_form_section_id | field_label | field_key | field_type | is_required | help_text | validation_rules | is_active`

### Sección 1 — POINTS_THRESHOLD
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 1 | 1 | "Puntos mínimos" | `MIN_POINTS` | `number` | 1 | "Cantidad mínima de puntos para que la regla se cumpla." | `{"min": 0, "decimals": 2}` |

### Sección 2 — POINTS_RANGE
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 2 | 2 | "Puntos mínimos del rango" | `MIN_POINTS` | `number` | 1 | "Límite inferior del rango de puntos (inclusivo)." | `{"min": 0, "decimals": 2}` |
| 3 | 2 | "Puntos máximos del rango" | `MAX_POINTS` | `number` | 0 | "Límite superior del rango de puntos. Dejar vacío si no tiene tope." | `{"min": 0, "decimals": 2, "nullable": true}` |

### Sección 3 — POINTS_SOURCE
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 4 | 3 | "Incluir compras personales" | `INCLUDE_PERSONAL` | `boolean` | 1 | "Indica si las compras propias del empresario cuentan en el cálculo." | `{"required": true}` |
| 5 | 3 | "Incluir compras de hijos" | `INCLUDE_CHILDREN` | `boolean` | 1 | "Indica si las compras de los referidos directos e indirectos cuentan." | `{"required": true}` |
| 6 | 3 | "Incluir red Soles Gen 1 y 2" | `INCLUDE_GEN1_2_SOLES` | `boolean` | 1 | "Indica si se incluyen los puntos aportados por empresarios 1 y 2 Soles de la red descendente." | `{"required": true}` |
| 7 | 3 | "Nivel máximo de hijos a contar" | `MAX_CHILD_LEVEL` | `number` | 0 | "Hasta qué nivel de profundidad se toman los hijos. Dejar vacío para todos los niveles." | `{"min": 1, "nullable": true}` |

### Sección 4 — CYCLE_COMPLETION
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 8 | 4 | "Plan anterior requerido" | `REQUIRED_PLAN` | `select` | 1 | "Plan que debe haberse cerrado antes de alcanzar este nivel." | `{"required": true, "options": "METHOD[fetchPlans]"}` |
| 9 | 4 | "Ciclos mínimos a cerrar" | `MIN_CYCLES` | `number` | 1 | "Cantidad mínima de ciclos completos que deben haberse cerrado en el plan anterior." | `{"min": 1}` |

### Sección 6 — PERSONAL_SALES (combinaciones de ciclos — REPEATABLE)
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 11 | 6 | "Ciclo a seleccionar" | `CYCLE_SELECTED` | `select` | 1 | "Elige a qué ciclo aplica esta combinación." | `{"required": true, "options": [12, 13]}` |
| 12 | 6 | "Puntos" | `CYCLE_POINTS` | `number` | 1 | "Cantidad de puntos requeridos en este ciclo." | `{"min": 0, "decimals": 2}` |
| 13 | 6 | "Fuente de puntos" | `POINTS_SOURCES` | `multiselect` | 1 | "Selecciona qué redes aportan puntos para esta combinación de ciclo." | `{"required": true, "options": [3, 4, 5]}` |

> Esta sección es **repetible** (`is_repeatable = true`): el usuario puede agregar N combinaciones. Cada una genera una fila en `form_answers` con el mismo campo pero diferente `repeat_index`.

### Sección 7 — DOWNLINE_TITLE_COUNT
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 16 | 7 | "Título requerido en la generación" | `REQUIRED_PLAN` | `select` | 1 | "Título que deben tener los empresarios directos para contar." | `{"required": true, "options": "METHOD[fetchPlans]"}` |
| 17 | 7 | "Número de generación" | `GENERATION` | `number` | 1 | "Generación desde la cual se cuentan los empresarios (1 = hijos directos)." | `{"min": 1}` |
| 18 | 7 | "Cantidad mínima de empresarios" | `MIN_COUNT` | `number` | 1 | "Cuántos empresarios con ese título deben existir en esa generación." | `{"min": 1}` |
| 19 | 7 | "Multiplicador mínimo de beneficio" | `MIN_BENEFIT_MULTIPLIER` | `number` | 1 | "Cada empresario debe haber alcanzado beneficios equivalentes al valor del punto multiplicado por este número." | `{"min": 1}` |

### Sección 8 — LIFE_TITLE_COUNT
| id | section_id | field_label | field_key | field_type | is_required | help_text | validation_rules |
|----|-----------|-------------|-----------|------------|-------------|-----------|-----------------|
| 20 | 8 | "Debajo de qué título" | `BENEATH_PLAN` | `select` | 1 | "Los empresarios Life se cuentan debajo de los empresarios de este título." | `{"required": true, "options": "METHOD[fetchPlans]"}` |
| 21 | 8 | "Cantidad mínima de empresarios Life" | `MIN_COUNT` | `number` | 1 | "Total de empresarios con título Life requeridos en toda la red bajo los Platinos." | `{"min": 1}` |
| 22 | 8 | "Puntos mínimos por empresario" | `MIN_POINTS_PER_MEMBER` | `number` | 1 | "Puntos mínimos que debe realizar cada empresario Life (personales o de su red No Life)." | `{"min": 0, "decimals": 2}` |

---

## TABLA: rule_form_answers
`id | rule_id | rule_form_field_id | answer | repeat_index`

> `repeat_index` es `null` en campos normales.
> En secciones repetibles (combinaciones de ciclos) se usa `0, 1, 2…` para agrupar filas de la misma repetición.

| id | rule_id | field_id | answer | repeat_index |
|----|---------|----------|--------|--------------|
| 1 | uuid-r17 | 10 (`TOTAL_POINTS`) | `"400"` | null |
| 2 | uuid-r17 | 11 (`CYCLE_1_POINTS`) | `"200"` | 0 |
| 3 | uuid-r17 | 12 (`CYCLE_2_POINTS`) | `"200"` | 0 |
| 4 | uuid-r17 | 11 (`CYCLE_1_POINTS`) | `"300"` | 1 |
| 5 | uuid-r17 | 12 (`CYCLE_2_POINTS`) | `"100"` | 1 |
| 6 | uuid-r17 | 11 (`CYCLE_1_POINTS`) | `"400"` | 2 |
| 7 | uuid-r17 | 12 (`CYCLE_2_POINTS`) | `"0"` | 2 |

---

## TABLA: rule_dependencies
`id | parent_rule_id | child_rule_id | operator | order_index`

> Solo aplica para reglas de categoría `MAINTENANCE`.
> El `operator` indica cómo se une este hijo con el siguiente en la evaluación.

| id | parent_rule_id | child_rule_id | operator | order_index |
|----|---------------|---------------|----------|-------------|
| 1 | uuid-r19 | uuid-r17 | `OR` | 0 |
| 2 | uuid-r19 | uuid-r18 | `OR` | 1 |

---

## TABLA: plan_rules
`id | plan_id | rule_id | is_required`

> Relaciona qué reglas aplican a qué plan.
> `is_required = true` significa que debe cumplirse para alcanzar/mantener el plan.

---

## Nota sobre secciones repetibles

Agregar `is_repeatable BOOLEAN DEFAULT FALSE` a `rule_form_sections` le indica al frontend que esa sección puede instanciarse N veces. El backend agrupa las respuestas por `(rule_id, rule_form_field_id, repeat_index)` para reconstruir cada combinación.


# Una duda, todo esto, ¿tambien permite que existan reglas compuestas?, ¿es decir reglas con subreglas?

Sí, lo permite, y ya está modelado en rule_dependencies. Pero vale la pena aclarar los dos niveles de composición que el modelo soporta:

Lo que ya funciona hoy — composición plana (R19)
R19 (MAINTENANCE)
 ├── R17  OR
 └── R18
R19 no tiene campos propios, solo referencia otras reglas con un operador. Esto ya está resuelto con rule_dependencies.

Lo que también soporta — composición anidada
Porque rule_dependencies es un self-join sobre rule, puedes anidar sin límite de profundidad:
R_COMPLEJA (MAINTENANCE)
 ├── R19 (MAINTENANCE)  AND       <-- otra regla compuesta
 │    ├── R17  OR
 │    └── R18
 └── R20 (DOWNLINE_TITLE_COUNT)
El modelo lo aguanta porque child_rule_id puede ser a su vez una regla de tipo MAINTENANCE que tenga sus propios hijos en la misma tabla.

Lo que hay que tener cuidado
Dos cosas que el modelo no previene por sí solo y deberías manejar en la capa de aplicación:

Ciclos: nada impide que R19 → R17 → R19, lo cual generaría un loop infinito al evaluar. Necesitas una validación al guardar que detecte referencias circulares.
Raíz huérfana: una regla MAINTENANCE sin hijos en rule_dependencies no tiene semántica. Conviene validar que al crear una regla de ese tipo tenga al menos dos hijos referenciados.

En resumen: el modelo ya es un árbol, y puede crecer en profundidad. La única adición que podrías considerar sería agregar una columna is_composite BOOLEAN en rule para que el frontend sepa de entrada si debe ir a buscar hijos en rule_dependencies o solo leer sus rule_form_answers.