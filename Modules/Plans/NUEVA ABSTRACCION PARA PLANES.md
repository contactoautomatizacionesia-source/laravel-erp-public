# Abstracción CRUD para Plans

---

## TABLA: plan (plan padre)
`id | title | description | scale | cycle_type | custom_days | order | is_active`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | Identificador único |
| `title` | VARCHAR(100) | Nombre del plan padre. Ej: "Life", "Platino" |
| `description` | TEXT | Descripción general del plan |
| `scale` | ENUM | Unidad de medida del ciclo: `Diario`, `Semanal`, `Mensual`, `Ciclo` |
| `cycle_type` | ENUM nullable | Solo aplica si `scale = Ciclo`: `Quincenal`, `Mensual`, `Personalizado` |
| `custom_days` | INT nullable | Solo aplica si `cycle_type = Personalizado`: número de días del ciclo |
| `order` | INT | Orden de aparición o jerarquía visual entre planes padre |
| `is_active` | BOOLEAN | Si el plan está disponible para crear subplanes |

> `cycle_type` y `custom_days` son `null` cuando `scale` no es `Ciclo`.
> `custom_days` es `null` cuando `cycle_type` no es `Personalizado`.
> Estas reglas de negocio se validan en la capa de aplicación (como ya lo hace el modelo Laravel).

### Ejemplo de datos

| id | title | description | scale | cycle_type | custom_days | order | is_active |
|----|-------|-------------|-------|------------|-------------|-------|-----------|
| 1 | "Life" | "Plan base de ingreso a la red" | `Ciclo` | `Mensual` | null | 1 | 1 |
| 2 | "Platino" | "Primer nivel avanzado" | `Ciclo` | `Personalizado` | 45 | 2 | 1 |

---

## TABLA: plan_child (plan hijo)
`id | plan_id | title | description | level_order | is_active`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | Identificador único |
| `plan_id` | INT FK → `plan.id` | Plan padre al que pertenece este subplan |
| `title` | VARCHAR(100) | Nombre del subplan. Ej: "Life Platino", "Life Zafiro" |
| `description` | TEXT | Descripción específica del subplan |
| `level_order` | INT | Orden jerárquico dentro del plan padre. Define quién es "anterior" a quién |
| `is_active` | BOOLEAN | Si el subplan está activo y operable |

> Es el `plan_child` el que se asocia a reglas y beneficios, no el plan padre.
> `level_order` es único por `plan_id`: dos hijos del mismo padre no pueden tener el mismo orden.

### Ejemplo de datos

| id | plan_id | title | description | level_order | is_active |
|----|---------|-------|-------------|-------------|-----------|
| 1 | 1 | "Life" | "Nivel base del plan Life" | 1 | 1 |
| 2 | 1 | "Life Platino" | "Primer nivel avanzado del plan Life" | 2 | 1 |
| 3 | 1 | "Life Zafiro" | "Segundo nivel avanzado del plan Life" | 3 | 1 |
| 4 | 1 | "Life Esmeralda" | "Tercer nivel avanzado del plan Life" | 4 | 1 |
| 5 | 1 | "Life Diamante" | "Cuarto nivel avanzado del plan Life" | 5 | 1 |

---

## TABLA: plan_rules
`plan_child_id | rule_id | is_required`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `plan_child_id` | INT FK → `plan_child.id` | Subplan al que aplica la regla |
| `rule_id` | UUID FK → `rule.id` | Regla asociada |
| `is_required` | BOOLEAN | Si la regla es obligatoria para alcanzar o mantener el subplan |

---

## TABLA: plan_benefits
`plan_child_id | benefit_id`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `plan_child_id` | INT FK → `plan_child.id` | Subplan al que aplica el beneficio |
| `benefit_id` | INT FK → `benefit.id` | Beneficio asociado |

---

## Vista de relaciones

```
plan (padre)
 │
 └── plan_child (hijo) ──────── plan_rules ──── rule
      │    └── level_order                        │
      │         define jerarquía             rule_form_answers
      │
      └────────────────────── plan_benefits ── benefit
                                                  │
                                           benefit_form_answers
```

---

## Notas de diseño

**¿Por qué dos tablas y no self-join?**
El código Laravel original usaba `parent_id` en la misma tabla `plans`, lo que permite profundidad ilimitada pero complica las consultas y no refleja que el negocio solo tiene exactamente dos niveles: padre y subplan. Con dos tablas separadas el modelo es explícito, las consultas son simples y las foreign keys hacia rules y benefits quedan siempre sobre `plan_child`, nunca ambiguas.

**`level_order` vive en `plan_child`**
Porque el orden jerárquico solo tiene sentido entre hermanos del mismo plan padre. "Life Platino es nivel 2" solo aplica dentro del plan "Life".

**El plan padre no tiene reglas ni beneficios directos**
Es un contenedor de configuración (escala, ciclo, orden). Toda la lógica de negocio opera sobre sus hijos.