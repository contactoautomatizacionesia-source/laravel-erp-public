from id_attributes_enum import IdAttributesEnum
from helpers import is_similar_id_label, is_known_id_label, standardize_date

def _is_label_or_short(linea_words) -> bool:
    """Guard compartido: descarta candidatos que sean etiquetas o textos muy cortos."""
    text = " ".join(w.value for w in linea_words).upper()
    primera = text.split()[0] if text.split() else ""
    return is_known_id_label(primera) or len(text.strip()) <= 5

def _line_next_text(all_lines: list, index: int) -> str:
    """Devuelve el text concatenado de la línea index+1."""
    return " ".join(w.value for w in all_lines[index + 1].words)

def _contains(text: str, attribute: IdAttributesEnum) -> bool:
    """True si el attribute está en el text o es fuzzy-similar a él."""
    return (
        attribute.value in text
        or is_similar_id_label(text, attribute.value)
    )

def _extract_nuip(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()
    if not _contains(text, IdAttributesEnum.NUIP):
        return None
    if len(line.words) > 1:
        return line.words[-1].value
    return _line_next_text(all_lines, index)

def _extract_last_names(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()
    if not _contains(text, IdAttributesEnum.APELLIDOS):
        return None
    return _line_next_text(all_lines, index)

def _extract_names(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()
    if not _contains(text, IdAttributesEnum.NOMBRES):
        return None
    return _line_next_text(all_lines, index)


def _extract_height_and_sex(line, all_lines: list, index: int) -> dict | None:
    text = " ".join(w.value for w in line.words).upper()
    has_height = _contains(text, IdAttributesEnum.ESTATURA)
    has_sex = _contains(text, IdAttributesEnum.SEXO)

    if not (has_height and has_sex):
        return None

    words_values = all_lines[index + 2].words
    if len(words_values) < 2:
        return None

    return {
        'Estatura': words_values[0].value,
        'Sexo':     words_values[1].value,
    }


def _extract_birth_date(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()

    es_fecha_nac = (
        _contains(text, IdAttributesEnum.FECHA)
        and _contains(text, IdAttributesEnum.NACIMIENTO)
        and IdAttributesEnum.LUGAR.value not in text
        and IdAttributesEnum.EXPEDICION.value not in text
    )
    if not es_fecha_nac:
        return None

    values = [w.value for w in all_lines[index + 1].words]
    return standardize_date(values[:3])


def _extract_birth_place(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()

    if not (_contains(text, IdAttributesEnum.LUGAR)
            and _contains(text, IdAttributesEnum.NACIMIENTO)):
        return None

    words = all_lines[index + 1].words
    parts = [
        w.value for w in words
        if not w.value.startswith('(') and not w.value.endswith(')')
    ]
    return " ".join(parts).upper()


def _extract_issue_date(line, all_lines: list, index: int) -> dict | None:
    text = " ".join(w.value for w in line.words).upper()
    if not _contains(text, IdAttributesEnum.EXPEDICION):
        return None

    words = all_lines[index + 1].words
    values  = [w.value for w in words]
    parts_location = [v.strip().rstrip(',') for v in values[3:]]

    return {
        'Fecha_Expedicion': standardize_date(values[:3]),
        'Lugar_Expedicion': " ".join(parts_location).upper(),
    }


def _extract_expiration_date(line, all_lines: list, index: int) -> str | None:
    text = " ".join(w.value for w in line.words).upper()
    if not _contains(text, IdAttributesEnum.EXPIRACION):
        return None

    for offset in range(1, 4):
        candidate_words = all_lines[index + offset].words
        if _is_label_or_short(candidate_words):
            continue
        values = [w.value for w in candidate_words]
        return standardize_date(values[:3])

    return None


# ─────────────────────────────────────────────
#  CÉDULA NUEVA  (refactorizada)
# ─────────────────────────────────────────────

# Orden importa: FECHA_NACIMIENTO debe evaluarse antes que LUGAR_NACIMIENTO
_NEW_ID_EXTRACTORS = [
    lambda l, ls, i, d: d.update({'NUIP': v}) or d
        if (v := _extract_nuip(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update({'Apellidos': v}) or d
        if (v := _extract_last_names(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update({'Nombres': v}) or d
        if (v := _extract_names(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update(v) or d
        if (v := _extract_height_and_sex(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update({'Fecha_Nacimiento': v}) or d
        if (v := _extract_birth_date(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update({'Lugar_Nacimiento': v}) or d
        if (v := _extract_birth_place(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update(v) or d
        if (v := _extract_issue_date(l, ls, i)) is not None else None,
    lambda l, ls, i, d: d.update({'Fecha_Expiracion': v}) or d
        if (v := _extract_expiration_date(l, ls, i)) is not None else None,
]


def proccess_new_id(ocr_result) -> dict:
    data_final = {}

    for page in ocr_result.pages:
        all_lines = [
            line
            for block in page.blocks
            for line in block.lines
        ]

        for i, line in enumerate(all_lines):
            try:
                for extractor in _NEW_ID_EXTRACTORS:
                    extractor(line, all_lines, i, data_final)
            except IndexError:
                continue

    return data_final