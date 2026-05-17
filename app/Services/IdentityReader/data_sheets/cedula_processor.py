from doctr.io import DocumentFile
from doctr.models import ocr_predictor
from id_attributes_enum import IdAttributesEnum
from api_response import success_response, error_response, ErrorCodesEnum, HttpStatusCodesEnum
from helpers import is_similar_id_label
from new_id import proccess_new_id
from yellow_id import process_yellow_id, process_yellow_id_back, _extract_number

class CedulaProcessor:
    # ─────────────────────────────────────────────
    #  INICIALIZACIÓN DEL MODELO (una sola vez)
    # ─────────────────────────────────────────────
    model = ocr_predictor(det_arch='db_resnet50', reco_arch='crnn_vgg16_bn', pretrained=True)

    # ─────────────────────────────────────────────
    #  DETECCIÓN AUTOMÁTICA DEL TIPO DE CÉDULA
    # ─────────────────────────────────────────────
    @staticmethod
    def detect_id_type(ocr_result) -> str:
        """
        Recorre todas las líneas del resultado OCR buscando:
        - la etiqueta 'NUIP' -> retorna 'new'
        - el número de cédula en cédula vieja -> si no lo encuentra, lanza error de imagen ilegible
        """
        number_found = False  # Flag para cédula vieja

        for page in ocr_result.pages:
            for block in page.blocks:
                for line in block.lines:
                    line_text = " ".join(w.value for w in line.words).upper()

                    # Caso cédula nueva
                    if IdAttributesEnum.NUIP.value in line_text or is_similar_id_label(line_text, IdAttributesEnum.NUIP.value):
                        return "new"

                    # Caso cédula vieja: intentamos extraer número
                    if _extract_number(line) is not None:
                        number_found = True

        # Si encontramos número -> cédula vieja
        if number_found:
            return "yellow"

        # Si no encontramos ni NUIP ni número -> imagen ilegible
        return error_response(
            status=HttpStatusCodesEnum.UNPROCESSABLE_ENTITY.value,
            error_code=ErrorCodesEnum.IMAGE_NOT_READABLE.value,
        )

    # ─────────────────────────────────────────────
    #  VALIDACIÓN DE CAMPOS MÍNIMOS
    # ─────────────────────────────────────────────
    @staticmethod
    def validate_minimum_data(data: dict, id_type: str) -> tuple[bool, list[str], bool]:
        """
        Valida que los campos mínimos requeridos estén presentes.
        Retorna una tupla: (es_valido, lista_de_campos_faltantes, imagen_ilegible)
        """
        if id_type == 'new':
            required_fields = ['NUIP', 'Apellidos', 'Nombres', 'Fecha_Nacimiento']
        else:
            required_fields = ['Numero', 'Apellidos', 'Nombres', 'Fecha_Nacimiento']

        missing_fields = [
            field for field in required_fields
            if not data.get(field, "").strip()
        ]

        is_valid_id = not missing_fields
        is_image_not_readable = len(missing_fields) == len(required_fields)

        return is_valid_id, missing_fields, is_image_not_readable

    @staticmethod
    def detect_citizenship(ocr_result) -> bool:
        """
        Retorna True si encuentra la palabra CIUDADANIA en cualquier línea del OCR.
        """
        for page in ocr_result.pages:
            for block in page.blocks:
                for line in block.lines:
                    line_text = " ".join(w.value for w in line.words).upper()
                    if IdAttributesEnum.CIUDADANIA.value in line_text or \
                    any(is_similar_id_label(w.value, IdAttributesEnum.CIUDADANIA.value) for w in line.words):
                        return True
        return False

    # ─────────────────────────────────────────────
    #  PROCESAMIENTO DEL REVERSO (cédula vieja)
    # ─────────────────────────────────────────────
    @classmethod
    def _process_yellow_back(cls, id_back_image: str, front_data: dict) -> tuple[dict | None, dict | None]:
        """
        Procesa el reverso de la cédula vieja.
        Retorna (data_combinada, error) — uno de los dos siempre es None.
        """
        if id_back_image is None:
            return None, error_response(
                status=HttpStatusCodesEnum.UNPROCESSABLE_ENTITY.value,
                error_code=ErrorCodesEnum.BACK_ID_NOT_FOUND.value,
            )

        id_back_doctr = DocumentFile.from_images(id_back_image)
        id_back_image_result = cls.model(id_back_doctr)
        yellow_id_back_data = process_yellow_id_back(id_back_image_result)
        return {**yellow_id_back_data, **front_data}, None

    # ─────────────────────────────────────────────
    #  VALIDACIÓN POST-EXTRACCIÓN
    # ─────────────────────────────────────────────
    @classmethod
    def _validate_and_check_citizenship(cls, data: dict, id_type: str, ocr_front) -> dict | None:
        """
        Valida campos mínimos y ciudadanía.
        Retorna un error_response si algo falla, o None si todo es válido.
        """
        is_valid_id, missing_fields, is_image_not_readable = cls.validate_minimum_data(data, id_type)

        if not is_valid_id:
            error_code = (
                ErrorCodesEnum.IMAGE_NOT_READABLE.value
                if is_image_not_readable
                else ErrorCodesEnum.NOT_MINIMUM_REQUIRED_FIELDS_FOUND.value
            )
            return error_response(
                status=HttpStatusCodesEnum.UNPROCESSABLE_ENTITY.value,
                error_code=error_code,
                data=missing_fields,
            )

        if not cls.detect_citizenship(ocr_front):
            return error_response(
                status=HttpStatusCodesEnum.UNPROCESSABLE_ENTITY.value,
                error_code=ErrorCodesEnum.NOT_CITIZEN_ID_FOUND.value,
            )

        return None

    # ─────────────────────────────────────────────
    #  MAPEO AL ESQUEMA DE SALIDA
    # ─────────────────────────────────────────────
    @staticmethod
    def _build_result(data: dict) -> dict:
        return {
            "is_citizen":            1,
            "identification_number": data.get("NUIP") or data.get("Numero") or None,
            "name":                  data.get("Nombres") or None,
            "last_name":             data.get("Apellidos") or None,
            "birth_date":            data.get("Fecha_Nacimiento") or None,
            "place_of_birth":        data.get("Lugar_Nacimiento") or None,
            "sex":                   data.get("Sexo") or None,
            "issue_date":            data.get("Fecha_Expedicion") or None,
            "issue_place":           data.get("Lugar_Expedicion") or None,
            "expiration_date":       data.get("Fecha_Expiracion") or None,
        }

    # ─────────────────────────────────────────────
    #  PUNTO DE ENTRADA UNIFICADO
    # ─────────────────────────────────────────────
    @classmethod
    def process_id(cls, id_front_image: str, id_back_image: str = None) -> dict | None:
        try:
            id_front_doctr = DocumentFile.from_images(id_front_image)
            ocr_front = cls.model(id_front_doctr)
            id_type = cls.detect_id_type(ocr_front)

            data = proccess_new_id(ocr_front) if id_type == 'new' \
                else process_yellow_id(ocr_front)

            if id_type == 'yellow':
                data, error = cls._process_yellow_back(id_back_image, data)
                if error:
                    return error

            validation_error = cls._validate_and_check_citizenship(data, id_type, ocr_front)
            if validation_error:
                return validation_error

            return success_response(data=cls._build_result(data))

        except Exception as e:
            return error_response(
                error_code=ErrorCodesEnum.INTERNAL_SERVER_ERROR.value,
                data={"message": f"There was an internal error: {str(e)[:30]}"}
            )