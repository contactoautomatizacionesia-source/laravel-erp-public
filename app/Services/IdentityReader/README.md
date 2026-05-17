# IdentityReader — Componente de OCR para Cédulas Colombianas

Servicio que extrae información estructurada de cédulas de ciudadanía colombianas (nueva y amarilla) mediante OCR, integrando un proceso Python con `docTR` dentro de una aplicación Laravel.

---

## Estructura del componente

```
Services/
└── IdentityReader/
    ├── config/
    │   └── data_sheets.php         # Configuración: Python bin, runner script, timeout, max image size
    ├── data_sheets/
    │   ├── api_response.py         # Helpers de respuesta estructurada
    │   ├── cedula_processor.py     # Orquestador principal del OCR
    │   ├── helpers.py              # Utilidades (fuzzy matching, fechas, etc.)
    │   ├── id_attributes_enum.py   # Enum de etiquetas de la cédula
    │   ├── new_id.py               # Extracción para cédula nueva (NUIP)
    │   ├── ocr_runner.py           # Entry point CLI del proceso Python
    │   ├── requirements.txt        # Dependencias Python
    │   └── yellow_id.py            # Extracción para cédula amarilla (frente + reverso)
    ├── IdentityReaderService.php   # Servicio Laravel que invoca el proceso Python
    └── README.md
```

---

## Requisitos

- **PHP** >= 8.1 con Laravel
- **Python** >= 3.11

---

## Nota para desarrolladores en Windows

Al instalar las dependencias del venv (PyTorch, docTR), Windows Defender puede bloquear archivos `.pyd` (módulos C compilados nativos) mostrando el mensaje **"Parte de esta aplicación se ha bloqueado"**. Esto es un falso positivo — las librerías son legítimas.

**Solución permanente:** agregar el directorio `venv/` a las exclusiones de Windows Defender:

1. Abrir **Seguridad de Windows** → **Protección contra virus y amenazas**
2. → **Administrar configuración** → **Exclusiones** → **Agregar exclusión** → **Carpeta**
3. Agregar la ruta completa al `venv/` del proyecto, por ejemplo:
   ```
   C:\laragon\www\<tu-proyecto>\app\Services\IdentityReader\data_sheets\venv
   ```

Esto solo afecta al entorno de desarrollo local — no aplica en servidores Linux.

---

## Instalación del entorno Python

El entorno virtual (`venv`) **no es portable entre máquinas**: contiene binarios compilados específicos para cada sistema operativo y versión de Python. Si el servicio llega a un proyecto nuevo, a una máquina nueva o a un servidor, el `venv/` debe crearse localmente antes de usarlo — aunque ya venga una carpeta `venv/` incluida, no servirá y debe reemplazarse.

**Pasos:**

Si ya existe una carpeta `venv/`, eliminarla primero:

```bash
# Linux / macOS
rm -rf data_sheets/venv

# Windows
rmdir /s /q data_sheets\venv
```

Luego crear el entorno e instalar las dependencias desde `data_sheets/`:

```bash
# Linux / macOS
python3 -m venv venv

venv/bin/pip install -r requirements.txt 
o
./venv/Scripts/python.exe -m pip install -r requirements.txt

# Windows
python -m venv venv
venv\Scripts\pip install -r requirements.txt
```

> La primera ejecución del servicio descargará los modelos OCR en caché local. Las siguientes serán más rápidas.

> El directorio `venv/` debe estar en el `.gitignore` del proyecto para evitar que se suba al repositorio.

El servicio detecta automáticamente el venv en esa ubicación. Si se prefiere usar un intérprete distinto, configurarlo mediante variables de entorno (ver sección de configuración).

---

## Configuración

El archivo `config/data_sheets.php` expone los parámetros del servicio, todos sobreescribibles en el `.env` del proyecto Laravel:

| Variable de entorno | Descripción | Valor por defecto |
|---|---|---|
| `DATA_SHEETS_PYTHON_BIN` | Ruta absoluta al binario Python. Vacío = auto-detect (venv → `python3` del sistema) | `''` |
| `DATA_SHEETS_RUNNER_SCRIPT` | Ruta absoluta a `ocr_runner.py`. Vacío = ruta relativa al paquete | `''` |
| `DATA_SHEETS_TIMEOUT` | Tiempo máximo en segundos para el proceso OCR | `120` |
| `DATA_SHEETS_MAX_IMAGE_SIZE` | Tamaño máximo permitido por imagen en bytes | `10485760` (10 MB) |

Ejemplo de `.env`:

```dotenv
DATA_SHEETS_PYTHON_BIN=/home/deploy/.pyenv/versions/3.11.0/bin/python
DATA_SHEETS_TIMEOUT=120
DATA_SHEETS_MAX_IMAGE_SIZE=5242880
```

---

## Uso desde Laravel

```php
use App\Services\IdentityReader\IdentityReaderService;

$service = new IdentityReaderService();

// Con archivos UploadedFile (request de formulario)
$result = $service->processImage(
    frontalFile: $request->file('frente'),
    reversoFile: $request->file('reverso'),   // opcional para cédula nueva
);

// Con ruta relativa al storage (archivo ya guardado previamente)
$result = $service->processImage(
    frontalFilePath: 'temp/cedula_frente.jpg',
    reversoFile: $request->file('reverso'),
);
```

---

## Respuestas

Todos los métodos devuelven un array con la siguiente estructura:

### Éxito (`status: 200`)

```json
{
    "status": 200,
    "error_code": "",
    "data": {
        "is_citizen": 1,
        "identification_number": "1234567890",
        "name": "JUAN",
        "last_name": "PÉREZ GÓMEZ",
        "birth_date": "1990-05-14",
        "place_of_birth": "BOGOTÁ",
        "sex": "M",
        "issue_date": "2010-03-01",
        "issue_place": "BOGOTÁ",
        "expiration_date": null
    }
}
```

> `expiration_date` puede ser `null` en cédulas amarillas.

### Error

```json
{
    "status": 400,
    "error_code": "BACK_ID_NOT_FOUND",
    "data": null
}
```

---

## Códigos de error

| Código | Status | Descripción |
|---|---|---|
| `NOT_IMAGES_FOUND` | 400 | No se proporcionó ninguna imagen |
| `IMAGE_TOO_LARGE` | 400 | La imagen supera el tamaño máximo permitido |
| `IMAGE_INVALID_FORMAT` | 400 | El formato de la imagen no es jpg o png |
| `IMAGE_INVALID_PATH` | 400 | La ruta del frente guardado apunta fuera de `storage/app/temp/` (protección path traversal) |
| `IMAGE_NOT_FOUND` | 400 | La imagen principal no existe en la ruta indicada |
| `SECOND_IMAGE_NOT_FOUND` | 400 | La imagen del reverso no existe en la ruta indicada |
| `BACK_ID_NOT_FOUND` | 422 | La cédula es amarilla y falta la imagen del reverso. El path del frente se conserva en `savedFrente` |
| `NOT_CITIZEN_ID_FOUND` | 422 | El documento no es una cédula de ciudadanía |
| `NOT_MINIMUM_REQUIRED_FIELDS_FOUND` | 422 | Faltan los campos mínimos requeridos (devuelve lista de campos ausentes en `data`) |
| `IMAGE_NOT_READABLE` | 422 | La imagen es ilegible o no contiene texto reconocible |
| `RUNNER_NOT_FOUND` | 500 | No se encontró el script `ocr_runner.py` |
| `PROCESS_FAILED` | 500 | El proceso Python terminó sin output |
| `INVALID_JSON` | 500 | El proceso Python devolvió output inválido como JSON |
| `RUNNER_EXCEPTION` | 500 | Excepción no controlada en el entry point `ocr_runner.py` |
| `UNEXPECTED_ERROR` | 500 | Excepción no controlada en el servicio PHP |

> **Nota sobre `BACK_ID_NOT_FOUND`:** cuando ocurre este error el servicio **no elimina** la imagen del frente del storage. El path relativo queda en `resultado['savedFrente']` para que el controlador pueda reutilizarlo al recibir el reverso en un segundo paso.

---

## Flujo interno

```
processImage()                  [IdentityReaderService.php]
  │
  ├─ validateUploadedFiles()    → valida formato y tamaño
  ├─ Guarda imágenes en storage/app/temp/
  │
  └─ executeOcr()
       │
       └─ Symfony\Process → python ocr_runner.py <frente> [reverso]
            │
            └─ CedulaProcessor.process_id()
                 │
                 ├─ detect_id_type()   → "new" | "yellow"
                 │
                 ├─ new:    process_new_id()
                 │   yellow: process_yellow_id() + _process_yellow_back()
                 │
                 ├─ validate_minimum_data()
                 ├─ detect_citizenship()
                 │
                 └─ _build_result()   → JSON → stdout → PHP array
```

---

## Tipos de cédula soportados

| Tipo | Detección | Imágenes requeridas |
|---|---|---|
| **Cédula nueva** | Presencia de etiqueta `NUIP` | Solo frente |
| **Cédula amarilla** | Ausencia de `NUIP` + número de cédula detectable | Frente **y** reverso |

---

## Notas de implementación

- El modelo OCR (`db_resnet50` + `crnn_vgg16_bn`) se instancia **una sola vez** como atributo de clase en `CedulaProcessor` para evitar recargas entre llamadas.
- Las imágenes temporales se eliminan siempre al finalizar el proceso, excepto el frente en el caso `BACK_ID_NOT_FOUND`.
- El proceso Python hereda las variables de entorno del sistema con `PYTHONHASHSEED=0` y `PYTHONUTF8=1` para garantizar salidas deterministas y encoding correcto.
- La primera ejecución puede tardar más de lo normal mientras se descargan los pesos del modelo OCR en caché local.

## Decisiones arquitecturales

### Modelo OCR como atributo de clase (subprocess vs. worker persistente)

El modelo OCR (`db_resnet50` + `crnn_vgg16_bn`) se declara como atributo de clase en `CedulaProcessor`, lo que garantiza que se inicializa **una sola vez por proceso Python**, no una vez por llamada a `process_id()`.

El trade-off conocido y aceptado es que **cada request de PHP lanza un nuevo proceso Python**, lo que implica una carga del modelo desde disco en cada petición (~2-5s). La alternativa sería un worker persistente (FastAPI, etc.) que mantenga el modelo en memoria entre requests.

Esta decisión se tomó deliberadamente por las siguientes razones:

- **Caso de uso**: el servicio se usa en flujos de registro, no en endpoints de alto volumen ni en tiempo real. La latencia de carga es aceptable.
- **Simplicidad de despliegue**: no requiere infraestructura adicional — solo Python instalado. Sin procesos permanentes que monitorear, reiniciar o proteger.
- **Aislamiento**: si el proceso Python falla, Laravel no se ve afectado.
- **Portabilidad**: cualquier proyecto Laravel puede integrar el servicio sin dependencias de red ni servicios externos.

Si el volumen de uso crece significativamente, migrar a un worker persistente es la evolución natural, pero no es necesario para el caso de uso actual.

---

## Seguridad

- **Path traversal**: cuando se recibe `frontalFilePath` (ruta de un frente ya guardado en storage), el servicio valida con `realpath()` que la ruta resuelta esté dentro de `storage/app/temp/`. Cualquier intento de salir del directorio permitido (ej. `../../.env`) devuelve `IMAGE_INVALID_PATH`. No usar `str_replace` para esta validación — es bypasseable con variantes como `....//`.
- **Formato y tamaño**: validados en PHP antes de llegar al proceso Python, evitando que archivos maliciosos o excesivamente grandes sean procesados.
- **Variables de entorno**: el proceso Python recibe únicamente las variables necesarias para su ejecución. No hereda el entorno completo del sistema — evita exponer credenciales de base de datos, API keys u otras variables sensibles del proceso Laravel.

  En Linux/macOS se pasan solo: `PATH`, `PYTHONHASHSEED`, `PYTHONIOENCODING`, `PYTHONUTF8`.

  En Windows se agregan adicionalmente las siguientes, requeridas por `asyncio` (Winsock) y PyTorch (directorio de caché). Todas se leen automáticamente del entorno del sistema con `getenv()` — no requieren configuración manual:

  | Variable | Propósito | Dónde encontrar el valor |
  |---|---|---|
  | `SYSTEMROOT` | Inicialización de Winsock (`asyncio`) | Siempre `C:\Windows` en Windows estándar |
  | `WINDIR` | Ídem | Siempre `C:\Windows` en Windows estándar |
  | `USERNAME` | Directorio de caché de PyTorch | Nombre del usuario actual del sistema |
  | `USERPROFILE` | Directorio home del usuario | `C:\Users\<usuario>` |
  | `APPDATA` | Datos de aplicación roaming | `C:\Users\<usuario>\AppData\Roaming` |
  | `LOCALAPPDATA` | Datos de aplicación local | `C:\Users\<usuario>\AppData\Local` |
  | `TEMP` | Archivos temporales | `C:\Users\<usuario>\AppData\Local\Temp` |
  | `TMP` | Ídem | `C:\Users\<usuario>\AppData\Local\Temp` |

  Estas variables existen en cualquier instalación Windows estándar. Solo serían un problema en entornos muy restringidos sin perfil de usuario configurado.

---

## Calidad mínima de imagen recomendada

El rendimiento del OCR depende directamente de la calidad de las imágenes. `CedulaProcessor` recorre horizontalmente bloques, líneas y palabras del resultado OCR — si el modelo no logra segmentar correctamente el texto, los campos no se extraen.

**Condiciones que degradan el reconocimiento:**
- Imagen borrosa o con movimiento (motion blur)
- Iluminación desigual, reflejos o brillos sobre el documento
- Cédula parcialmente cubierta u obstruida
- La cédula no está completamente horizontal en la foto

Se recomienda capturar la imagen con buena iluminación difusa y la cédula en posición horizontal.
