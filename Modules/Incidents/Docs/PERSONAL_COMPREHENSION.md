Novedades
-> Reportes de diferencias entre conteo de productos de quien envía y de quien recibe

	Caso 1: La sede origen envía X unidades, la sede destino reporta haber recibido menos. Esa diferencia no tiene un responsable claro.
	Caso 2: El sistema dice que la sede debería tener X unidades, pero el asesor hace el conteo y reporta tener menos. Esa diferencia tampoco tiene resolución

Actores:
	-> Asesor/Responsable de la sede: Quien genera la operación (transferencia o conteo). Es el primer imputado de la novedad
	-> Administrador del sistema: Quien investiga, valida y toma la decisión final: si el asesor paga o lo asume la organización.
	-> Organización: Puede asumir la perdida si el administrador lo determina.

La novedad se genera cuando:
	-> Se aprueba una transferencia con diferencia (lado destino)
	-> El administrador aprueba un conteo con faltante.

La responsabilidad recae sobre:
	-> SIEMPRE, sobre el asesor de la sede destino que reporto la diferencia
	-> La única excepción es cuando el asesor ya no esta vinculado, en este momento la UI debe marcar que el administrador puede reasignar la responsabilidad a la organización

Gestión de los casos:
	Caso 1 desglose: 
		1. Destino reporta una cantidad menor, adjunta fotos y evidencias. 
		2. El sistema genera la novedad. 
		3. El sistema tiene un plazo configurable para respuesta de la novedad. 
		4. Si el origen asume el error reportado por el destino dentro del plazo configurable adjuntando notas + evidencias obligatorias se realiza una reversión automática al origen de 		las unidades que no llegaron al destino.
		5. Novedad cerrada sin intervención del administrador.

	Caso 2 desglose:
		1. Asesor realiza el conteo de inventario y reporta unidades físicas faltantes que se habían aceptado, es decir, lo que el cuenta en su inventario físico no coincide con lo que la 		plataforma le marca.
		2. El sistema genera la novedad.
		3. Administrador entra a investigar, la novedad se marca como "En investigación".
		4. El administrador determina quien responde por las unidades faltantes, si el asesor que reporto el conteo o la organización lo asume por cortesía.
		5. Si el administrador determina que el asesor debe responder: El asesor subsana comprando el producto al precio público — esto genera una transacción real en el CRM y cruza contra 		la novedad.
		6. Si el administrador determina que la organización debe responder: esto genera una salida tipo cortesía, con su factura y sus cruces financieros. Esto implica que el módulo debe 		poder disparar ese proceso de salida de inventario hacia el sistema existente.

Definiciones cerradas del modulo de Novedades
	
	- Valor económico de la novedad: se toma el precio público del producto en el momento en que se genera la novedad. Este valor no cambia aunque el precio del producto cambie después. Esto 	implica que el módulo debe capturar y guardar el precio público vigente al momento del evento, no consultarlo dinámicamente.

	- Asesor: Puede ver todas las novedades de su sede (no solo las suyas)

	- Administrador: Todas las novedades de todas las sedes

Notificaciones: doble canal — correo electrónico y notificación interna en el sistema. Los eventos que disparan notificación son: creación de novedad (al asesor responsable y al administrador), cambio de estado (a los involucrados), y resolución final (al asesor y al administrador).
	
Atributos mínimos de una novedad
- Cada registro de novedad debe guardar al menos:
	1. Identificación: ID único, fecha y hora de creación, origen (transferencia o conteo) con referencia al documento fuente.
	2. Producto: referencia al producto, unidades faltantes, precio público capturado al momento de la novedad, valor total de la novedad (unidades × precio público).
	3. Responsabilidad: sede donde ocurrió el evento, asesor responsable asignado.
	4. Resolución: estado actual, tipo de resolución (asesor / organización / anulada), justificación del administrador, fecha de cierre.
	5. Auditoría: log de todos los cambios de estado con usuario, fecha y acción.

Reglas de negocio consolidadas

	1. Sobre el plazo del origen: el sistema tendrá un parámetro configurable (por ejemplo, 48 horas por defecto) dentro del cual la sede origen puede pronunciarse. Si el plazo vence sin 	respuesta, la novedad escala automáticamente al administrador. Esto implica que el módulo necesita un job o tarea programada que evalúe novedades vencidas y cambie su estado.

	2. Sobre el reconocimiento voluntario del origen: si el origen asume el error, la novedad se cierra directamente sin pasar por el administrador, siempre que queden registradas las notas y 	evidencias. El administrador no interviene, pero sí puede ver el registro cerrado en modo auditoría.

	3. Sobre las evidencias del destino: el asesor destino debe poder subir evidencias (fotos, observaciones escritas) en el momento de reportar la diferencia, o dentro de un período 	posterior. Estas evidencias quedan visibles para el origen y para el administrador.

Regla de negocio cerrada
	1. Cuando el origen reconoce el error:
	2. El sistema cierra la novedad (con notas y evidencias obligatorias).
	3. El sistema ejecuta automáticamente la reversión de inventario: las unidades de diferencia regresan al stock de la sede origen.
	4. El módulo de Novedades no hace seguimiento de la transferencia correcta posterior. Esa es responsabilidad operativa del asesor de origen.

El cierre tiene dos caminos distintos con consecuencias diferentes en el sistema:
	-> El asesor subsana comprando el producto al precio público — esto genera una transacción real en el CRM y cruza contra la novedad.
	-> La organización asume — esto genera una salida tipo cortesía, con su factura y sus cruces financieros. Esto implica que el módulo debe poder disparar ese proceso de salida de inventario 	hacia el sistema existente.

	-> La configuración de plazos vive dentro del módulo en una sección propia (Configuraciones), lo que ya define la estructura del menú.