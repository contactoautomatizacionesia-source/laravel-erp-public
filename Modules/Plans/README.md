# Plans Module — Formularios Dinámicos, Selects y Multiselect

## Índice
1. [Arquitectura general](#1-arquitectura-general)
2. [Estructura de base de datos](#2-estructura-de-base-de-datos)
3. [Tabla de opciones centralizada (form_options)](#3-tabla-de-opciones-centralizada-form_options)
4. [API de estructura del formulario](#4-api-de-estructura-del-formulario)
5. [Tipos de campo soportados](#5-tipos-de-campo-soportados)
6. [Secciones repetibles](#6-secciones-repetibles)
7. [El componente Multiselect](#7-el-componente-multiselect)
8. [Flujo de guardado (submit)](#8-flujo-de-guardado-submit)
9. [Flujo de carga al editar](#9-flujo-de-carga-al-editar)
10. [Serialización de respuestas](#10-serialización-de-respuestas)
11. [Diferencias Rules vs Benefits](#11-diferencias-rules-vs-benefits)
12. [Puntos críticos y advertencias](#12-puntos-críticos-y-advertencias)

---

## 1. Arquitectura general

El módulo implementa un sistema de **formularios dinámicos** donde la estructura del formulario (secciones, campos, tipos) se define en base de datos y se construye en el frontend mediante AJAX.

Desde la migración `2026_03_18_000014`, las tablas de catálogo y respuestas están **unificadas** para Rules y Benefits (y cualquier entidad futura):

```
rule_category_type
    └── rule_category  (key único, ej: "MAINTENANCE")
            └── form_sections  (owner_key = rule_category.key, is_repeatable)
                    └── form_fields  (field_type, validation_rules)
                            └── form_answers  (formable_type='rule', formable_id, form_field_id)

benefit_category_type
    └── benefit_category  (key único, ej: "MONETARY_BONUS")
            └── form_sections  (owner_key = benefit_category.key, is_repeatable)
                    └── form_fields  (field_type, validation_rules)
                            └── form_answers  (formable_type='benefit', formable_id, form_field_id)
```

La relación `category → form_sections` es por `owner_key` (string), no por FK, lo que permite reutilizar el sistema para cualquier entidad futura sin modificar el esquema.

### Entidades Laravel involucradas

| Entidad | Tabla | Descripción |
|---|---|---|
| `FormSection` | `form_sections` | Sección del formulario; `owner_key` = key de la categoría |
| `FormField` | `form_fields` | Campo individual con tipo y reglas de validación |
| `FormOption` | `form_options` | Opción centralizada para select/multiselect |
| `FormAnswer` | `form_answers` | Respuesta polimórfica (formable_type + formable_id) |

### HasFormAnswers trait

`Rule` y `Benefit` usan el trait `Modules\Plans\Traits\HasFormAnswers`, que expone:

```php
public function formAnswers()
{
    return $this->morphMany(FormAnswer::class, 'formable');
}
```

El morph map en `PlansServiceProvider::boot()` registra los aliases cortos:

```php
Relation::morphMap([
    'rule'    => Rule::class,
    'benefit' => Benefit::class,
]);
```

Así la columna `formable_type` almacena `'rule'` o `'benefit'` (no el namespace completo).

---

## 2. Estructura de base de datos

### Tablas de catálogo (definición del formulario)

| Tabla | Campo clave | Descripción |
|---|---|---|
| `form_sections` | `owner_key` | Key de la categoría propietaria (ej: `"MAINTENANCE"`, `"MONETARY_BONUS"`) |
| `form_sections` | `is_repeatable` | Si `true`, el usuario puede agregar N filas |
| `form_fields` | `field_type` | Enum: `number`, `select`, `boolean`, `text`, `currency`, `multiselect` |
| `form_fields` | `validation_rules` | JSON libre con opciones, rangos, métodos dinámicos |

### Tabla de respuestas (polimórfica)

```sql
form_answers
  - formable_type   -- 'rule' | 'benefit' (morph alias)
  - formable_id     -- ID del registro propietario
  - form_field_id   -- FK → form_fields.id
  - answer          -- string; para multiselect: JSON '[1,2]'
  - repeat_index    -- NULL para campos normales; 0,1,2... para secciones repetibles
```

### El campo `validation_rules`

Es un JSON flexible. Ejemplos de uso:

```json
// Para select/multiselect con opciones centralizadas
{ "required": true, "options": [1, 2] }

// Para select con carga dinámica
{ "options": "METHOD[fetchPlanChildren]" }

// Para number
{ "min": 0, "max": 100, "decimals": true }
```

Cuando `options` es un array de enteros, los IDs referencian registros en `form_options`. El backend resuelve los IDs a objetos completos antes de entregarlos al frontend (ver sección 3).

---

## 3. Tabla de opciones centralizada (form_options)

### Motivación

Antes, las opciones de selects con valores fijos se almacenaban inline en `validation_rules`:
```json
{ "options": [{"es": "Fijo", "en": "Fixed"}, {"es": "Porcentaje", "en": "Percentage"}] }
```
Y el `answer` guardaba el texto en inglés: `"Fixed"`. Esto hacía imposible cambiar el label sin romper datos existentes y no permitía almacenar metadatos por opción.

### Tabla unificada

```sql
form_options
  id
  option_label  JSON            -- {"es": "Fijo", "en": "Fixed"}
  option_key    VARCHAR unique  -- "FIXED"
  help_text     JSON (nullable)
```

Una sola tabla sirve para rules y benefits. Las referencias por ID en `validation_rules` apuntan a esta tabla.

### Qué cambia

| Antes | Ahora |
|---|---|
| `validation_rules.options: [{es,en}, ...]` | `validation_rules.options: [1, 2]` (IDs) |
| `answer: "Fixed"` | `answer: "1"` (ID de la opción) |
| `answer: '["Fixed","Percentage"]'` | `answer: '[1,2]'` (IDs) |

### Resolución en el backend (`resolveFieldOptions`)

Tanto `get_form_structure` como `show` llaman a `resolveFieldOptions()` antes de devolver los campos al frontend. Este método detecta si `options` es un array de enteros, consulta `form_options` y sustituye los IDs por objetos completos:

```php
// Entrada:  { "required": true, "options": [1, 2] }
// Salida:   { "required": true, "options": [
//               { "id": 1, "option_label": {"es":"Fijo","en":"Fixed"}, "option_key": "FIXED", "help_text": null },
//               { "id": 2, "option_label": {"es":"Porcentaje","en":"Percentage"}, "option_key": "PERCENTAGE", "help_text": null }
//           ]}
```

Selects con `METHOD[...]` y opciones legacy inline no se modifican.

### Resolución en el frontend (`resolveAnswerDisplay`)

`window.plansUtils.resolveAnswerDisplay(answer, field)` resuelve el ID guardado al label traducido para mostrar en la modal de detalle:

```js
// answer = "1", field.validation_rules.options contiene objetos con .id
// → devuelve getTranslatedValue(opt.option_label, opt.option_key) → "Fixed" / "Fijo"
```

---

## 4. API de estructura del formulario

### Rules
```
GET /plans/rules/form-structure/{categoryId}
```
Respuesta:
```json
{
  "is_maintenance": false,
  "sections": [
    {
      "id": 1,
      "section_label": {"es": "...", "en": "..."},
      "section_key": "general",
      "section_order": 1,
      "is_repeatable": false,
      "fields": [
        {
          "id": 5,
          "field_label": {"es": "...", "en": "..."},
          "field_key": "amount",
          "field_type": "number",
          "is_required": true,
          "help_text": null,
          "validation_rules": { "min": 0, "decimals": true }
        }
      ]
    }
  ]
}
```

### Benefits
```
GET /plans/benefits/form-structure/{categoryId}
```
Igual al anterior pero retorna `has_form` en lugar de `is_maintenance`:
```json
{ "has_form": true, "sections": [...] }
```

---

## 5. Tipos de campo soportados

| `field_type` | HTML generado | Notas |
|---|---|---|
| `text` | `<input type="text">` | Por defecto |
| `number` | `<input type="number">` | min/step desde `validation_rules` |
| `currency` | `<input type="text" class="currency-mask">` | Máscara monetaria aplicada por `applyCurrencyMask()` |
| `boolean` | `<input type="checkbox" value="1">` | Unchecked se serializa como `'0'` |
| `select` | `<select>` | Ver variantes abajo |
| `multiselect` | `.ign-multiselect-wrapper` | Ver sección 6 |

### Variantes de `select` y `multiselect`

**Opciones centralizadas** — `validation_rules.options` es array de objetos con `.id` (ya resuelto por el backend):
```js
// El backend entrega: options: [{id:1, option_label:{es,en}, option_key:"FIXED"}, ...]
// buildFieldHtml genera <option value="1">Fixed</option>
// El answer guardado es el ID: "1" (select) o '[1,2]' (multiselect JSON)
// Se resuelven con getTranslatedValue(o.option_label, o.option_key)
```

**Carga dinámica** — `validation_rules.options === "METHOD[fetchPlanChildren]"`:
```js
// Se genera <select class="dynamic-select" data-method="fetchPlanChildren">
// initDynamicSelects() lanza AJAX GET /plans/rules/plan-children-list
// Resultado cacheado en planChildrenCache (una sola carga por página)
// Sin cambios: estos selects siempre guardaron el ID del registro
```

**`METHOD[fetchPermissions]`** (solo en benefits):
```js
// Se renderiza como <input type="text"> simple
// No hay endpoint AJAX implementado todavía
```

---

## 6. Secciones repetibles

Cuando `section.is_repeatable === true`, el formulario renderiza filas que el usuario puede agregar y eliminar.

### Naming de campos en secciones repetibles

```
Sección normal:       answers[fieldId]
Sección repetible:    answers[fieldId][repeatIndex]
```

### Cómo funciona

1. Se renderiza una fila inicial con `index = 0`
2. El botón "Agregar combinación" agrega la siguiente fila con `index = n`
3. El botón de eliminar de cada fila no actúa si es la única fila restante
4. Al guardar, el service itera `answers[fieldId]` como array y persiste cada `repeatIndex` por separado

### Al editar — expansión automática de filas

Al cargar datos guardados, `populateAnswers()` detecta el `repeat_index` máximo por sección y **crea automáticamente las filas faltantes** antes de poblar los valores:

```js
// Para cada answer con repeat_index:
//   Busca la sección repetible que contiene el field
//   Crea filas hasta que container.find('.repeat-row').length > repeatIdx
//   Inicializa multiselects de las filas nuevas
```

---

## 7. El componente Multiselect

### Archivos involucrados

| Archivo | Propósito |
|---|---|
| `Modules/Plans/Resources/views/components/multiselect-engine.blade.php` | Motor JS global (`window.ignMultiselect`) — incluido via `@include` en `@push('scripts')` |
| `resources/views/components/admin/multiselect.blade.php` | Componente Blade reutilizable para usar con `<x-admin.multiselect>` |
| `public/css/ign_custom.css` | Estilos del componente (sección `IGN MULTISELECT`) |

### Estructura HTML generada

```html
<div class="ign-multiselect-wrapper" data-id="ms_answers_9_" data-name="answers[9]" data-placeholder="...">

    <!-- Select nativo oculto: maneja accesibilidad y estado real -->
    <select name="answers[9][]" multiple style="position:absolute;opacity:0;pointer-events:none;height:0;width:0;">
        <option value="Option A">Opción A</option>
        <option value="Option B">Opción B</option>
    </select>

    <!-- Control visual: chips + input de búsqueda -->
    <div class="ign-ms-control" id="ms_answers_9_" tabindex="0">
        <div class="ign-ms-chips-area">
            <div class="ign-ms-chips"></div>
            <input type="text" class="ign-ms-search" autocomplete="off">
        </div>
        <span class="ign-ms-arrow" aria-hidden="true"><i class="ti-angle-down"></i></span>
    </div>

    <!-- Dropdown de opciones -->
    <div class="ign-ms-dropdown" style="display:none;">
        <div class="ign-ms-options">
            <button type="button" class="ign-ms-option" data-value="Option A">
                <span class="ign-ms-check"><i class="ti-check"></i></span>
                <span class="ign-ms-option-label">Opción A</span>
            </button>
            <span class="ign-ms-empty" style="display:none;">Sin resultados</span>
        </div>
    </div>
</div>
```

### Inicialización del motor

El motor se inicializa en `initDynamicSelects()` leyendo las opciones del DOM:

```js
$('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
    if (this._ignMs || !window.ignMultiselect) return;
    var opts = [];
    $(this).find('.ign-ms-option').each(function () {
        opts.push({
            value: String($(this).data('value')),
            label: $(this).find('.ign-ms-option-label').text().trim()
        });
    });
    window.ignMultiselect.init(this, opts, []);
    // wrapper._ignMs queda seteado con la API pública
});
```

### API pública del motor (`wrapper._ignMs`)

```js
wrapper._ignMs.getSelected()        // → ['1', '2']  (IDs de opción, como strings)
wrapper._ignMs.setSelected(['1'])   // actualiza estado y re-renderiza chips
wrapper._ignMs.setOptions([{value, label}])  // reemplaza opciones dinámicamente
wrapper._ignMs.reset()              // limpia selección
wrapper._ignMs.destroy()            // desliga event listeners
```

### Dónde se incluye el motor

El motor debe estar disponible **antes** de que cualquier formulario dinámico lo invoque. Se incluye al inicio del `@push('scripts')` en cada vista:

```blade
@push('scripts')
@include('plans::components.multiselect-engine')
{{-- resto del JS de la página --}}
```

> **Importante**: el motor NO se incluye via `@once` ni mediante el componente Blade `<x-admin.multiselect>`, ya que esas vistas usan renderizado JS dinámico (no Blade) para construir el formulario.

---

## 8. Flujo de guardado (submit)

El form se envía con `contentType: 'application/json'` desde jQuery AJAX. Esto requiere un paso previo para los multiselect:

```js
$('#ruleForm').on('submit', function (e) {
    e.preventDefault();

    // 1. Serializar multiselects como JSON string antes de serialize()
    $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
        let msName = $(this).data('name');          // "answers[9]"
        let vals   = this._ignMs.getSelected();     // ["1", "2"]  ← IDs de opción
        $(this).find('select').prop('disabled', true);  // excluir el <select> nativo
        $(this).append($('<input type="hidden">').attr('name', msName).val(JSON.stringify(vals)));
    });

    let formData = $(this).serialize();

    // 2. Restaurar estado original
    $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
        $(this).find('select').prop('disabled', false);
        $(this).find('input[type="hidden"][name^="answers"]').remove();
    });

    $.ajax({ data: formData, ... });
});
```

**Por qué este enfoque**: sin desactivar el `<select multiple>`, jQuery serialize produce `answers[9][]=1&answers[9][]=2`, que PHP convierte en un array — el service lo trataría como sección repetible y guardaría dos filas separadas con `repeat_index=0` y `repeat_index=1`. Con el hidden input JSON se guarda un solo registro con `answer='[1,2]'`.

### Cómo llega al service (PHP)

```
POST: answers[9]=%5B1%2C2%5D

// $request->input('answers.9') → '[1,2]'  ← string, no array
```

El service detecta que es string (not is_array) y lo persiste como una sola respuesta:
```php
$benefit->formAnswers()->create([
    'form_field_id' => 9,
    'answer'        => '[1,2]',
    'repeat_index'  => null,
]);
```

El morph map se encarga automáticamente de setear `formable_type = 'benefit'` y `formable_id = $benefit->id`.

---

## 9. Flujo de carga al editar

```
$.get('/plans/rules/{id}/edit')
  → rule.form_answers = [
      { form_field_id: 9, answer: '[1,2]', repeat_index: null }
    ]

loadFormStructure(rule.rule_category_id, function() {
    populateAnswers(rule.form_answers, rule.dependencies);
})
```

### `populateAnswers` — lógica de hidratación

```js
// 1. Expandir filas repetibles según repeat_index máximo guardado
// 2. Agrupar answers por fieldId
// 3. Para cada fieldId:

//    A) Multiselect no-repetible:
let msWrapper = $('#dynamic-form-container .ign-multiselect-wrapper[data-name="answers[9]"]');
if (msWrapper[0]._ignMs) {
    // Juntar todos los valores — ahora son IDs: ["1", "2"]
    let allVals = [];
    answers.forEach(ans => {
        try { let p = JSON.parse(ans.answer); allVals = allVals.concat(Array.isArray(p) ? p : [ans.answer]); }
        catch(e) { if (ans.answer) allVals.push(ans.answer); }
    });
    msWrapper[0]._ignMs.setSelected(allVals);  // busca data-value="1", data-value="2"
}

//    B) Multiselect en sección repetible:
let msWrapper = $(`[data-name="answers[9][0]"]`);
msWrapper[0]._ignMs.setSelected(['1']);
```

### Dato legacy: respuestas guardadas incorrectamente

Si un multiselect fue guardado con `repeat_index=0,1` (bug en versiones anteriores al fix del submit), la lógica de populate lo detecta: verifica que el campo **no pertenece** a una sección repetible, y si encuentra un multiselect wrapper en modo non-repeat, **junta todos los valores** en un solo `setSelected()`.

---

## 10. Serialización de respuestas

| Tipo de campo | Cómo se guarda en `answer` | Cómo se carga |
|---|---|---|
| `text`, `number`, `currency` | Valor directo: `"42.50"` | `input.val(answer)` |
| `boolean` | `"1"` o `"0"` | `input.prop('checked', val === '1')` |
| `select` con opciones centralizadas | ID de la opción: `"1"` | `select.val(answer)` |
| `select` con `METHOD[...]` | ID del registro dinámico: `"42"` | `select.val(answer)` |
| `multiselect` con opciones centralizadas | JSON de IDs: `'[1,2]'` | `JSON.parse(answer)` → `setSelected(parsed)` |
| Sección repetible | Mismo que arriba por cada fila | `repeat_index` identifica la fila |

La columna FK en `form_answers` es siempre `form_field_id` (unificado tras migración 000014).

---

## 11. Diferencias Rules vs Benefits

| Aspecto | Rules | Benefits |
|---|---|---|
| Categoría especial | `MAINTENANCE` (sin form, usa dependency builder) | No hay |
| Respuesta API form-structure | `{ is_maintenance, sections }` | `{ has_form, sections }` |
| Secciones repetibles | Soportado (`is_repeatable` en `form_sections`) | Soportado |
| Campo extra del form principal | `times_triggered` | `is_cumulative`, `times_triggered` |
| METHOD dinámico | `METHOD[fetchPlanChildren]` → AJAX select | `METHOD[fetchPermissions]` → input text |
| Container HTML | `#dynamic-form-container` | `#benefit-dynamic-form` |
| Clases de filas repetibles | `.repeat-row`, `.repeatable-rows` | `.b-repeat-row`, `.b-repeatable-rows` |
| Cache de secciones | `_sectionsCache` | `_benefitSectionsCache` |
| Dependencies | Sí (self-join `rule_dependencies`) | No |
| `formable_type` en `form_answers` | `'rule'` | `'benefit'` |
| Field FK en respuestas | `form_field_id` (unificado) | `form_field_id` (unificado) |

---

## 12. Puntos críticos y advertencias

### El motor JS debe cargarse antes del código de la página

```blade
@push('scripts')
@include('plans::components.multiselect-engine')  {{-- PRIMERO --}}
<script> ... código de la página ... </script>
```

Si el motor no está disponible cuando `initDynamicSelects()` corre, el multiselect queda sin inicializar (`_ignMs === undefined`) y falla silenciosamente.

### El `<select>` nativo del multiselect no se debe serializar directamente

El `<select multiple name="answers[9][]">` serializa como array PHP — el service lo interpreta como sección repetible. Siempre usar el patrón disable + hidden input JSON antes de serialize.

### Orden en `populateAnswers`: primero expandir filas, luego poblar

Si se intenta poblar valores antes de que existan las filas DOM, los selectores no encuentran el wrapper y la operación es silenciosa.

### `data-name` como identificador del multiselect

El wrapper `.ign-multiselect-wrapper` usa `data-name` (no `id`) como identificador para los selectores de populate. El valor coincide exactamente con el `name` del campo: `answers[9]` o `answers[9][0]`.

### Enum `field_type` en migración

La migración original define `ENUM('number','select','boolean','text')`. Los tipos `currency` y `multiselect` fueron agregados mediante migración posterior (`2026_03_17_000010`). Si se regenera la tabla desde cero, usar la migración más reciente.

### Agregar opciones a selects: usar `form_options`, no `validation_rules`

Para agregar, renombrar o eliminar opciones de un select/multiselect con opciones fijas, operar sobre `form_options` directamente. **No** volver al formato inline en `validation_rules` — los answers existentes guardan IDs y se romperían.

### `show()` debe resolver opciones igual que `get_form_structure`

Ambos endpoints llaman a `resolveFieldOptions()` antes de devolver campos al frontend. Si se agrega un nuevo endpoint que devuelva `form_answers`, debe incluir la misma resolución; de lo contrario, la modal de detalle mostrará IDs en lugar de labels.

### Extender el sistema a nuevas entidades

Para agregar una nueva entidad (ej: `Coverage`) al sistema de formularios dinámicos:

1. Crear `coverage_category` con campo `key`
2. Relacionar `CoverageCategory::formSections()` via `hasMany(FormSection::class, 'owner_key', 'key')`
3. Agregar `Coverage` al morph map: `'coverage' => Coverage::class`
4. Usar el trait `HasFormAnswers` en el modelo `Coverage`
5. Insertar secciones en `form_sections` con `owner_key = 'COVERAGE_CATEGORY_KEY'`
6. No se requiere ninguna nueva tabla de catálogo ni de respuestas
