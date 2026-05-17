<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt;
    color: #000;
    line-height: 1.2;
}

/* ── HEADER ── */
.header-wrap {
    display: table;
    width: 100%;
    border-bottom: 2px solid #000;
    margin-bottom: 3px;
    padding-bottom: 3px;
}
.header-logo { display: table-cell; width: 22%; vertical-align: middle; }
.header-logo img { width: 110px; }
.header-logo-text {
    font-size: 19pt;
    font-weight: bold;
    color: #1a1a1a;
    letter-spacing: -1px;
}
.header-logo-text span { color: #888; }
.header-title {
    display: table-cell;
    width: 54%;
    text-align: center;
    vertical-align: middle;
    padding: 0 6px;
}
.header-title h1 {
    font-size: 11pt;
    font-weight: bold;
    line-height: 1.3;
    text-transform: none;
    border: none;
    margin: 0;
}
.header-right {
    display: table-cell;
    width: 24%;
    text-align: right;
    vertical-align: top;
    font-size: 6pt;
}
.header-right .site { font-size: 6.5pt; margin-bottom: 2px; }
.fecha-box {
    border: 1px solid #000;
    padding: 3px 5px;
    font-size: 7pt;
    font-weight: bold;
    text-align: center;
    margin-top: 2px;
}
.fecha-box .fecha-label {
    font-size: 6pt;
    font-weight: bold;
    background: #000;
    color: #fff;
    display: block;
    padding: 1px 2px;
    margin-bottom: 2px;
}
.fecha-value {
    border-top: 1px solid #000;
    font-size: 7pt;
    padding: 1px 0;
    display: inline-block;
    width: 100%;
    text-align: center;
}

/* numero factura */
.nro-factura {
    font-size: 6.5pt;
    margin: 3px 0 5px 0;
    border-bottom: 1px solid #aaa;
    padding-bottom: 2px;
}

/* ── SECTION HEADERS ── */
.sec-header {
    display: inline-block;
    background-color: {{ $primaryColor }};
    color: #fdfdfb;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 2px 16px 2px 8px;
    margin-top: 6px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    border-radius: 0 16px 16px 0;
}

/* ── MAIN GRID TABLE ── */
.form-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}
.form-table td {
    border: 1px solid #aaa;
    padding: 3px 2px;
    vertical-align: top;
    font-size: 6.5pt;
}
.form-table .lbl {
    font-size: 6.5pt;
    color: #444;
    display: inline;
    font-weight: bold;
}
.form-table .val {
    font-size: 6.5pt;
    font-weight: normal;
    display: inline;
}
.form-table tr.stacked td .lbl { display: block; }
.form-table tr.stacked td .val { display: block; }
.form-table .chk {
    display: inline-block;
    border: 1px solid #555;
    border-radius: 2px;
    width: 12px;
    height: 12px;
    vertical-align: middle;
    margin-left: 2px;
    font-size: 6.5pt;
    font-weight: bold;
    text-align: center;
    line-height: 10px;
    overflow: hidden;
}
.check-row { font-size: 6.5pt; }

/* row height */
.form-table td { height: 14px; }
.form-table td.tall { height: 20px; }

/* ── TEXT BODY (cláusulas) ── */
.text-body {
    font-size: 6pt;
    text-align: justify;
    line-height: 1.35;
    margin: 3px 0;
}
.text-body p { margin-bottom: 2px; }

/* ── FIRMA FINAL ── */
.firma-wrap {
    margin-top: 6px;
    border: 1px solid #000;
    padding: 4px 6px;
}
.firma-grid { display: table; width: 100%; }
.firma-left { display: table-cell; width: 55%; vertical-align: top; }
.firma-right { display: table-cell; width: 45%; vertical-align: top; border-left: 1px solid #000; padding-left: 8px; }
.firma-field { margin-bottom: 5px; }
.firma-field .lbl { font-size: 6pt; color: #333; }
.firma-field .line { border-bottom: 1px solid #000; min-height: 10px; margin-top: 1px; }
.firma-section-title {
    font-weight: bold;
    font-size: 7pt;
    text-align: center;
    background: #f0f0f0;
    padding: 2px;
    margin-bottom: 4px;
    border: 1px solid #ccc;
}
.firma-sig-line {
    border-bottom: 1px solid #000;
    margin-top: 20px;
    margin-bottom: 2px;
    width: 80%;
}

/* ── NIT footer ── */
.nit-footer {
    font-size: 6.5pt;
    text-align: center;
    margin-top: 4px;
    font-weight: bold;
}

/* helper */
.w100 { width: 100%; }
.bold { font-weight: bold; }
.small { font-size: 6pt; }
.center { text-align: center; }
.italic { font-style: italic; }
.noborder td { border: none !important; }
</style>
</head>
<body>

{{-- Todas las variables de lógica son inyectadas por ContractBuilderService --}}
{{-- $docCC, $docCE, $docOtro, $pepSi, $pepNo, ... $fmt --}}

{{-- ══════════════════ ENCABEZADO ══════════════════ --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:0;">
  <tr>
    {{-- Logo --}}
    <td style="width:22%; vertical-align:middle; padding-right:8px; border:none;">
      @if($brand->logo)
        <img src="{{ showImage($brand->logo) }}" alt="{{ $brand->name ?? '' }}" style="max-width:100%; max-height:50px;">
      @else
        <div class="header-logo-text">{{ $brand->name ?? '' }}</div>
      @endif
    </td>
    {{-- Título --}}
    <td style="vertical-align:middle; text-align:center; border:none;">
      <div style="font-size:11pt; font-weight:bold; line-height:1.3;">
        Contrato de Afiliación y Distribución del<br>
        Vendedor Independiente
      </div>
    </td>
    {{-- Derecha: sitio web + fecha --}}
    <td style="width:26%; vertical-align:top; text-align:right; border:none;">
      <div style="font-size:6.5pt; margin-bottom:4px;">{{ $brand->link ?? '' }}</div>
      <div style="font-size:6.5pt; font-weight:bold; text-align:center; margin-bottom:3px; letter-spacing:0.3px;">FECHA DE AFILIACIÓN</div>
      <table style="width:auto; border-collapse:separate; border-spacing:0; margin:0 auto;">
        <tr>
          <td style="border:1px solid #555; border-right:none; border-radius:6px 0 0 6px; text-align:center; font-size:6pt; font-weight:bold; padding:2px 6px; min-width:22px;">{{ now()->format('d') }}</td>
          <td style="border:1px solid #555; text-align:center; font-size:6pt; font-weight:bold; padding:2px 6px; min-width:22px;">{{ now()->format('m') }}</td>
          <td style="border:1px solid #555; border-left:none; border-radius:0 6px 6px 0; text-align:center; font-size:6pt; font-weight:bold; padding:2px 10px; min-width:36px;">{{ now()->format('Y') }}</td>
        </tr>
      </table>
    </td>
  </tr>
  {{-- Segunda fila: N° Factura y Código Interno alineados a la derecha --}}
  <tr>
    <td colspan="3" style="border:none; text-align:right; font-size:6.5pt; padding-top:4px;">
      N° de Factura <span style="display:inline-block; border-bottom:1px solid #000; width:90px;">&nbsp;</span>
      &nbsp;&nbsp;&nbsp;Código Interno <span style="display:inline-block; border-bottom:1px solid #000; width:90px;">&nbsp;</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 1: INFORMACIÓN BÁSICA ══════════════════ --}}
<div class="sec-header">INFORMACIÓN BÁSICA</div>

{{-- F1: Tipo Doc (35%) | Documento N° (40%) | Fecha Exp (25%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:45%;"><col style="width:30%;"><col style="width:25%;"></colgroup>
  <tr>
    <td><span class="lbl">Tipo de Documento:</span>
      <span class="check-row">C.C.:<span class="chk">{{ $docCC }}</span>
      &nbsp;&nbsp;C.E.:<span class="chk">{{ $docCE }}</span>
      &nbsp;&nbsp;OTRO:<span class="chk">{{ $docOtro }}</span>
      &nbsp;&nbsp;¿Cuál?<span style="display:inline-block; border-bottom:1px solid #aaa; width:40px;">&nbsp;</span></span>
    </td>
    <td><span class="lbl">Documento N°:</span>
      <span class="val">{{ $profile?->document_number ?? '' }}</span>
    </td>
    <td><span class="lbl">Fecha de Expiración:</span>
      <span class="val">{!! $profile?->expiration_date?->format('d/m/Y') ?? '&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;' !!}</span>
    </td>
  </tr>
</table>

{{-- F2: Ciudad Exp (16%) | 1er Apellido (18%) | 2do Apellido (32%) | Nombres (34%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:25%;"><col style="width:25%;"><col style="width:25%;"><col style="width:25%;"></colgroup>
  <tr>
    <td><span class="lbl">Ciudad de Expedición:</span>
      <span class="val">{{ $profile?->issueCity?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">1er. Apellido:</span>
      <span class="val">{{ $user->last_name ?? '' }}</span>
    </td>
    <td><span class="lbl">2do. Apellido:</span>
      <span class="val">{{ $user->middle_name ?? '' }}</span>
    </td>
    <td><span class="lbl">Nombres:</span>
      <span class="val">{{ $user->first_name ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F3: Sexo (6%) | Estado Civil (18%) | País (15%) | Departamento (61%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:17%;"><col style="width:29%;"><col style="width:27%;"><col style="width:27%;"></colgroup>
  <tr>
    <td><span class="lbl">Sexo:</span>
      <span class="val">{{ $profile?->gender?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">Estado Civil:</span>
      <span class="val">{{ $profile?->civilStatus?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">País:</span>
      <span class="val">Colombia</span>
    </td>
    <td><span class="lbl">Departamento:</span>
      <span class="val">{{ $state }}</span>
    </td>
  </tr>
</table>

{{-- F4: Ciudad (15%) | Dirección (52%) | Tel. Domicilio (33%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:20%;"><col style="width:46.7%;"><col style="width:33.3%;"></colgroup>
  <tr>
    <td><span class="lbl">Ciudad:</span>
      <span class="val">{{ $addr?->getCity?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">Dirección:</span>
      <span class="val">{{ $addr?->address ?? '' }}</span>
    </td>
    <td><span class="lbl">Tel. Domicilio:</span>
      <span class="val">{{ $profile?->phone_calls ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F5: Tel. Oficina (18%) | Cel. (22%) | Lugar de Nacimiento (60%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:33%;"><col style="width:33%;"><col style="width:33%;"></colgroup>
  <tr>
    <td><span class="lbl">Tel. Oficina:</span>
      <span class="val">{{ $profile?->phone_office ?? '' }}</span>
    </td>
    <td><span class="lbl">Cel:</span>
      <span class="val">{{ $profile?->whatsapp ?? '' }}</span>
    </td>
    <td><span class="lbl">Lugar de Nacimiento:</span>
      <span class="val">{{ $profile?->birthCity?->name ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F6: Fecha Nac (16%) | Profesión (25%) | E-mail (59%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:20%;"><col style="width:35%;"><col style="width:45%;"></colgroup>
  <tr>
    <td><span class="lbl">Fecha de Nacimiento:</span>
      <span class="val">{!! $profile?->date_of_birth?->format('d/m/Y') ?? '&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;' !!}</span>
    </td>
    <td><span class="lbl">Profesión:</span>
      <span class="val">{{ $profile?->profession?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">E-mail:</span>
      <span class="val">{{ $user->email ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F7: Cuenta Bancaria (22%) | Ahorros/Corriente (22%) | Nombre Banco (56%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:36%;"><col style="width:20%;"><col style="width:46%;"></colgroup>
  <tr>
    <td><span class="lbl">Cuenta Bancaria N°:</span>
      <span class="val">{{ $fp?->account_number ?? '' }}</span>
    </td>
    <td><span class="lbl">&nbsp;</span>
      <span class="check-row">Ahorros:<span class="chk">{{ $savingsChk }}</span>
      &nbsp;&nbsp;Corriente:<span class="chk">{{ $currentChk }}</span></span>
    </td>
    <td><span class="lbl">Nombre del Banco:</span>
      <span class="val">{{ $fp?->bank?->name ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F8: Actividad Económica (40%) | Código CIU (15%) | Nacionalidad (45%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:40.2%;"><col style="width:14.8%;"><col style="width:45%;"></colgroup>
  <tr>
    <td><span class="lbl">Actividad económica:</span>
      <span class="val">{{ $profile?->economicActivity?->name ?? '' }}</span>
    </td>
    <td><span class="lbl">Código CIU:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td><span class="lbl">Nacionalidad:</span>
      <span class="val">{{ $profile?->nationalityCountry?->name ?? 'Colombiana' }}</span>
    </td>
  </tr>
</table>

{{-- F9: Empresa (30%) | Cargo (28%) | Depto laboral + sociedad conyugal (42%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:30%;"><col style="width:28%;"><col style="width:42%;"></colgroup>
  <tr class="stacked">
    <td><span class="lbl">Nombre de la empresa donde labora (Si aplica):</span>
      <span class="val">{{ $fp?->company_name ?? '' }}</span>
    </td>
    <td><span class="lbl">Cargo que desempeña (Si aplica):</span>
      <span class="val">{{ $fp?->job_title ?? '' }}</span>
    </td>
    <td><span class="lbl">Departamento y domicilio de donde labora (Si aplica):</span>
      <span class="val">{{ $fp?->work_address ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- F10: Sociedad conyugal vigente --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:100%;"></colgroup>
  <tr>
    <td class="center">
      <span class="small italic">En la actualidad tengo sociedad conyugal o de hecho vigente:</span>
      &nbsp;Sí <span class="chk">{{ $socSi }}</span>
      &nbsp;No <span class="chk">{{ $socNo }}</span>
    </td>
  </tr>
</table>

{{-- F11: Nombre Completo (36%) | Tipo Sociedad (28%) | Documento Identidad (36%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:36%;"><col style="width:28%;"><col style="width:36%;"></colgroup>
  <tr>
    <td><span class="lbl">Nombre Completo:</span><span class="val">&nbsp;</span></td>
    <td><span class="lbl">Tipo de Sociedad:</span><span class="val">&nbsp;</span></td>
    <td><span class="lbl">Documento de Identidad:</span><span class="val">&nbsp;</span></td>
  </tr>
</table>

{{-- F12: PEP (65%) | Administra recursos (35%) --}}
<table class="form-table" style="table-layout:fixed; width:100%; margin-bottom:-1px;">
  <colgroup><col style="width:65%;"><col style="width:35%;"></colgroup>
  <tr>
    <td><span class="lbl">Identifique si el solicitante es una PEP (Persona Expuesta Públicamente):</span>
      &nbsp;Sí <span class="chk">{{ $pepSi }}</span>
      &nbsp;No <span class="chk">{{ $pepNo }}</span>
    </td>
    <td><span class="lbl">¿Administra recursos públicos?:</span>
      &nbsp;Sí <span class="chk">{{ $pubSi }}</span>
      &nbsp;No <span class="chk">{{ $pubNo }}</span>
    </td>
  </tr>
</table>

{{-- F13: PEP familiares (100%) --}}
<table class="form-table" style="table-layout:fixed; width:100%;">
  <tr>
    <td><span class="lbl">¿Tiene el solicitante parientes de consanguinidad y/o afinidad en primer grado civil que sean PEP (Persona expuesta políticamente)?:</span>
      &nbsp;Sí <span class="chk">{{ $pepFamSi }}</span>
      &nbsp;No <span class="chk">{{ $pepFamNo }}</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 2: INFORMACIÓN PEP PARIENTE ══════════════════ --}}
<div class="sec-header">INFORMACIÓN PEP PARIENTE</div>

<table class="form-table">
  <tr>
    <td style="width:28%;">
      <span class="lbl">1er Apellido:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:28%;">
      <span class="lbl">2do. Apellido:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:44%;">
      <span class="lbl">Nombres:</span>
      <span class="val">&nbsp;</span>
    </td>
  </tr>
  <tr>
    <td style="width:38%;">
      <span class="lbl">Tipo de Documento:</span>
      <span class="check-row">
        C.C.:<span class="chk">&nbsp;</span>
        &nbsp;&nbsp;C.E.:<span class="chk">&nbsp;</span>
        &nbsp;&nbsp;OTRO:<span class="chk">&nbsp;</span>
        &nbsp;&nbsp;¿Cuál?<span style="display:inline-block; border-bottom:1px solid #aaa; width:40px;">&nbsp;</span>
      </span>
    </td>
    <td style="width:30%;">
      <span class="lbl">Documento N°:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:32%;">
      <span class="lbl">Fecha de expedición:</span>
      <span class="val">&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;</span>
    </td>
  </tr>
  <tr>
    <td style="width:30%;">
      <span class="lbl">Cargo o Función:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:30%;">
      <span class="lbl">Ciudad:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:40%;">
      <span class="lbl">Fecha de Retiro del Cargo:</span>
      <span class="val">&nbsp;</span>
    </td>
  </tr>
  <tr>
    <td colspan="3" style="font-size:6pt; font-weight:bold; text-align:center; padding:2px;">
      LA SIGUIENTE INFORMACIÓN LA REGISTRO EN CUMPLIMIENTO DE LO DISPUESTO EN EL ARTÍCULO 3 DEL DECRETO 830 DEL 26 DE JULIO DE 2021 APLICABLE A LA EMPRESA
    </td>
  </tr>
  <tr>
    <td colspan="3" style="background:#d7d7d7; font-weight:bold; text-align:center; padding: 1px 4px;">
      <span class="small">Información del cónyuge y/o compañero(a) permanente</span><br>
    </td>
  </tr>
  <tr>
    <td colspan="3" style="text-align: center; padding: 1px 4px;">
      <span class="small italic">En la actualidad tengo sociedad conyugal o de hecho vigente:</span>
      &nbsp;Sí <span class="chk">&nbsp;</span>
      &nbsp;No <span class="chk">&nbsp;</span>
    </td>
  </tr>
  <tr>
    <td style="width:36%;">
      <span class="lbl">Nombre Completo:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:28%;">
      <span class="lbl">Tipo de Sociedad:</span>
      <span class="val">&nbsp;</span>
    </td>
    <td style="width:36%;">
      <span class="lbl">Documento de Identidad:</span>
      <span class="val">&nbsp;</span>
    </td>
  </tr>
  <tr>
    <td colspan="3" style="background:#d7d7d7; font-size:6pt; font-weight:bold; padding:2px 4px; text-align:center;">
      Información de Parientes de Consanguinidad, Afinidad y Primero Civil
    </td>
  </tr>
  <tr>
    <td colspan="3" class="center" style="font-size:6pt; padding:2px 4px;">
      A continuación, relacione información de los parientes hasta el segundo grado de consanguinidad civil, segundo de afinidad (Padres, Suegros, Hijos, Yerno/Nuera, Abuelos,
      Hermanos, Cuñados y Nietos) que sea susceptible de generar conflicto de interés frente a la labor o actividad que desempeño:
    </td>
  </tr>
  <tr>
    <td style="width:40%; font-weight:bold; font-size:6.5pt; text-align:center; background:#e8e8e8;">NOMBRE COMPLETO<br><span style="font-weight:normal; font-size:6pt;">(nombres y apellidos)</span></td>
    <td style="width:30%; font-weight:bold; font-size:6.5pt; text-align:center; background:#e8e8e8;">PARENTESCO</td>
    <td style="width:30%; font-weight:bold; font-size:6.5pt; text-align:center; background:#e8e8e8;">DOCUMENTO DE IDENTIDAD</td>
  </tr>
  <tr>
    <td style="height:16px;">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td style="height:16px;">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 3: POTENCIALES CONFLICTOS DE INTERÉS ══════════════════ --}}
<div class="sec-header">POTENCIALES CONFLICTOS DE INTERÉS</div>

<table class="form-table">
  <tr>
    <td colspan="5" class="center" style="padding:0px 32px 0px;">
      <span class="small">A) Declaro que a la fecha No tengo<span class="chk">{{ $hasFxNo }}</span> Si tengo<span class="chk">{{ $hasFxSi }}</span> cuentas financieras en algún país extranjero con total derecho o poder de firma o de otra índole sobre alguna. En caso de marcar SI, indicar lo siguiente:</span>
    </td>
  </tr>
  <tr>
    <td style="width:16%; font-size:6pt; font-weight:bold; background:#e8e8e8; text-align:center;">País:</td>
    <td style="width:16%; font-size:6pt; font-weight:bold; background:#e8e8e8; text-align:center;">Ciudad:</td>
    <td style="width:24%; font-size:6pt; font-weight:bold; background:#e8e8e8; text-align:center;">Banco:</td>
    <td style="width:22%; font-size:6pt; font-weight:bold; background:#e8e8e8; text-align:center;">N° Cuenta:</td>
    <td style="width:22%; font-size:6pt; font-weight:bold; background:#e8e8e8; text-align:center;">Tipo de Moneda:</td>
  </tr>
  <tr>
    <td style="height:14px;">{{ $fp?->foreignCountry?->name ?? '' }}</td>
    <td>{{ $fp?->foreignCity?->name ?? '' }}</td>
    <td>{{ $fp?->foreign_bank ?? '' }}</td>
    <td>{{ $fp?->foreign_account_number ?? '' }}</td>
    <td>{{ $fp?->foreign_currency ?? '' }}</td>
  </tr>
  <tr>
    <td colspan="5" style="padding:2px 4px;">
      <span class="small">
        B) Relacione otros intereses personales que podrían constituir una posible situación de conflicto de intereses, por ejemplo:<br>
        - Actividades que desempeño, negocios, establecimientos que poseo etc.<br>
        - Actividades o negocios de mi cónyuge o compañero(a) permanente y parientes hasta el segundo grado de consanguinidad, segundo de afinidad y primero civil, de acuerdo con lo descrito en el numeral 2.<br>
        - Actividades o negocios de mi sociedad de derecho o hecho, tiene participación, administración o es beneficiario de fideicomisos o patrimonios autónomos.
      </span>
    </td>
  </tr>
  <tr>
    <td colspan="5" style="font-size:6.5pt; font-weight:bold; background:#e8e8e8; text-align:center; padding:2px;">
      Descripción del Potencial Conflicto de Interés:
    </td>
  </tr>
  <tr>
    <td colspan="5" style="height:40px;">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5" style="padding:2px 4px;">
      <span class="small">
        C) Me comprometo que en caso de presentarse durante el ejercicio del cargo y/o función una situación de Conflicto de Interés me declaro impedido e informaré para que se tomen las medidas a que haya lugar.
      </span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 4: INFORMACIÓN FINANCIERA ══════════════════ --}}
<div class="sec-header">INFORMACIÓN FINANCIERA</div>

<table class="form-table">
  <tr>
    <td style="width:33%;">
      <span class="lbl">Total Ingresos Mensuales:</span>
      <span class="val">{{ $fmt($fp?->monthly_income) }}</span>
    </td>
    <td style="width:34%;">
      <span class="lbl">Total de Otros Ingresos:</span>
      <span class="val">{{ $fmt($fp?->other_income) }}</span>
    </td>
    <td style="width:33%;">
      <span class="lbl">¿Cuáles?:</span>
      <span class="val">{{ $fp?->other_income_desc ?? '' }}</span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="lbl">Total Activos:</span>
      <span class="val">{{ $fmt($fp?->total_assets) }}</span>
    </td>
    <td>
      <span class="lbl">Total Pasivos:</span>
      <span class="val">{{ $fmt($fp?->total_liabilities) }}</span>
    </td>
    <td>
      <span class="lbl">Total Patrimonio:</span>
      <span class="val">{{ $fmt($fp?->total_equity) }}</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 5: OPERACIONES EN MONEDA EXTRANJERA ══════════════════ --}}
<div class="sec-header">OPERACIONES EN MONEDA EXTRANJERA</div>

<table class="form-table">
  <tr>
    <td style="width:40%;">
      <span class="lbl">Realiza Operaciones en Moneda Extranjera (X):</span>
      &nbsp;Sí <span class="chk">{{ $opsFxSi }}</span>
      &nbsp;No <span class="chk">{{ $opsFxNo }}</span>
    </td>
    <td style="width:60%;" colspan="2">
      <span class="lbl">Cuáles:</span>
      <span class="val">{{ $fp?->ops_foreign_desc ?? '' }}</span>
    </td>
  </tr>
  <tr>
    <td style="width:40%;">
      <span class="lbl">Posee Cuentas en Moneda Extranjera (X):</span>
      &nbsp;Sí <span class="chk">{{ $hasFxSi }}</span>
      &nbsp;No <span class="chk">{{ $hasFxNo }}</span>
    </td>
    <td style="width:33%;">
      <span class="lbl">Banco:</span>
      <span class="val">{{ $fp?->foreign_bank ?? '' }}</span>
    </td>
    <td style="width:37%;">
      <span class="lbl">N° de Cuenta:</span>
      <span class="val">{{ $fp?->foreign_account_number ?? '' }}</span>
    </td>
  </tr>
  <tr>
    <td style="width:40%;">
      <span class="lbl">Moneda:</span>
      <span class="val">{{ $fp?->foreign_currency ?? '' }}</span>
    </td>
    <td style="width:33%;">
      <span class="lbl">País:</span>
      <span class="val">{{ $fp?->foreignCountry?->name ?? '' }}</span>
    </td>
    <td style="width:37%;">
      <span class="lbl">Ciudad:</span>
      <span class="val">{{ $fp?->foreignCity?->name ?? '' }}</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 6: INFORMACIÓN TRIBUTARIA ══════════════════ --}}
<div class="sec-header">INFORMACIÓN TRIBUTARIA</div>

<table class="form-table">
  {{-- Fila 1: Responsable IVA | Agente Retención Renta | Agente Retención ICA --}}
  <tr>
    <td style="width:35%;">
      <span class="lbl">Responsable de IVA:</span>
      &nbsp;Sí <span class="chk">{{ $ivaSi }}</span>
      &nbsp;No <span class="chk">{{ $ivaNo }}</span>
    </td>
    <td style="width:34%;">
      <span class="lbl">Agente de Retención Renta:</span>
      &nbsp;Sí <span class="chk">{{ $agRteSi }}</span>
      &nbsp;No <span class="chk">{{ $agRteNo }}</span>
    </td>
    <td style="width:33%;">
      <span class="lbl">Agente de Retención ICA:</span>
      &nbsp;Sí <span class="chk">{{ $agIcaSi }}</span>
      &nbsp;No <span class="chk">{{ $agIcaNo }}</span>
    </td>
  </tr>
  {{-- Fila 2: Resp. Imp. Ventas | Gran Contribuyente | Autorretenedor --}}
  <tr>
    <td>
      <span class="lbl">Responsables de Impuestos a las Ventas (X):</span>
      &nbsp;Sí <span class="chk">{{ $salesTaxSi }}</span>
      &nbsp;No <span class="chk">{{ $salesTaxNo }}</span>
    </td>
    <td>
      <span class="lbl">Gran Contribuyente (X):</span>
      &nbsp;Sí <span class="chk">{{ $grandSi }}</span>
      &nbsp;No <span class="chk">{{ $grandNo }}</span>
    </td>
    <td>
      <span class="lbl">Autorretenedor (X):</span>
      &nbsp;Sí <span class="chk">{{ $autRteSi }}</span>
      &nbsp;No <span class="chk">{{ $autRteNo }}</span>
    </td>
  </tr>
  {{-- Fila 3: Retención en la fuente | Motivo (si aplica) --}}
  <tr>
    <td style="width:35%;">
      <span class="lbl">Retención en la fuente (X):</span>
      &nbsp;Sí <span class="chk">{{ $rvaSi }}</span>
      &nbsp;No <span class="chk">{{ $rvaNo }}</span>
    </td>
    <td colspan="2">
      <span class="lbl">En caso de marcar no en la retención en la fuente, explique el motivo:</span>
      <span class="val">{{ $retentionReason }}</span>
    </td>
  </tr>
  {{-- Fila 4: Imp. Industria y Comercio | Tarifa ICA --}}
  <tr>
    <td colspan="2">
      <span class="lbl">Impuestos de Industria y Comercio (X):</span>
      &nbsp;Sí <span class="chk">{{ $icaTaxSi }}</span>
      &nbsp;No <span class="chk">{{ $icaTaxNo }}</span>
    </td>
    <td>
      <span class="lbl">Tarifa de Impuesto de Industria y Comercio (X):</span>
      <span class="val">{{ $icaRate }}</span>
    </td>
  </tr>
  {{-- Fila 5: Ciudad Donde Declara --}}
  <tr>
    <td colspan="3">
      <span class="lbl">Ciudad Donde Declara:</span>
      <span class="val">{{ $declarationCity }}</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ SECCIÓN 7: INFORMACIÓN DEL REPRESENTANTE ══════════════════ --}}
<div class="sec-header">INFORMACIÓN DEL REPRESENTANTE</div>

<table class="form-table">
    <tr>
        <td style="width:34%;">
        <span class="lbl">Código Interno:</span>
        <span class="val">{{ $profile?->representative?->referralCode?->referral_code ?? '' }}</span>
        </td>
        <td style="width:33%;">
        <span class="lbl">Cel./Tel.:</span>
        <span class="val">{{ $profile?->representative?->phone ?? '' }}</span>
        </td>
        <td style="width:33%;">
        <span class="lbl">País:</span>
        <span class="val">COLOMBIA</span>
        </td>
    </tr>
    <tr>
        <td>
        <span class="lbl">1er. Apellido:</span>
        <span class="val">{{ $profile?->representative?->last_name ?? '' }}</span>
        </td>
        <td>
        <span class="lbl">2do. Apellido:</span>
        <span class="val">{{ $profile?->representative?->middle_name ?? '' }}</span>
        </td>
        <td>
        <span class="lbl">Nombres:</span>
        <span class="val">{{ $profile?->representative?->first_name ?? '' }}</span>
        </td>
    </tr>
</table>

{{-- ══════════════════ CUERPO LEGAL DEL CONTRATO ══════════════════ --}}
<div style="margin-top:5px; border-top: 1px solid #aaa; padding-top:3px;" class="text-body">

<p>
Contrato de Afiliación y Distribución del Vendedor Independiente. Entre los suscritos,
<strong>{{ strtoupper(trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''))) }}</strong>,
mayor de edad, identificado con cédula de ciudadanía No. <strong>{{ $profile?->document_number ?? '' }}</strong>,
expedida en <strong>{{ strtoupper($profile?->document_expedition_place ?? '') }}</strong>,
residente en <strong>{{ strtoupper($addr?->city ?? '') }}@if($state), {{ strtoupper($state) }}@endif</strong>,
mayor de edad y quien actúa en su propio nombre y representación, parte que en adelante se llamará el VENDEDOR INDEPENDIENTE
y {{ strtoupper($brand->getTranslation('name', 'es')) }}@if($brand->nit), persona jurídica con NIT No. {{ $brand->nit }}@endif,
persona jurídica creada por documento privado del 25 de octubre de 2021, representada a través de {{ strtoupper($brand->getTranslation('name', 'es')) }}@if($brand->nit),
quien se identifica con NIT No. {{ $brand->nit }}@endif, todo lo cual consta en el certificado de existencia y representación legal, expedido
por la Cámara de Comercio de la jurisdicción, documento que hará parte integral de este contrato, parte que en adelante se llamará la EMPRESA.
En adelante los dos anteriores igualmente identificados como PARTES. Por el presente se acuerda celebrar un CONTRATO DE AFILIACIÓN A LA RED Y MARCADEO MULTINIVEL,
según lo dispuesto en la Ley 1700 del 27 de diciembre de 2013, así como bajo los términos y condiciones que constan en las cláusulas siguientes:
Por medio del presente contrato, EL VENDEDOR INDEPENDIENTE se afilia a la red multinivel de la EMPRESA, para que pueda, bajo su entera y absoluta responsabilidad
y autonomía técnica, administrativa, financiera y directiva, tener la posibilidad de realizar actividades de mercadeo y promoción para la venta de productos de la EMPRESA,
así como la búsqueda e incorporación de personas a la actividad multinivel, a fin de acceder a las comisiones, bonificaciones y premios ofrecidos por la EMPRESA de tiempo en tiempo.
Las partes acuerdan que EL VENDEDOR INDEPENDIENTE no está obligado a realizar gestiones específicas para comercializar los productos de LA EMPRESA y/o incorporar personas a la red,
pero, si así desea hacerlo, recibirá las bonificaciones o premios ofrecidos por la EMPRESA en la Guía de Negocio que sea entregada para el efecto, siempre y cuando acredite tales ventas
ya sea por su gestión o la de sus incorporaciones dentro de la red multinivel y las mismas sean aceptadas por la EMPRESA de acuerdo al trámite establecido en la Manual del Vendedor
Independiente de la compañía. (Anexo al presente contrato).
</p>

<p style="margin-top:3px;">
SEGUNDA: NATURALEZA DEL CONTRATO El presente contrato es de índole estrictamente civil y no constituye prestación de servicios ni vínculo laboral alguno entre la EMPRESA y EL VENDEDOR INDEPENDIENTE,
lo que se corrobora al tenor de las características del presente contrato multinivel y las condiciones detalladas en las facultades y obligaciones de cada una de las partes de este contrato.
Como consecuencia de la inexistencia de vínculo laboral entre la EMPRESA y el VENDEDOR INDEPENDIENTE, queda claramente establecido que este último no accede a ningún tipo de compensación o beneficio
por tiempo de servicios, pensión de jubilación, seguro de vida, vacaciones, indemnizaciones, plan de salud y en general ningún otro beneficio social o laboral a cargo de la EMPRESA.
TERCERA: OBLIGACIONES DEL VENDEDOR INDEPENDIENTE En virtud del presente contrato serán obligaciones del VENDEDOR INDEPENDIENTE: 3.1. EL VENDEDOR INDEPENDIENTE determinará de manera autónoma, pero siguiendo
las pautas establecidas por la EMPRESA, la manera en que coordinará a las personas que logre incorporar en la actividad multinivel. 3.2. El VENDEDOR INDEPENDIENTE asume enteramente todos los riesgos para sí
y para sus eventuales empleados, inherentes a su actividad independiente y especializada bajo este contrato y utilizará sus propios equipos, herramientas y bienes que en general requiera para la ejecución
del presente contrato. 3.3. El VENDEDOR INDEPENDIENTE declara conocer las reglamentaciones legales aplicables a las actividades que desarrollará bajo este contrato, entender lo contenido en el presente
documento, y de forma expresa se compromete a su cabal observancia y cumplimiento. 3.4. Enviar inmediatamente a la EMPRESA los contratos de venta debidamente diligenciados con la información del producto
ordenado, los datos del comprador, así como la información que sea necesaria según se trate de una venta de contado o a crédito. La información y firmas contenidas en el contrato de venta deben haber sido
suministradas directamente por el comprador / deudor y el codeudor cuando a ello haya lugar. 3.5. Recibir del comprador y enviar sin mayor dilación a la EMPRESA, mediante los canales de pago aprobados por
esta, la cantidad total de dinero recibida del comprador cuando se trate de pago total del producto por venta al contado, la cual en ningún caso podrá ser inferior a la suma mínima establecida en las listas
de precios vigentes al momento de la venta, junto con el correspondiente contrato de venta. La remisión o transferencia de la suma de dinero a la que se refiere este literal por parte de EL VENDEDOR
INDEPENDIENTE a la EMPRESA, se realizará a través de las entidades de recaudo informadas por esta en los formularios entregados a EL VENDEDOR INDEPENDIENTE. 3.6. Recibir del comprador y enviar sin mayor
dilación a la EMPRESA, mediante los canales de pago aprobados por la misma, la cantidad total de dinero recibida del comprador a título de cuota inicial cuando se trate de venta a crédito, junto con el
correspondiente contrato de venta, en el cual deberá constar el precio total de la venta, que en ningún caso podrá ser inferior a la suma mínima establecida en la listas de precios vigentes, así como el
valor de la cuota inicial recibida del comprador y las cuotas mensuales pactadas. La remisión o transferencia de la suma de dinero a la que se refiere este literal por parte de EL VENDEDOR INDEPENDIENTE a
la EMPRESA, se realizará a través de las entidades de recaudo informadas por esta en los formularios entregados a EL VENDEDOR INDEPENDIENTE. 3.7. A excepción hecha de la cuota inicial cuando se trate de
ventas a crédito o del precio total de la compra cuando se trate de ventas al contado, y salvo que la EMPRESA lo autorice por escrito, EL VENDEDOR INDEPENDIENTE deberá abstenerse de cobrar o recibir de los
compradores suma alguna de dinero por concepto de pago de cuotas. 3.8. Proporcionar la información y documentos adicionales que la EMPRESA pueda razonablemente solicitar con respecto a cualquier contrato de
venta. 3.9. Cumplir y velar por la observancia de las disposiciones contempladas en Manual del Vendedor Independiente, los códigos de ética establecidos por la EMPRESA, por la DSA (Direct Selling Association),
ACOVEDI (Asociación Colombiana de Venta Directa), INVIMA (Instituto Nacional de Vigilancia de Medicamentos y Alimentos), entre otros, así como la normatividad vigente en materia de protección al consumidor,
entre otros aplicables. 3.10. Abstenerse de identificarse, presentarse o declararse como empleado o representante de la EMPRESA. 3.11. Abstenerse de usar el nombre de la EMPRESA, su imagen, la de sus productos
o logotipos en listas telefónicas, páginas web, anuncios publicitarios, avisos interiores y/o exteriores, promociones o cualquier otra publicación sin consentimiento previo y por escrito de la EMPRESA. 3.12.
Reembolsar las comisiones y/o bonificaciones que hayan sido pagadas por anticipado por la EMPRESA, por las ventas realizadas en caso de que el comprador no acepte o devuelva el producto incluido en un contrato
de venta que EL VENDEDOR INDEPENDIENTE hubiese diligenciado y remitido a la EMPRESA, se reserva el derecho de reversar las comisiones y/o bonificaciones pagadas, en caso de que el comprador no pague el valor
del producto o si la EMPRESA no recibe el dinero suficiente para cubrir las comisiones o bonificaciones pagadas por anticipado. En todo caso EL VENDEDOR INDEPENDIENTE autoriza desde ya a la EMPRESA para cargar
a su estado de cuenta, retener el pago y/o compensar las cantidades que EL VENDEDOR INDEPENDIENTE le adeude, por cualquier concepto, incluyendo la reversión de comisiones y/o bonificaciones. 3.13. Pagar a
tiempo el total del precio de venta de los productos adquiridos directamente por EL VENDEDOR INDEPENDIENTE ya sea de contado o a crédito, así como cualquier bono de ingreso, material de venta o cargos de
mantenimiento y cualquier otra cantidad que se haya obligado a pagar a la EMPRESA. 3.14. Utilizar de manera responsable los formularios que la EMPRESA ponga a su disposición, única y exclusivamente para el
desarrollo de su actividad conforme a lo pactado en este contrato, así como responder por el uso indebido de los mismos. 3.15. Empeñar en correcta forma cada uno de los instrumentos que se han dispuesto por
parte de la empresa como lo son el Manual de Marca y el tool kit, conservando de esta manera la integridad, valores, conducta y ética de la EMPRESA. 3.16. EL VENDEDOR INDEPENDIENTE se obliga por medio del
presente a comercializar de manera exclusiva los productos de la EMPRESA a fin de no incurrir en un conflicto de intereses o competencia desleal. 3.1.5. Abstenerse de celebrar contratos, acuerdos o compromisos,
verbales o escritos con los compradores de los productos de la EMPRESA o con terceros, de los cuales se pueda inferir que la misma asume algún tipo de responsabilidad distinta al suministro de los productos.
Salvo autorización previa y escrita de la EMPRESA. 3.16. Difundir en forma permanente las normas de conducta y ética de la EMPRESA, así como las establecidas por la DSA y ACOVEDI entre su red de VENDEDORES
INDEPENDIENTES para todas y cada una de sus actividades comerciales. 3.17. EL VENDEDOR INDEPENDIENTE no está autorizado a demostrar los productos de la EMPRESA en ningún tipo de establecimiento comercial sin
autorización escrita de la misma, independiente de la denominación que este tenga. 3.18. EL VENDEDOR INDEPENDIENTE no está autorizado a comercializar, celebrar contratos, acuerdos o compromisos, verbales o
escritos con los productos o códigos de la EMPRESA por fuera del territorio colombiano. 3.19. Suscribir los formularios establecidos por la EMPRESA para oficializar su vinculación en calidad de VENDEDOR
INDEPENDIENTE, así como renovar anualmente su vinculación, efectuando el pago o mediante el débito directo de su estado de cuenta, del cargo de solicitud en los montos previamente establecidos por esta, y
actualizar la documentación e información aportada y registrada, necesaria para la afiliación en calidad de VENDEDOR INDEPENDIENTE cuando la EMPRESA lo requiera, o cuando la autoridad competente en materia
tributaria así lo considere. 3.20. EL VENDEDOR INDEPENDIENTE se obliga a usar de manera responsable el código asignado por la EMPRESA, el cual es personal e intransferible y será responsable por todas las
transacciones que se tramiten por medio del mismo. 3.21. EL VENDEDOR INDEPENDIENTE operará como un negocio independiente al ejecutar este contrato, asumiendo toda la carga tributaria que grava los ingresos
que percibe por la intermediación en la venta de productos de la EMPRESA, tales como renta, IVA, retención en la fuente o cualquier otro impuesto. 3.22. EL VENDEDOR INDEPENDIENTE no podrá ceder sus derechos
ni su posición contractual a terceras personas, salvo autorización por escrito de la EMPRESA y con el lleno de las formalidades y la documentación establecidas para tal fin. La sucesión por causa de muerte será
el único caso contemplado para la cesión de los derechos, previa verificación de formalidades y la documentación pertinente. 3.23. EL VENDEDOR INDEPENDIENTE se obliga a no revelar ni poner a disposición de
ningún competidor de la EMPRESA o de tercera persona, ningún material o información confidencial de esta, como por ejemplo, información relativa a la relación de compradores o posibles compradores, las listas de
precios y el valor de las comisiones, los planes de ventas, plan de compensación, promociones y mercadeo, así como cualquier otra información o material marcado o identificado de alguna manera como confidencial
o de propiedad de la EMPRESA y cuya divulgación le pueda representar algún perjuicio a misma. 3.24. Atender las sugerencias y recomendaciones que imparta la EMPRESA, siempre que estas se relacionen con el objeto
del contrato. 3.25. No intervenir de ninguna manera en las actividades de la EMPRESA, que no guarden relación con el objeto del presente contrato. PARÁGRAFO PRIMERO: No dar, pagar, entregar, ofrecer o prometer
el pago o la entrega de dinero, dádiva, contraprestación o de cualquier cosa de valor, directa o indirectamente para o a favor de alguna persona u oficial gubernamental, entidad pública, partido político, candidato
a puesto oficial dentro del gobierno, organización pública internacional, o empleado del área administrativa de la compañía con el propósito de obtener, negociar, ofrecer, lograr o retener cualquier negocio para
la venta de los productos de la EMPRESA. CUARTA: OBLIGACIONES DE LA EMPRESA 4.1. Tener a disponibilidad de EL VENDEDOR INDEPENDIENTE, los formularios de pedidos, formularios de vinculación, muestras publicitarias,
catálogos, Guía de Negocio, y en general cualquier otro material que la EMPRESA determine necesaria para el desarrollo del objeto de este contrato, entendiendo que la EMPRESA, por la naturaleza de la vinculación,
no se encuentra en la obligación de suministrar local comercial o establecimiento de comercio para el desarrollo de las actividades del VENDEDOR INDEPENDIENTE. 4.2. Revisar a la mayor brevedad posible y en su caso,
aceptar o rechazar, cualquier solicitud de venta de productos presentada por EL VENDEDOR INDEPENDIENTE, teniendo en cuenta que todos los pedidos están sujetos a la aceptación de la EMPRESA, a su completa discreción,
conforme a los parámetros establecidos en la Guía de Negocio. 4.3. Reconocer a EL VENDEDOR INDEPENDIENTE, una comisión y/o bonificación por los productos cuya venta haya sido aceptada por la EMPRESA, según importes
determinables de acuerdo con la lista de precios y de acuerdo con el programa de comisiones y/o bonificaciones establecido en la Guía de Negocio vigente a la fecha de presentación del pedido. La comisión y/o
bonificación obtenida por el VENDEDOR INDEPENDIENTE será pagada el día 15 del mes siguiente al cierre previa verificación de la documentación pertinente. 4.4. Proporcionar a EL VENDEDOR INDEPENDIENTE el acceso
a estados de cuenta periódicos que incorporen el detalle de las comisiones y bonificaciones pagadas a su favor y en general todos los ingresos y egresos que se generen en virtud del contrato. 4.5. Efectuar los
descuentos y/o retenciones correspondientes a los impuestos, tasas o contribuciones que graven las comisiones o cualquier otro concepto que se pague o entregue a EL VENDEDOR INDEPENDIENTE, de conformidad con este
contrato y con las normas tributarias aplicables. QUINTA: FACULTADES Y DERECHOS DE LAS PARTES. 5.1. DEL VENDEDOR INDEPENDIENTE: 5.1.1. Decidir libremente la cantidad de tiempo que dedicará al desarrollo de este
contrato. En este sentido, EL VENDEDOR INDEPENDIENTE entiende que este contrato de afiliación no exige acciones específicas sobre la comercialización de los productos de la EMPRESA y/o la incorporación de personas
a la red, pero le permite acceder a mayores beneficios en la medida en que dedique más tiempo al desarrollo del mismo, siempre que se materialice en mayores ventas de productos de la EMPRESA. 5.1.2. Contratar por
su exclusiva cuenta y costo toda la asistencia que pueda requerir para la ejecución del objeto de este contrato, en cuyo caso EL VENDEDOR INDEPENDIENTE asumirá todos los gastos, costos, impuestos y demás sin límite,
propios del sistema de contratación que determine EL VENDEDOR INDEPENDIENTE. También deberá adquirir por su exclusiva cuenta y costo cualquier elemento, herramienta, asesoría y demás, que requiera para la ejecución
de este contrato. 5.1.3. Desarrollar cualquier técnica de venta siempre que la misma respete el código de ética, Manual de Vendedor Independiente, valores de los productos y demás requisitos que hayan sido publicados
por la EMPRESA. 5.1.4. Formular preguntas, consultas y solicitudes de aclaración a la EMPRESA, y recibir información suficiente en relación con los productos, las metas, los beneficios de pertenecer a la red
multinivel y los planes de compensación, y en general sobre cualquier asunto del presente Contrato. 5.1.5. Ser informado con precisión por parte de la EMPRESA, de las características de los productos y del alcance
de las garantías de los productos. 5.1.6. Percibir oportuna e inequívocamente las compensaciones, comisiones y/o bonificaciones en razón de las ventas y los logros asociados a su actividad independiente, según el
plan de compensaciones de la EMPRESA, y con sujeción a las condiciones establecidas en este contrato para la procedencia de dichos pagos. 5.2. DE LA EMPRESA: 5.2.1. Ser defendido, indemnizado y mantenido indemne
por parte de EL VENDEDOR INDEPENDIENTE por todo reclamo, queja, declaración de responsabilidad, daño, perjuicios, multas, sanciones, costos, honorarios razonables de abogados y en general cualquier gasto en el que
la EMPRESA, sus subsidiarias, directores, ejecutivos, empleados, contratistas y representantes incurran, como consecuencia de cualquier acción u omisión por parte del VENDEDOR INDEPENDIENTE, entre ellos, falsedad,
mala conducta, negligencia, información engañosa, incumplimiento de las declaraciones y garantías no autorizadas por la EMPRESA, incumplimiento de las normas laborales, de protección del consumidor y tributarias que
le apliquen, reclamaciones de índole laboral relacionadas con los empleados del VENDEDOR INDEPENDIENTE, o cualquier otra conducta agraviosa, infracción de cualquier ley, o cualquier acto ilegal o no autorizado.
5.2.2. La EMPRESA podrá cargar al estado de cuenta de EL VENDEDOR INDEPENDIENTE, las cuotas vencidas de cualquier crédito otorgado por esta, a favor del VENDEDOR INDEPENDIENTE. 5.2.3. La EMPRESA se reserva el
derecho a no aceptar solicitudes de vinculación con esta, independiente la razón que lo fundamente, en un término de 30 días posteriores a la recepción de la vinculación como Vendedor Independiente.
SEXTA: PLAN DE COMPENSACIÓN Y FORMA DE PAGO Si EL VENDEDOR INDEPENDIENTE, ya sea directamente o por medio de su red de ventas, ha decidido adelantar gestiones para las cuales logre que un cliente o comprador
realice un pedido a la EMPRESA, cuya venta haya sido aceptada por esta, en o antes de la fecha de terminación de este contrato, la EMPRESA reconocerá a EL VENDEDOR INDEPENDIENTE las comisiones y/o bonificaciones
especificadas en el plan de compensación incluido en el Manual del Vendedor Independiente vigente al momento del pedido según sea el caso y que hace parte integral del presente contrato. En el plan de compensación
incluida en la Manual del Vendedor Independiente se especifican las condiciones para ascender en la red multinivel, así como en los diferentes medios de comunicación se muestran los premios y concursos a que puede
acceder EL VENDEDOR INDEPENDIENTE por sus gestiones autónomas e independientes, sin perjuicio de que las condiciones específicas de cada concurso o premio se determinen en los anuncios correspondientes de la EMPRESA.
Las partes acuerdan expresamente que la EMPRESA puede reemplazar o revisar el plan de compensación incluido en el Manual del Vendedor Independiente periódicamente. En caso de que EL VENDEDOR INDEPENDIENTE no acepte
el reemplazo o modificación del plan de compensaciones incluido en el Manual del Vendedor Independiente, documento que en todo caso hace parte del presente contrato incluyendo sus versiones futuras, este contrato
terminará de manera automática bajo el entendido de que la terminación se efectuó por el mutuo acuerdo de las partes. PARÁGRAFO PRIMERO: Todo pago a realizar con motivo del presente contrato, se le aplicarán sin
falta las normas jurídicas, comerciales, contables, tributarias y demás sin limite, sean estas, nacionales, locales, entre otras, aplicables al efecto del pago, y EL VENDEDOR INDEPENDIENTE se obliga a cumplirlas
sin condición alguna. No se podrá realizar pago alguno por motivo de este contrato, cuando no se cumplan estas condiciones. No hay mora bajo ninguna circunstancia de la EMPRESA, si el VENDEDOR INDEPENDIENTE no
cumple con dichos requisitos para hacer un pago debido de la obligación. PARÁGRAFO SEGUNDO: EL VENDEDOR INDEPENDIENTE, debe emitir los documentos soportes, y demás que se indique, para justificar su pago debido,
en cada periodo mensual, por lo tanto, en un máximo de cinco (5) días siguientes al vencimiento del anterior periodo, debe radicar todos los documentos y demás que le exija la EMPRESA, para proceder con el pago
debido. Sin que entregue la documental pertinente que se le solicite, no habrá mora. PARÁGRAFO TERCERO: Se entiende por las partes, y así lo aceptan sin condiciones, que las comisiones, remuneraciones, premios y
demás erogaciones sin limitante, que se deriven del presente contrato, o se paguen al VENDEDOR INDEPENDIENTE, va incluido en valor del IVA. SÉPTIMA: PROHIBICIÓN DE ACTOS CONSTITUTIVOS DE COMPETENCIA DESLEAL EL
VENDEDOR INDEPENDIENTE se compromete a que ni durante la duración del contrato ni después de su terminación, realizará actuaciones o conductas constitutivas de actos de competencia desleal o restrictivos de la
competencia en contra de EMPRESA, de otros vendedores independientes o de cualquier empresa y por ende, entre otros, se abstendrá de realizar actos de desviación de clientela o nuevos vendedores independientes de
la red, uso no autorizado de la Información Confidencial para fines distintos al contrato, así como de cualquier otra información constitutiva de secretos industriales de EMPRESA, inducción de ruptura contractual
a proveedores, clientes u otros vendedores independientes y de realizar cualquier otra conducta que pudiera ser contraria a las sanas prácticas comerciales o de competencia leal. Así mismo, EL VENDEDOR INDEPENDIENTE
debe abstenerse de realizar acuerdos con vendedores de otras compañías sobre precios de productos, reparticiones del mercado, de clientes o cualquier otra conducta que sea restrictiva de la competencia. PARÁGRAFO
PRIMERO: EL VENDEDOR INDEPENDIENTE reconoce y acepta que cualquier uso indebido que éste haga de la Información Confidencial durante y aún después de la terminación del contrato, generará un grave perjuicio en
contra de los intereses de la EMPRESA. Por lo tanto, ante cualquier uso indebido que haga EL VENDEDOR INDEPENDIENTE de la Información Confidencial o en caso que incurra en actos de competencia desleal en contra de
la EMPRESA o de terceros, o actos restrictivos de la competencia, EL VENDEDOR INDEPENDIENTE pagará a la EMPRESA de inmediato y en calidad de penalidad el equivalente a CINCUENTA (50) SALARIOS MÍNIMOS LEGALES
MENSUALES VIGENTES, por motivo de los daños y perjuicios causados. OCTAVA: VIGENCIA El presente contrato tiene un término de duración de un (1) año contados desde la fecha de su suscripción por parte de EL
VENDEDOR INDEPENDIENTE. El contrato podrá prorrogarse automáticamente, y conforme a las condiciones del Manual del Vendedor Independiente, por periodos iguales, a menos que alguna de las partes envíe una
comunicación por escrito a la otra señalando su intención de dar por terminado el contrato en los términos establecidos en la cláusula novena de este contrato. NOVENA: TERMINACIÓN Constituye justa causa para la
terminación inmediata del contrato por parte de la EMPRESA, sin necesidad de aviso previo, la ocurrencia de cualquiera de los siguientes eventos: 9.1. La realización y/u omisión por parte de EL VENDEDOR
INDEPENDIENTE de una cualquiera de las conductas descritas en las cláusulas tercera y séptima de este contrato, entre otras aplicables, según sea el caso o la violación de una cualquiera de las obligaciones
contenidas en cualquier contenido del presente contrato. 9.2. Por haberse comprobado que EL VENDEDOR INDEPENDIENTE ha incurrido en actos contrarios a la ley en desarrollo de sus actividades de comercialización,
ofrecimiento y venta de los productos de la EMPRESA, y en general en el desarrollo de las actividades derivadas del presente contrato. 9.3. Por la presentación de dos o más quejas por parte de clientes u otros
vendedores independientes de la EMPRESA en relación con el comportamiento, desempeño o trato por parte de EL VENDEDOR INDEPENDIENTE. 9.4. Por la cesión que EL VENDEDOR INDEPENDIENTE haga del presente contrato,
sin contar con la previa y expresa autorización de la EMPRESA PARÁGRAFO PRIMERO: Cualquiera de las partes podrá dar por terminado el presente contrato, en cualquier momento, dando aviso a la otra parte con una
antelación no menor de treinta (30) días a la fecha en que se hará efectiva la terminación, sin que por ello se cause obligación de indemnización alguna a cargo de la parte que así procediese. Si se presenta
incumplimiento de alguna de las partes de las obligaciones previstas en este contrato o de los compromisos incluidos en la Guía de Negocio, o en los códigos de ética de la DSA o ACOVEDI, que no se subsane en el
plazo de treinta (30) días calendario contados desde la fecha de recibo de la solicitud escrita de la otra parte, se considerará que existe incumplimiento grave y, por tanto, justa causa para que la parte cumplida
dé por terminada la relación contractual de manera unilateral e inmediata, sin que haya lugar al pago de indemnización alguna. DÉCIMA: REGLAS A OBSERVARSE A LA TERMINACIÓN DEL CONTRATO 10.1. Con la ocurrencia
de cualquiera de las causales señaladas en la cláusula novena, entre otras aplicables, la EMPRESA podrá retener cualesquiera o todos los fondos pendientes de pago a EL VENDEDOR INDEPENDIENTE, a efectos de permitir
la recopilación de información y tramitar el pago por compensación de cualquier obligación pendiente a favor de la EMPRESA. EL VENDEDOR INDEPENDIENTE cooperará y tomará cualquier medida adicional que la EMPRESA
pueda solicitarle para llevar a cabo una terminación ordenada, pacífica y sin mayores contratiempos del presente contrato. 10.2. En caso de que la autoridad competente estime el presente contrato como de una
naturaleza distinta a la de AFILIACIÓN A LA RED Y MERCADEO MULTINIVEL, EL VENDEDOR INDEPENDIENTE desde ya renuncia expresamente a las prestaciones del artículo 1324 del Código de Comercio. En caso de terminación
del contrato, cualquiera sea la causa, EL VENDEDOR INDEPENDIENTE deberá devolver a la EMPRESA todos los materiales publicitarios y demás documentación que se le haya suministrado, productos en consignación y/o
demostración, así como eliminar y dejar de usar en cualquier medio las marcas, nombres, dibujos, signos distintivos y la propiedad intelectual de la misma. DÉCIMA PRIMERA: DISPOSICIONES GENERALES 11.1. Este contrato
regula integramente las relaciones comerciales entre las partes, razón por la cual deja sin valor y efecto cualquier otro entendimiento, acto o acuerdo, escrito o verbal, ejecutado, celebrado o firmado con
anterioridad. 11.2. La EMPRESA no será responsable ante EL VENDEDOR INDEPENDIENTE por daño alguno, incluyendo, pero sin limitarse a daño emergente, expectativa de ganancia y lucro cesante, como consecuencia de
la aceptación o rechazo de determinada solicitud de productos. 12. RESOLUCIÓN DE CONTROVERSIAS Cualquier controversia que surja con ocasión del presente contrato, será dirimida conforme a la normatividad vigente
en la República de Colombia y por los jueces colombianos, sin perjuicio de que las partes directamente puedan conciliar o transigir la materia objeto de controversia. DÉCIMA SEGUNDA: AUTORIZACIÓN DE DATOS
PERSONALES Con la suscripción del presente acuerdo de voluntades, el VENDEDOR INDEPENDIENTE, manifiesta expresamente que comprende, acepta y autoriza a la EMPRESA, para usar y/o transferir y/o ceder la
información personal contenida en el presente contrato, así como toda la información que se genere en desarrollo de sus actividades dentro de la red de mercadeo, a terceras personas, incluyendo otros VENDEDORES
INDEPENDIENTES, de acuerdo con la Política de Tratamiento de Datos Personales y con las normas que regulan la materia. DÉCIMA TERCERA: OFICINA ABIERTA AL PÚBLICO De acuerdo a lo exigido por la ley la EMPRESA
tendrá una oficina abierta al público de carácter permanente ubicada en la carrera 13 No. 16 Norte – 45 de la ciudad de Armenia, Quindío. DÉCIMA CUARTA: CONFIDENCIALIDAD Debido a la naturaleza del presente
contrato, LA EMPRESA y EL VENDEDOR INDEPENDIENTE se obligan a mantener en absoluta reserva toda la información, documentos y/o copias relacionadas con la realización, celebración o ejecución de este contrato,
en especial respecto de la información que se archive o curse en los medios y equipos a cargo del VENDEDOR, así como respecto de los códigos y procedimientos de acceso, seguridad y demás garantías ofrecidas para
el ingreso a las instalaciones de la EMPRESA. No obstante, lo anterior, ambas partes convienen expresamente que toda información a la que se tenga acceso o se reciba en virtud del presente contrato se considera
confidencial y, por lo tanto, divulgarla o ponerla en conocimiento de terceros por cualquier medio, puede ocasionar daños en la conducción de sus respectivos negocios. La parte que incumpla el deber de
confidencialidad debe indemnizar los perjuicios correspondientes que ocasione. Si como consecuencia del contrato, el VENDEDOR INDEPENDIENTE, sus respectivos empleados, asesores, consultores, revisores,
contratistas o subcontratistas, entre otros, tienen acceso a la información y documentos confidenciales de la EMPRESA, quien recibe la información confidencial se compromete a guardar absoluta reserva, en
cumplimiento de lo cual se obliga a: 14.1. No emplear la información y/o los documentos para el desarrollo de actividades diversas a las que constituyen el objeto del presente contrato. 14.2. Restringir el
conocimiento de la información confidencial que se suministre únicamente a las personas que estrictamente deban conocerla para el cabal cumplimiento del contrato. 14.3. Comprometerse a devolver a la finalización
del contrato los documentos con carácter confidencial entregados por la EMPRESA o en su defecto, certificar que los mismos han sido destruidos o eliminados, salvo acuerdo distinto. PARÁGRAFO: Sin perjuicio de lo
anterior, la obligación de confidencialidad no existirá en los siguientes eventos: 14.4. Respecto de aquellas informaciones, documentos o materiales que legítimamente hubieren sido puestos en conocimiento o
divulgados al público con anterioridad a la celebración del presente contrato. 14.5. Respecto de la información, documentos, materiales y en general cualquier tipo de información que por razones de orden legal o
por disposición expresa de quienes ejerzan alguna clase de propiedad sobre ellos, pasen a ser de dominio público o hayan sido divulgados al público. 14.6. Por mutuo acuerdo entre las partes. DÉCIMA QUINTA:
TERMINACIÓN DEL CONTRATO El Contrato terminará por las siguientes causales: 15.1. Mutuo consenso de las partes expresado por escrito. 15.2. Cumplimiento del objeto del Contrato. 15.3. Sentencia judicial o acto
jurídico de iguales efectos que así lo determine. 15.4. Por parte de la EMPRESA, en cualquier momento de ejecución del contrato, dando aviso con cinco (5) días comunes de antelación a su terminación, cancelando
el valor pendiente de pago, solo lo porcentualmente ejecutado a partir del momento de inicio de ejecución del contrato, sin que de ninguna manera se genere indemnización de perjuicios o sanciones de ningún tipo.
15.5. Las partes acuerdan que la EMPRESA podrá declarar anticipada e inmediatamente resuelto el contrato, con justa causa, en cualquiera de los siguientes supuestos: a) Incumplimiento del VENDEDOR de cualquiera
de las obligaciones contraídas en virtud de este contrato, documentos anexos, y las necesariamente conexas y de ley; b) Cualquier acción u omisión del VENDEDOR que afecte gravemente los intereses de la EMPRESA;
c) La declaratoria de concordato o figuras de efectos similares del VENDEDOR, que ponga en riesgo la ejecución del presente contrato; d) La cesión o subcontratación total o parcial del presente contrato por parte
del VENDEDOR sin la autorización previa y por escrito de la EMPRESA; e) Por comprobarse falsificación de los productos, inexactitud, ocultación, o falsedad sin necesidad de declaración judicial, en los documentos,
datos e información que debe suministrar el VENDEDOR en virtud de este contrato; f) Suspensión o parálisis de los trabajos por parte del VENDEDOR, excepto en caso de fuerza mayor debidamente acreditada;
g) Falta de seguimiento de los procesos, descuido o contravención de las instrucciones de la EMPRESA relativas a la ejecución del contrato. La terminación unilateral del contrato de que trata este numeral se
formalizará mediante comunicación que por escrito dirija el CONTRATANTE al CONTRATISTA en la que se relacionará la causa que motiva tal determinación. 15.6. Justa causa por parte del VENDEDOR si se presentare
alguna de las siguientes causales: a) Incumplimiento de la EMPRESA de las obligaciones consignadas en el presente contrato y las necesariamente conexas y de ley; b) Cualquier acción u omisión que afecte
gravemente los intereses del VENDEDOR. DÉCIMO SEXTA: CUMPLIMIENTO POLÍTICAS SAGRILAFT. EL VENDEDOR INDEPENDIENTE se compromete a dar cumplimiento con todas y cada una de las políticas enmarcadas en el Sistema
de Autocontrol y Gestión del Riesgo de Lavado de Activos y Financiación del Terrorismo – SAGRILAFT, con base en el Manual de SAGRILAFT, al Reglamento Interno de Trabajo y demás políticas internas empresariales;
así como las disposiciones señaladas en la Circular 100-000016 de septiembre de 2021. Lo anterior basado en las diferentes capacitaciones que serán impartidas por parte del Oficial de Cumplimiento de la Empresa.
Adicional, el VENDEDOR INDEPENDIENTE se compromete a reportar al jefe inmediato, las operaciones inusuales o sospechosas que detecte, junto con las pruebas que lo soporten.
DÉCIMA SÉPTIMA: DECLARACIÓN DE ORIGEN DE FONDOS. Las partes declaran que todas las actividades del objeto contractual y los recursos de estos, no se encuentran relacionados o provienen de actividades ilícitas;
particularmente, de lavado de activos o financiación del terrorismo. En todo caso, si durante el plazo de vigencia del convenio se encontraren en alguna de las partes, dudas razonables sobre sus operaciones,
así como el origen de sus activos y/o que alguna de ellas, llegare a resultar inmiscuido en una investigación de cualquier tipo (penal, civil administrativa, etc.) relacionada con actividades ilícitas, lavado de
dinero o financiamiento del terrorismo, o fuese incluida en las listas internacionales vinculantes para Colombia, de conformidad con el derecho internacional (listas de naciones unidas- ONU), en listas de la OFAC
o Clinton, etc., la parte libre de reclamo tendrá derecho de terminar unilateralmente el convenio sin que por este hecho, esté obligado a indemnizar ningún tipo de perjuicio a la parte que lo generó. De igual
manera, Las Partes se obligan a mantenerse indemnes frente a cualquier reclamación o investigación al respecto pudiera ser adelantada en contra de cualquiera de ellas, para lo cual, Las Partes declaran, bajo la
gravedad de juramento, que la totalidad de sus activos y sus actividades son totalmente lícitas.
DÉCIMA OCTAVA: FIRMA DIGITAL. Este contrato está autorizado a ser firmado de manera digital, será enviado por correo electrónico directamente al VENDEDOR INDEPENDIENTE y firmarlo digitalmente para ser enviado
posteriormente. PARÁGRAFO PRIMERO: Este contrato puede ser firmado electrónicamente desde computador, Tablet, teléfono móvil o mediante un archivo con la firma, por ello este contrato no requiere ser impreso
y firmado a mano alzada.
En señal de conformidad las partes suscriben el presente contrato en DOS (2) EJEMPLARES con la misma validez jurídica cada uno, a los {{ now()->format('d') }} días del mes {{ now()->format('m') }} del año {{ now()->format('Y') }} en {{ $brand->getTranslation('name', 'es') }}.
</p>
</div>

{{-- NIT --}}
<div style="font-size:6.5pt; font-weight:bold; margin-top:4px;">
    {{ $brand->getTranslation('name', 'es') }}@if($brand->nit)<br>
    N.I.T. No. {{ $brand->nit }}@endif
</div>

{{-- ══════════════════ DATOS V.J. + FIRMAS ══════════════════ --}}
<div style="margin-top:6px; border:1.5px solid #999; border-radius:5px; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; height:110px;">
        <tr>
            {{-- Columna izquierda: campos de datos --}}
            <td style="width:52%; vertical-align:top; padding:5px 8px; padding-top:0;">
                <div style="font-weight:bold; font-size:7pt; background:{{ $primaryColor }}; color:#fdfdfb; padding:4px 6px; margin-bottom:5px; margin-top:6px; display:inline-block; border-radius:0 16px 16px 0; letter-spacing:0.3px; margin-left:-8px;">
                    DATOS DEL V.J.
                </div>
                <div style="font-size:6.8pt;">
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Nombre:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Documento N°:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Fecha de Nacimiento:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Fecha de Ingreso:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Dirección:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Email:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                    <div style="display:flex; align-items:flex-end; padding:1.5px 0;"><span style="white-space:nowrap;">Cel./Tel.:&nbsp;</span><span style="flex:1; border-bottom:0.7px solid #333; min-height:7px;"></span></div>
                </div>
            </td>
            {{-- Columna derecha: firmas + recuadro sello --}}
            <td style="width:48%; vertical-align:middle; padding:5px 6px;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="border:none; vertical-align:middle;">
                            {{-- Firma y sello de recibido --}}
                            <div style="text-align:center; margin-top:34px;">
                                <div style="border-top:0.7px solid #333; width:82%; margin:0 auto 2px auto;"></div>
                                <div style="font-size:7.5pt; font-weight:bold; letter-spacing:0.2px;">FIRMA Y SELLO DE RECIBIDO</div>
                            </div>
                            {{-- Firma V.J. --}}
                            <div style="text-align:center; margin-top:40px;">
                                <div style="border-top:0.7px solid #333; width:82%; margin:0 auto 2px auto;"></div>
                                <div style="font-size:7.5pt; font-weight:bold; letter-spacing:0.2px;">FIRMA V.J</div>
                            </div>
                        </td>
                        {{-- Recuadro sello --}}
                        <td style="border:none; vertical-align:top; width:26%; padding-left:4px;">
                            <div style="border:1px solid #aaa; width:100%; height:108px;"></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
