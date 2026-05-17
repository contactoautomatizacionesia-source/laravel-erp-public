from id_attributes_enum import IdAttributesEnum
from helpers import is_similar_id_label, is_known_id_label, standardize_date

# ─────────────────────────────────────────────
#  CÉDULA VIEJA
# ─────────────────────────────────────────────
def process_yellow_id(ocr_result) -> dict:
    data_final = {}

    for page in ocr_result.pages:
        all_lines = [
            line
            for block in page.blocks
            for line in block.lines
        ]

        for i, line in enumerate(all_lines):
            try:
                if (extracted_number := _extract_number(line)) is not None:
                    data_final['Numero'] = extracted_number

                elif (extracted_last_names := _extract_last_names(all_lines, i)) is not None:
                    data_final['Apellidos'] = extracted_last_names

                elif (extracted_name := _extract_names(all_lines, i)) is not None:
                    data_final['Nombres'] = extracted_name

            except IndexError:
                continue

    return data_final

def process_yellow_id_back(ocr_result) -> dict:
    data_final = {}
 
    for page in ocr_result.pages:
        all_lines = [
            line
            for block in page.blocks
            for line in block.lines
        ]
 
        for i, line in enumerate(all_lines):
            try:
                _try_extract_birth_date(line, data_final)
                _try_extract_birth_place(all_lines, i, line, data_final)
                _try_extract_sex(all_lines, i, line, data_final)
                _try_extract_expedition(all_lines, i, line, data_final)
            except IndexError:
                continue
 
    return data_final
 
def _line_text(line) -> str:
    return " ".join(w.value for w in line.words).upper()
 
 
def _try_extract_birth_date(line, data_final: dict) -> None:
    """Fecha de nacimiento: aparece en la misma línea que la etiqueta."""
    text = _line_text(line)
    is_birth_date_label = (
        IdAttributesEnum.NACIMIENTO.value in text
        and IdAttributesEnum.FECHA.value in text
        and IdAttributesEnum.LUGAR.value not in text
        and len(line.words) > 1
    )
    if is_birth_date_label:
        fecha_token = line.words[-1].value
        data_final['Fecha_Nacimiento'] = standardize_date([fecha_token])
 
 
def _try_extract_birth_place(all_lines: list, index: int, line, data_final: dict) -> None:
    """Lugar de nacimiento: el valor está en líneas ANTERIORES a la etiqueta."""
    text = _line_text(line)
    if not (IdAttributesEnum.NACIMIENTO.value in text and IdAttributesEnum.LUGAR.value in text):
        return
 
    data_final['Lugar_Nacimiento'] = _find_birth_place(all_lines, index)
 
 
def _find_birth_place(all_lines: list, desde_index: int) -> str:
    """Recorre hacia atrás buscando el lugar de nacimiento."""
    for offset in range(1, 4):
        candidate_words = all_lines[desde_index - offset].words
        candidate_text = " ".join(w.value for w in candidate_words).upper()
 
        if _is_parenthesis_only_line(candidate_words):
            continue
 
        primera = candidate_text.split()[0] if candidate_text.split() else ""
        if is_known_id_label(primera) or IdAttributesEnum.FECHA.value in candidate_text:
            break
 
        lugar_partes = [
            w.value for w in candidate_words
            if not w.value.startswith('(') and not w.value.endswith(')')
        ]
        if lugar_partes:
            return " ".join(lugar_partes).upper()
 
    return ""
 
 
def _is_parenthesis_only_line(words) -> bool:
    return all(w.value.startswith('(') or w.value.endswith(')') for w in words)
 
 
def _try_extract_sex(all_lines: list, index: int, line, data_final: dict) -> None:
    """Sexo: el valor (M/F) está en una línea ANTERIOR a la etiqueta."""
    text = _line_text(line)
    if not is_similar_id_label(text, IdAttributesEnum.SEXO.value):
        return
 
    for offset in range(1, 4):
        candidate_words = all_lines[index - offset].words
        candidate_text = " ".join(w.value for w in candidate_words).upper().strip()
        primera = candidate_text.split()[0] if candidate_text.split() else ""
 
        is_valid_sex_token = (
            len(candidate_text) <= 5
            and not is_known_id_label(primera)
            and candidate_text in ('M', 'F')
        )
        if is_valid_sex_token:
            data_final['Sexo'] = candidate_text
            break
 
 
def _try_extract_expedition(all_lines: list, index: int, line, data_final: dict) -> None:
    """Fecha y lugar de expedición: los valores están en la línea ANTERIOR a la etiqueta."""
    text = _line_text(line)
    if IdAttributesEnum.EXPEDICION.value not in text:
        return
 
    candidate_words = all_lines[index - 1].words
    values = [w.value for w in candidate_words]
 
    data_final['Fecha_Expedicion'] = standardize_date([values[0]])
    parts_location = [v.strip().rstrip(',') for v in values[1:]]
    data_final['Lugar_Expedicion'] = " ".join(parts_location).upper()
 

def _extract_number(line) -> str | None:
    """Extrae el número de cédula si la línea contiene la etiqueta NUMERO."""
    text = " ".join(w.value for w in line.words).upper()

    is_number_label = (
        IdAttributesEnum.NUMERO.value in text
        or is_similar_id_label(text.split()[0], IdAttributesEnum.NUMERO.value)
    )

    if is_number_label and len(line.words) >= 2:
        return line.words[-1].value

    return None

def _find_previous_value(all_lines: list, desde_index: int) -> str:
    """
    Recorre hacia atrás desde `desde_index` buscando el primer candidato
    válido (no etiqueta, no dígitos, no muy corto).
    Reutilizable por apellidos y nombres.
    """
    for offset in range(1, min(4, desde_index + 1)):
        candidate = " ".join(
            w.value for w in all_lines[desde_index - offset].words
        ).upper()
        primera = candidate.split()[0] if candidate.split() else ""

        if (not is_known_id_label(primera)
                and not candidate.replace(" ", "").replace(".", "").isdigit()
                and len(candidate.strip()) > 3):
            return candidate

    return ""

def _extract_last_names(all_lines: list, index: int) -> str | None:
    """Extrae apellidos buscando hacia atrás desde la línea de la etiqueta."""
    text = " ".join(w.value for w in all_lines[index].words).upper()

    if not is_similar_id_label(text, IdAttributesEnum.APELLIDOS.value):
        return None

    return _find_previous_value(all_lines, index) or None


def _extract_names(all_lines: list, index: int) -> str | None:
    """Extrae nombres buscando hacia atrás desde la línea de la etiqueta."""
    text = " ".join(w.value for w in all_lines[index].words).upper()

    if not is_similar_id_label(text, IdAttributesEnum.NOMBRES.value):
        return None

    return _find_previous_value(all_lines, index) or None
