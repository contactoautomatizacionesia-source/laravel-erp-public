from rapidfuzz import fuzz
from id_attributes_enum import IdAttributesEnum

YELLOW_ID_LABELS = {
    IdAttributesEnum.APELLIDOS.value,
    IdAttributesEnum.NOMBRES.value,
    IdAttributesEnum.NUMERO.value,
    IdAttributesEnum.CEDULA.value,
}

MONTHS = {
    'ENE': '01', 'FEB': '02', 'MAR': '03', 'ABR': '04',
    'MAY': '05', 'JUN': '06', 'JUL': '07', 'AGO': '08',
    'SEP': '09', 'OCT': '10', 'NOV': '11', 'DIC': '12',
}

THRESHOLD = 80

def is_similar_id_label(texto: str, etiqueta: str) -> bool:
    return fuzz.ratio(texto.strip().upper(), etiqueta.upper()) >= THRESHOLD

def is_known_id_label(id_label: str) -> bool:
    return any(fuzz.ratio(id_label.upper(), e) >= THRESHOLD for e in YELLOW_ID_LABELS)

def standardize_date(palabras: list) -> str:
    """
    Soporta dos formatos:
      - Lista de tokens: ['07', 'NOV', '2007']  → '07/11/2007'
      - Token con guiones: ['03-MAY-2004']       → '03/05/2004'
    """
    if not palabras:
        return ""

    # Formato con guiones en un solo token: '03-MAY-2004'
    if len(palabras) == 1 and '-' in palabras[0]:
        partes = palabras[0].split('-')
    else:
        partes = [p.strip().rstrip(',').rstrip('.').upper() for p in palabras[:3]]

    if len(partes) < 3:
        return " ".join(partes)

    day, month_str, year = partes
    clean_month_str = ''.join(c for c in month_str if c.isalpha())
    month = MONTHS.get(clean_month_str[:3], clean_month_str)
    day = day.zfill(2)
    return f"{day}/{month}/{year}"