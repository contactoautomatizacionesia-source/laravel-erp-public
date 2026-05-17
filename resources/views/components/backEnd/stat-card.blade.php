{{--
    Stat Card — tarjeta de métrica reutilizable para cualquier módulo.

    Props:
        id         (string)   — id del elemento HTML que muestra el valor (para actualizarlo con JS)
        value      (string)   — valor estático. Si se usa `id` con JS se puede omitir (queda '—')
        label      (string)   — texto descriptivo debajo del valor
        color      (string)   — clase de color Bootstrap: primary | secondary | success |
                                danger | warning | info | dark | purple
                                Aplica sobre el valor y el ícono. Default: 'secondary'
        icon       (string)   — clase del ícono (ti-* / fa fa-*). Opcional.
        cols       (string)   — clases de columna Bootstrap. Default: 'col-md-3 col-sm-6'

    Uso básico (valor estático):
        <x-backEnd.stat-card label="Pendientes" value="42" color="warning" icon="ti-time" />

    Uso con JS (valor dinámico vía id):
        <x-backEnd.stat-card id="metric-pending" label="Pendientes" color="warning" icon="ti-time" />
        // luego en JS: $('#metric-pending').text(data.pending);
--}}
@props([
    'id'    => null,
    'value' => '—',
    'label' => '',
    'color' => 'secondary',
    'icon'  => null,
    'cols'  => 'col-lg-3 col-sm-6',
])

@php
    // Bootstrap warning (#ffc107) tiene muy bajo contraste sobre blanco.
    // Se sustituye por un ámbar oscuro legible. El resto usa text-{color} estándar.
    $isWarning   = $color === 'warning';
    $colorStyle  = $isWarning ? 'color:#b8860b !important;' : '';
@endphp

<div class="{{ $cols }} mb-10">
    <div class="form-card">
        <div class="d-flex justify-content-between align-items-center">
            <p class="mb-0">{{ $label }}</p>
            @if($icon)
                <span class="{{ $icon }} stat-icon fs-20 {{ $isWarning ? '' : "text-{$color}" }}"
                      @if($isWarning) style="{{ $colorStyle }}" @endif></span>
            @endif
        </div>
        <h2 class="mt-2 mb-0 text-dark-green"
            style="font-size:1.5rem; font-weight:500;"
            @if($id) id="{{ $id }}" @endif>
            {{ $value }}
        </h2>
    </div>
</div>
