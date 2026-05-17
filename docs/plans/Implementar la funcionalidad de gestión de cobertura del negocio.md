📌 Gestión de cobertura del negocio

La funcionalidad de gestión de cobertura del negocio permite a los usuarios autorizados parametrizar y administrar países, regiones y ciudades organizados jerárquicamente.

🎯 Contexto

Refinar la gestión de la cobertura geográfica del sistema, mejorando la integridad de los datos mediante lógica de cascada y optimizando la experiencia de usuario en la interacción con las tablas y formularios.

  HU

https://project-development-team.atlassian.net/wiki/x/34CI

✅ Funcionalidades requeridas

🎨 Frontend

  Gestión de Tablas (DataGrid)

Ordenamiento Dinámico: Todas las cabeceras de las columnas de la tabla (ej. Nombre, Código, Estado) deben ser interactivas. Al hacer clic, la grilla debe reordenarse de forma ascendente/descendente según el valor de dicha columna.

Indicadores Visuales: Se debe mostrar un icono (flecha arriba/abajo) en la columna que tiene el foco de ordenamiento actual.

Formulario de Registro y Edición

Validaciones de Input: Los campos deben restringir el tipo de dato en tiempo real:Nombre: Solo caracteres alfabéticos y espacios.

- Código/Código Telefónico: Solo caracteres alfanuméricos (según estándar ISO) o numéricos según corresponda, sin espacios ni caracteres especiales innecesarios.
- Banderas: Validación de formato de imagen (PNG/JPG) y dimensiones recomendadas (61x36 px).
- Feedback de Error: Si un dato no cumple con el formato, el input debe resaltar en rojo y mostrar un mensaje de ayuda (micro-copy) bajo el campo.

Inactivación en Cascada (Descendente)

Acción: Si un nodo superior (País o Estado) se inactiva, todos sus hijos deben inactivarse automáticamente.

Regla de UX (Confirmación): Antes de ejecutar la acción, el sistema debe desplegar un modal de advertencia indicando el impacto. Ejemplo: "¿Está seguro de inactivar este País? Esta acción desactivará automáticamente todos los estados y ciudades asociados, mostrar alerta de advertencia: "Esta acción inactivará [N] estados y [M] ciudades vinculadas. ¿Desea continuar?"."

Bloqueo de Activación Independiente (Ascendente)

Regla: El sistema no debe permitir activar un registro si su "padre" está inactivo.

Comportamiento: * Si el usuario intenta activar una Ciudad, el sistema debe verificar el estado del Estado/Provincia. Si está inactivo, el switch de la ciudad debe bloquearse o mostrar un error: "No se puede activar la ciudad porque el Estado/Provincia se encuentra inactivo".

Lo mismo aplica para activar un Estado si el País está inactivo.

Excepción: El sistema debe ofrecer "activación en cadena" (activar el padre automáticamente), pero por seguridad se lanza incialmente el bloqueo con mensaje informativo.

🧱 Backend

Lógica de Estado en Cascada (Integridad de Datos)

Para mantener la coherencia en la cobertura del negocio, se debe implementar una desactivación jerárquica:

Inactivación de País: Al cambiar el estado de un País a "Inactivo", el sistema debe establecer automáticamente como "Inactivos" todos los Estados/Provincias y Ciudades vinculados a dicho país.

Inactivación de Estado/Provincia: Al cambiar el estado de un Estado a "Inactivo", se deben inactivar automáticamente todas las Ciudades vinculadas a este.

Regla de UX (Confirmación): Antes de ejecutar la acción, el sistema debe desplegar un modal de advertencia indicando el impacto. Ejemplo: "¿Está seguro de inactivar este País? Esta acción desactivará automáticamente todos los estados y ciudades asociados."

Configuración Inicial (Seed Data)

Restricción de Cobertura: Por defecto, solo el país Colombia debe permanecer con estado "Activo". Todos los demás países en la base de datos deben inicializarse como "Inactivos".

Unicidad de Datos

Validación: El sistema debe impedir la creación de duplicados bajo el mismo nodo.

No permitir dos países con el mismo Código ISO.

No permitir dos ciudades con el mismo nombre dentro del mismo Estado. (Sí se permite "Guadalupe" en diferentes estados, pero no dos veces en el mismo).

Regla de "País por Defecto" (Plataforma Siempre Activa)

Para garantizar la continuidad operativa del negocio, el sistema debe gestionar un país base obligatorio:

Existencia Mínima: El sistema debe impedir que la lista de países activos quede vacía. Siempre debe existir al menos un (1) país con estado "Activo".

Protección de Inactivación: Si solo queda un país activo (ej. Colombia), el sistema debe deshabilitar la opción de "Inactivar" para ese registro o lanzar un error impidiendo la acción: "No es posible inactivar el país; la plataforma requiere al menos una ubicación activa para operar".

Asignación por Defecto: Se debe definir una bandera (flag) en la base de datos is_default. Este país será el seleccionado automáticamente en formularios de registro o checkout para mejorar la conversión.

🧪 Criterios de aceptación

1. Gestión de Jerarquía y Cascada (Descendente)

AC 1.1: Al cambiar el estado de un País a "Inactivo", todos sus Estados/Provincias y Ciudades vinculados deben cambiar automáticamente a "Inactivo" en la base de datos y la interfaz.

AC 1.2: Al cambiar el estado de un Estado/Provincia a "Inactivo", todas sus Ciudades vinculadas deben cambiar automáticamente a "Inactivo".

AC 1.3: El sistema debe mostrar un modal de confirmación antes de ejecutar la inactivación, informando la cantidad de registros hijos que serán afectados.

2. Restricción de Activación (Ascendente)

AC 2.1: El sistema debe impedir la activación de una Ciudad si su Estado o País padre están inactivos. El control de estado (switch/checkbox) debe aparecer deshabilitado o disparar un mensaje de error explicativo.

AC 2.2: El sistema debe impedir la activación de un Estado si su País padre está inactivo.

AC 2.3: Si el usuario intenta activar un registro con padres inactivos, debe recibir un mensaje de error: "No se puede activar el registro: El nivel superior [Nombre] está inactivo".

3. País por Defecto y Disponibilidad Mínima

AC 3.1: El sistema debe marcar un país como is_default (inicialmente Colombia). Este país no puede ser inactivado bajo ninguna circunstancia mientras sea el único activo.

AC 3.2: Si el usuario intenta inactivar el último país activo de la lista, el sistema debe bloquear la acción y mostrar el mensaje: "Operación denegada: La plataforma requiere al menos un país activo para operar".

AC 3.3: Solo puede existir un (1) país marcado como "Por defecto" simultáneamente. Al marcar uno nuevo, el anterior pierde la propiedad automáticamente.

4. Validaciones de Formulario e Integridad

AC 4.1: El sistema no debe permitir la creación de un País con un código ISO o Nombre que ya exista (Case Insensitive).

AC 4.2: El sistema no debe permitir crear dos Ciudades con el mismo nombre dentro del mismo Estado.

AC 4.3: El input de Código Telefónico debe validar que solo se ingresen números y el símbolo "+".

AC 4.4: El campo de carga de Bandera debe rechazar archivos que no sean .jpg o .png y que superen los 200KB.

5. Usabilidad de la Grilla (Tablas)

AC 5.1: Al hacer clic en el nombre de cualquier columna, la lista debe reordenarse (A-Z / Z-A / 0-9) sin recargar la página completa.

AC 5.2: El buscador de "Búsqueda Rápida" debe filtrar los resultados visibles en la tabla de forma instantánea según el texto ingresado.

AC 5.3: Si un registro está "Inactivo" debido a la cascada de un padre, debe mostrar un indicador visual o tooltip que explique la razón al pasar el mouse.

Ejemplo de "User Story" :

COMO Administrador del sistema, QUIERO que la activación de ubicaciones sea jerárquica, PARA evitar que existan ciudades activas en países donde el negocio no tiene operación.

