# Consideraciones
---

### 1. Definición de la Jerarquía de Tesorería (Arquitectura de Datos)
Para lograr un control granular, el sistema debe entender la relación de dependencia entre las entidades. No es solo una lista de cajas, es un **árbol de flujo de efectivo**:

* **Nivel 1: Caja Fuerte (Caja Madre):** Entidad central receptora de todos los Centros de Costo (CC). Es el nodo raíz.
* **Nivel 2: Caja Principal (Caja Líder del CC):** Una por Centro de Costo. [cite_start]Es la encargada de consolidar y enviar a la Caja Madre.
* **Nivel 3: Cajas Auxiliares (Operadores):** Múltiples cajas por CC. Reportan directamente a su Caja Principal.



---

### 2. El Ciclo de Vida de la Caja (Business Logic)
Un sistema con "control bancario" requiere que cada caja pase por estados estrictos:

1.  **Apertura (Base Inicial):** El sistema carga la base configurable por CC (ej. $100,000). Este valor es un "pasivo" temporal: se usa para operar pero no cuenta como ingreso del día.
2.  **Operación:** Registro de transacciones por múltiples medios (Efectivo, Datáfono, Transferencia).
3.  **Pre-Arqueo (Conteo Físico):** El operador cuenta billete por billete y moneda por moneda.
4.  **Conciliación (Cuadre):** El sistema compara el **Saldo en Sistema** vs. **Conteo Físico**. 
    **Regla de Oro:* Si hay diferencia (Sobrante/Faltante), el sistema bloquea el cierre hasta que se registre una "Justificación de Novedad" o se corrija el conteo.
5.  **Cierre y Entrega:** La caja auxiliar transfiere el recaudo a la Principal, quedando nuevamente solo con su base inicial.
6.  **Consolidación y Envío:** El líder del CC suma las entregas, realiza un reconteo físico total y despacha a la Caja Madre.

---

### 3. Matriz de Requisitos Granulares (Especificaciones Técnicas)

#### A. Control por Denominación (Efectivo)
El sistema no recibe un valor total manual. El usuario llena una **Grilla de Denominaciones**:
* **Campos:** ID_Denominación, Valor_Nominal, Cantidad, Subtotal (Calculado).
* **UX:** Selector rápido para moneda local (Billetes de 10k, 20k, 50k, etc.).

#### B. Control de Medios de Pago (Conciliación Bancaria)
Para medios no físicos (Datáfono/Transferencia), el rigor exige:
* **Datáfono:** Registro de "Lotes" y número de vouchers.
* **Transferencia:** Registro de número de confirmación o referencia bancaria.

#### C. Seguridad y Auditoría (Compliance)
* **No Repudio:** Cada cierre debe llevar un "Hash" digital que impida la modificación de los valores una vez entregados a la siguiente instancia.
* **Logs de Modificación:** Registro obligatorio de `user_id`, `timestamp` y `old_value` / `new_value` para cualquier ajuste.

---

### 4. Flujo de Información (Data Flow Diagram)

1.  **Auxiliar:** `Conteo Físico` + `Vouchers` -> **Cierre Auxiliar**.
2.  **Validación:** `Sistema` vs `Conteo` == 0? 
    * *No:* Generar Ticket de Novedad (Sobrante/Faltante).
    * *Si:* Habilitar botón "Entregar a Principal".
3.  **Principal:** `Recibir de Auxiliares` -> `Reconteo Total` -> **Cierre de Centro de Costo**.
4.  **Líder CC:** `Generar Envío a Caja Fuerte` -> **Estado: En Tránsito**.
5.  **Caja Madre:** `Confirmar Recepción` -> **Estado: Depositado**.

---

### 5. Optimización Propuesta (Valor Agregado de Experto)
Para que esto sea realmente "como un banco", sugiero añadir:
1.  **Módulo de Novedades:** Si hay un faltante, el sistema debe crear automáticamente una "Cuenta por Cobrar" al empleado, o permitir que el líder autorice el cierre asumiendo el gasto.
2.  **Dashboard de Tesorería en Tiempo Real:** Donde la gerencia pueda ver cuánto dinero hay "en mano" en todos los países, cuánto está "en tránsito" y cuánto ya está en la "Caja Madre".
3.  **Configuración de Topes:** Alertar si una caja auxiliar supera un monto de efectivo (ej. $2.000.000) para exigir un "Retiro Parcial" hacia la caja principal por seguridad.