from enum import Enum

class HttpStatusCodesEnum(Enum):
    OK = 200
    CREATED = 201
    BAD_REQUEST = 400
    UNAUTHORIZED = 401
    FORBIDDEN = 403
    NOT_FOUND = 404
    UNPROCESSABLE_ENTITY = 422
    INTERNAL_SERVER_ERROR = 500

class ErrorCodesEnum(Enum):
    BACK_ID_NOT_FOUND = "BACK_ID_NOT_FOUND"
    NOT_CITIZEN_ID_FOUND = "NOT_CITIZEN_ID_FOUND"
    NOT_MINIMUM_REQUIRED_FIELDS_FOUND = "NOT_MINIMUM_REQUIRED_FIELDS_FOUND"
    IMAGE_NOT_READABLE = "IMAGE_NOT_READABLE"
    INTERNAL_SERVER_ERROR = "INTERNAL_SERVER_ERROR"

def response_builder(status=HttpStatusCodesEnum.OK.value, error_code="", data=None):
    """
    Retorna la estructura unificada solicitada.
    """
    return {
        "status": status,
        "error_code": error_code,
        "data": data
    }

def success_response(data):
    """
    Retorna la estructura de respuesta para un caso de éxito.
    """
    return response_builder(
        status=HttpStatusCodesEnum.OK.value,
        error_code="",
        data=data
    )

def error_response(error_code, status=HttpStatusCodesEnum.INTERNAL_SERVER_ERROR.value, data=None):
    """
    Retorna la estructura de respuesta para un caso de error.
    """
    return response_builder(
        status=status,
        error_code=error_code,
        data=data
    )