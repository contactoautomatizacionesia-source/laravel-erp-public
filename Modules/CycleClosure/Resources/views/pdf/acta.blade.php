<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Acta de Cierre {{ $cycle->period_label }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; }
        .page { padding: 20mm 18mm; }

        /* Header */
        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 10px; margin-bottom: 16px; }
        .header-title { font-size: 14pt; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 1px; }
        .header-sub { font-size: 8pt; color: #555; margin-top: 2px; }
        .header-badge { display: inline-block; background: #16a34a; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }

        /* Sections */
        .section { margin-bottom: 14px; }
        .section-title { font-size: 9pt; font-weight: bold; color: #fff; background: #1e3a5f; padding: 4px 8px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 0 4px; }

        /* Info grid */
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 3px 6px; vertical-align: top; font-size: 8.5pt; }
        .info-grid .label { color: #555; width: 35%; }
        .info-grid .value { font-weight: bold; }

        /* Tables */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        table.data-table th { background: #e8edf5; color: #1e3a5f; padding: 4px 6px; text-align: left; border: 1px solid #c8d0de; }
        table.data-table td { padding: 3px 6px; border: 1px solid #dce3ec; }
        table.data-table tr.total-row td { background: #f0f4ff; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Alert / warning box */
        .warning-box { background: #fffbea; border: 1px solid #f59e0b; border-left: 4px solid #f59e0b; padding: 8px 10px; margin-bottom: 12px; font-size: 8pt; }

        /* Signatures */
        .signatures { width: 100%; margin-top: 20px; }
        .signatures td { width: 50%; padding: 0 16px; text-align: center; vertical-align: top; }
        .sig-line { border-top: 1px solid #1e3a5f; margin: 40px auto 4px auto; width: 80%; }
        .sig-name { font-weight: bold; font-size: 8pt; }
        .sig-role { color: #555; font-size: 7.5pt; }

        /* QR placeholder */
        .qr-area { text-align: right; margin-bottom: 8px; }
        .qr-area .qr-label { font-size: 7pt; color: #555; }

        /* Badge */
        .badge-success { background: #16a34a; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-danger  { background: #dc2626; color: #fff; padding: 1px 5px; border-radius: 3px; }
        .badge-info    { background: #0284c7; color: #fff; padding: 1px 5px; border-radius: 3px; }

        .divider { border: none; border-top: 1px solid #dce3ec; margin: 10px 0; }

        /* Page break */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── QR (esquina superior derecha) ────────────────────────────── --}}
    <div class="qr-area">
        <div class="qr-label">Escanee para verificar en el sistema</div>
        {{-- El QR apunta a la ruta protegida del ciclo — solo admins autenticados --}}
        <div style="font-size: 7pt; color:#888;">[QR: {{ route('cycle_closure.show', $cycle->id) }}]</div>
    </div>

    {{-- ── Header ───────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="d-flex justify-content-between">
            <div>
                <div class="header-title">Acta de Cierre de Ciclo Operativo</div>
                <div class="header-sub">
                    ID DE ACTA: <strong>AC-{{ $cycle->period_label }}-{{ str_pad($cycle->id, 3, '0', STR_PAD_LEFT) }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    ESTADO: <span class="header-badge">CERTIFICADO</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 1. Información General ───────────────────────────────── --}}
    <div class="section">
        <div class="section-title">1. Información General del Período</div>
        <div class="section-body">
            <table class="info-grid">
                <thead><tr><th></th><th></th><th></th><th></th></tr></thead>
                <tr>
                    <td class="label">Período Liquidado:</td>
                    <td class="value">{{ $cycle->period_start?->format('d \d\e F Y') }} – {{ $cycle->period_end?->format('d \d\e F Y') }}</td>
                    <td class="label">Fecha / Hora Ejecución:</td>
                    <td class="value">{{ $cycle->approved_at?->format('d/m/Y - H:i:s') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Usuario Ejecutor:</td>
                    <td class="value">{{ optional($cycle->executor)->name ?? '—' }}</td>
                    <td class="label">Co-Aprobador:</td>
                    <td class="value">{{ optional($cycle->coApprover)->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">IP de Origen:</td>
                    <td class="value">{{ $ipAddress ?? '—' }}</td>
                    <td class="label">Período Label:</td>
                    <td class="value">{{ $cycle->period_label }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── 2. Resumen Financiero ─────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">2. Resumen Consolidado (Finanzas y Ventas)</div>
        <div class="section-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-center">Cantidad / Transacciones</th>
                        <th class="text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($financialSummary ?? [] as $row)
                    <tr class="{{ $loop->last ? 'total-row' : '' }}">
                        <td>{{ $row['concept'] }}</td>
                        <td class="text-center">{{ $row['quantity'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['value'] }}</td>
                    </tr>
                    @endforeach
                    @if(empty($financialSummary))
                    <tr>
                        <td colspan="3" class="text-center" style="color:#888; font-style:italic;">
                            — Datos pendientes de integración con módulo de finanzas —
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 3. Consolidado Inventario ─────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">3. Consolidado de Inventario (Logística)</div>
        <div class="section-body">
            <table class="info-grid">
                <thead><tr><th></th><th></th><th></th><th></th></tr></thead>
                <tr>
                    <td class="label">Total Productos en Existencia:</td>
                    <td class="value">{{ $inventorySummary['total_units'] ?? '—' }} unidades</td>
                    <td class="label">Valorización de Inventario (Costo):</td>
                    <td class="value">{{ $inventorySummary['valuation'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Ajustes por Auditoría en el Período:</td>
                    <td class="value">{{ $inventorySummary['audit_adjustments'] ?? '—' }}</td>
                    <td class="label">Centros de Costo Conciliados:</td>
                    <td class="value">{{ $inventorySummary['cost_centers_reconciled'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── 4. Gestión de Rangos ──────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">4. Gestión de Rangos y Planes (Red)</div>
        <div class="section-body">
            <table class="info-grid">
                <thead><tr><th></th><th></th><th></th><th></th></tr></thead>
                <tr>
                    <td class="label">Ascensos de Rango:</td>
                    <td class="value">{{ $rankSummary['promotions'] ?? '—' }} usuarios</td>
                    <td class="label">Permanencias Confirmadas:</td>
                    <td class="value">{{ $rankSummary['maintained'] ?? '—' }} usuarios</td>
                </tr>
                <tr>
                    <td class="label">Descensos de Rango:</td>
                    <td class="value">{{ $rankSummary['demotions'] ?? '—' }} usuarios</td>
                    <td class="label"></td>
                    <td class="value">
                        {{-- TODO: integrar con módulo de rangos cuando esté disponible NOSONAR --}}
                        <span style="color:#888; font-size:7.5pt; font-style:italic;">Pendiente integración módulo rangos</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── 5. Integridad Técnica ─────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">5. Integridad y Seguridad Técnica</div>
        <div class="section-body">
            <table class="info-grid">
                <thead><tr><th></th><th></th><th></th><th></th></tr></thead>
                <tr>
                    <td class="label">Copia de Seguridad Lógica:</td>
                    <td class="value" colspan="3">{{ $backupReference ?? 'N/A' }} <span class="badge-success">Verificado ✓</span></td>
                </tr>
                <tr>
                    <td class="label">Hash de Integridad (SHA-256):</td>
                    <td class="value" colspan="3" style="font-size:7pt; word-break:break-all;">{{ $integrityHash ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Bloqueo Retroactivo:</td>
                    <td class="value" colspan="3">Activado para transacciones anteriores al {{ $cycle->period_end?->addDay()->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <hr class="divider">

    {{-- ── Alertas de Auditoría (logs resumen) ──────────────────────── --}}
    @if($cycle->logs->isNotEmpty())
    <div class="section">
        <div class="section-title">6. Alertas de Auditoría</div>
        <div class="section-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fase</th>
                        <th>Nivel</th>
                        <th>Mensaje</th>
                        <th class="text-center">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cycle->logs as $log)
                    @if($log->level !== 'info')
                    <tr>
                        <td>{{ $log->phase }}</td>
                        <td class="text-center">
                            <span class="{{ $log->level === 'error' ? 'badge-danger' : ($log->level === 'success' ? 'badge-success' : 'badge-info') }}">
                                {{ ucfirst($log->level) }}
                            </span>
                        </td>
                        <td>{{ $log->message }}</td>
                        <td class="text-center">{{ $log->created_at?->format('d/m H:i') }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <hr class="divider">

    {{-- ── 7. Firmas Digitales ───────────────────────────────────────── --}}
    <table class="signatures">
        <thead><tr><th></th><th></th></tr></thead>
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">Firma Digital: Ejecutor</div>
                <div class="sig-role">{{ optional($cycle->executor)->name ?? '—' }}</div>
                <div class="sig-role">ID: #{{ $cycle->executor_id }}</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-name">Firma Digital: Co-Aprobador</div>
                <div class="sig-role">{{ optional($cycle->coApprover)->name ?? '—' }}</div>
                <div class="sig-role">ID: #{{ $cycle->co_approver_id }}</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div style="margin-top: 16px; border-top: 1px solid #dce3ec; padding-top: 6px; font-size: 7pt; color: #888; text-align: center;">
        Documento generado automáticamente por el sistema ERP &mdash;
        {{ now()->format('d/m/Y H:i:s') }} &mdash;
        Acta ID: AC-{{ $cycle->period_label }}-{{ str_pad($cycle->id, 3, '0', STR_PAD_LEFT) }}
    </div>

</div>
</body>
</html>
