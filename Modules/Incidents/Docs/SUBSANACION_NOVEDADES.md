📌 Subsanación de novedades.

El sistema debe permitir que un usario pueda subsanar la novedad

Ejemplo 1: Un asesor debe responder por la novedad de perdida de productos ya se en un proceso de conteo o en un proceso de transferencia de productos:

El asesor debera comprar el producto faltante en su mismo centro de costo, este proceso subsanara la novedad generado facturas, ordenes y demás, es decir es un proceso de comprar normal. finalizada la compra por el asesor se relacionara la venta y factura con la novedad generando trazabilidad, cambios de estados y pagos a la novedad.

Ejemplo 2: El administrador es el reponsable de subsanar el producto porque este se perdio en el camino o se daño:

En este caso debe vincularse la novedad con el módulo existente “Salidas de inventario“, el administrador tendra la opción dentro de la novedad una opción “Dar salida de inventario“, esto lo llevara al módulo de salida de inventario y le permitira al admnistrador construir la respectiva salida.

Esta salida de inventario debera quedar relacionada con la novedad para que se pueda acceder desde ambas vistas “Novedades“ y “Salidas de inventario“.

Todas las salidas deben generar una orden de salida y a la vez factura, si el producto tiene IVA se cobra solo este, si no se cobra 1 peso, la factura de una salida siempre se debe aplicar al usuario RAIZ.

A continuación puede ver un ejemplo de una cortesia en el sistema actual, este proceso no debe generar puntos ni ningún tipo de redito.



Consolidación de reglas de negocio — Subsanación de novedades

Flujo A — Asesor subsana comprando el producto:

El asesor inicia el proceso de compra directamente desde la vista de detalle de la novedad en el CRM, con su sesión activa.

La compra se fuerza a incluir exactamente la cantidad de unidades faltantes de la novedad. No se permite compra parcial.

El precio de compra es siempre el precio público, independientemente del descuento E.U.I. del asesor.

Al confirmarse la compra con sus documentos correctos, la novedad se cierra automáticamente con referencia cruzada a la orden y factura generadas.

Flujo B — Administrador subsana mediante salida de inventario:

El administrador accede al formulario de "Salidas de inventario" directamente desde la novedad, con los campos pre-cargados (producto, cantidad, motivo "Pérdida").

La salida genera orden de salida + factura a nombre del usuario raiz.

Si el producto tiene IVA, la factura cobra solo el IVA. Si no tiene IVA, cobra $1.

La salida y la novedad quedan enlazadas bidireccionalmente: desde la novedad se ve la salida, y desde la salida se ve la novedad.

Al confirmarse la salida, la novedad se cierra automáticamente.



Los dos diagramas en conjunto cuentan la historia completa. El primero establece la precondición (novedad resuelta con responsable definido) y la bifurcación. El segundo muestra paso a paso qué hace cada actor, qué valida el sistema, qué documentos genera y qué evento interno dispara el cierre automático de la novedad.

Los puntos más importantes que el diagrama deja visibles de un vistazo: ambos flujos son iniciados manualmente por el actor desde la vista de la novedad, ambos tienen un rombo de validación que puede devolver al paso anterior si algo falla, y ambos convergen en el mismo estado final remediated con referencias cruzadas a los documentos generados.

Teniendo en cuenta lo anterior la tabla de lista de novedades debe agregar un indicador para saber el estado de la novedad al igual que en el detalle de la misma relacionando la orden con acceso a la misma.

