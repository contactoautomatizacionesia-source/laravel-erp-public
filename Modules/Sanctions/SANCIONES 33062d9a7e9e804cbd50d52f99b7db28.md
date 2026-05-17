# SANCIONES

## Trazabilidad

- **`cat_complaint_source`**: identifica el origen de la falla cometida por el empresario.
- **`investigation_evidencia`**: pruebas adjuntadas a la investigación realizada.
- **`eui_defense`**: derecho de réplica del empresario.
- **`process_notification`**: registro estricto de comunicaciones para evitar nulidades por falta de notificación.

---

## Reincidencia

- En **`investigation`**, el campo **`offense_count`** permite escalar automáticamente la severidad de la sanción.

---

## Ejecución operativa

La tabla **`sanction_enforcement`** define banderas booleanas que el resto del ecosistema de software de *Lifehuni* debe leer para:

- **`block_orders`**: bloquear compras.
- **`freeze_earnings`**: congelar pagos de comisiones.
- **`block_qualification`**: suspender el crecimiento en el plan de carrera.

---

## Instancia de apelación

- La tabla **`committee_review`** permite que las decisiones sean revisadas por un comité superior, manteniendo el estado de suspensión mientras se decide.

---

## Flujo de datos

- **Apertura**: se crea una **`investigation`** vinculada a un **`users`**.
- **Instrucción**: se recopilan **`evidence`**, se envían **`notification`** y se reciben **`eui_defense`**.
- **Resolución**: se emite una **`sanction_resolution`** que puede incluir **`applied_mitigating`** (atenuantes).
- **Ejecución**: se activan los efectos en **`sanction_enforcement`** y se audita el cambio en **`eui_status_log`**.
- **Cierre / Apelación**: el caso se cierra o pasa a **`committee_review`**.

---

## Nota de arquitectura

Este modelo separa los **datos maestros** (usuarios/planes) de la **lógica de negocio configurable** (catálogos) y la **actividad transaccional**. Es un diseño preparado para auditorías legales y para una integración profunda con los módulos de e-commerce y pagos de la empresa.

# 🎑 Vistas

**Sanciones (Menú padre)**: ubicado debajo de **Gestión de Usuarios**.

- **🔍 Casos Activos**: enlace directo a la grilla de investigaciones en curso.
- **📜 Historial de Fallos**: consulta histórica de casos finalizados o archivados.
- **⚙️ Configuración**: acceso exclusivo para administradores para gestionar catálogos (**`cat_*`**) y tipos de faltas.