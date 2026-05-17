<div class="tabs-container-wrapper">
    {{-- Lista de Pestañas --}}
    <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-0 custom_nav_details" id="pills-tab">
        @php
            $tabs = [
                ['id' => 'score', 'label' => __('amazy.score_summary'), 'icon' => 'ti-cup', 'active' => true],
                ['id' => 'network', 'label' => __('tree.network'), 'icon' => 'ti-share', 'active' => false],
                ['id' => 'basic', 'label' => __('amazy.step_basic_data'), 'icon' => 'ti-id-badge', 'active' => false],
                ['id' => 'additional', 'label' => __('amazy.step_additional_info'), 'icon' => 'ti-map-alt', 'active' => false],
                ['id' => 'documents', 'label' => __('amazy.step_documents'), 'icon' => 'ti-files', 'active' => false],
                ['id' => 'bank', 'label' => __('amazy.step_bank_info'), 'icon' => 'ti-credit-card', 'active' => false],
                ['id' => 'work', 'label' => __('amazy.step_work_pep'), 'icon' => 'ti-briefcase', 'active' => false],
                ['id' => 'financial', 'label' => __('amazy.step_financial'), 'icon' => 'ti-wallet', 'active' => false],
                ['id' => 'foreign', 'label' => __('amazy.step_foreign'), 'icon' => 'ti-world', 'active' => false],
                ['id' => 'tax', 'label' => __('amazy.step_tax'), 'icon' => 'ti-agenda', 'active' => false],
                ['id' => 'entrepreneur', 'label' => __('amazy.step_entrepreneur_data'), 'icon' => 'ti-crown', 'active' => false],
                ['id' => 'kyc_history', 'label' => __('Historial KYC'), 'icon' => 'ti-time', 'active' => false],
            ];
        @endphp
    
        @foreach($tabs as $tab)
            <li class="nav-item" >
                <a class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                    id="pills-{{ $tab['id'] }}-tab"
                    data-toggle="pill"
                    href="#pills-{{ $tab['id'] }}"
                    role="tab"
                    title="{{ $tab['label'] }}"
                    aria-controls="pills-{{ $tab['id'] }}"
                    aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                    
                    {{-- Icono visible en móvil --}}
                    <i class="{{ $tab['icon'] }} nav-icon"></i>
                    
                    {{-- Texto visible en escritorio --}}
                    <span class="nav-text">{{ $tab['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- NUEVO: Label de Sección Activa (Solo visible en Móvil) --}}
    <div class="mobile-section-label d-none">
        <span id="mobile-active-text">{{ __('amazy.score_summary') }}</span>
    </div>
</div>

{{-- Script para actualizar el texto en móvil --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Escuchar el evento nativo de cambio de tab de Bootstrap
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            // e.target es la pestaña recién activada
            // Buscamos el texto dentro del span .nav-text (que está oculto visualmente en mobile pero existe en el DOM)
            var newLabel = $(e.target).find('.nav-text').text();
            
            // Actualizamos el label inferior con efecto fade
            var label = $('#mobile-active-text');
            label.fadeOut(100, function() {
                $(this).text(newLabel).fadeIn(200);
            });
        });
    });
</script>
@endpush

<style>
    /* Contenedor principal */
    .tabs-container-wrapper {
        margin-bottom: 25px;
        padding-bottom: 10px; /* Reduje un poco para pegar más el texto al borde */
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        position: relative;
    }

    /* ESTILO ESCRITORIO */

    
    .custom_nav_details .nav-link {
        color: var(--text_black);
        background: #fff;
        border: solid var(--border_color);
        border-width: 0 0 2px 0;
        padding: 3px 15px;
        font-size: 13px;
        font-weight: 400;
        white-space: nowrap;
        transition: all 0.2s ease;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-icon {
        display: none;
        font-size: 14px;
    }

    .custom_nav_details .nav-link:hover,
    .custom_nav_details .nav-link.active {
        border-color: var(--base_color);
        background-color: #fff;
        color: var(--base_color);
    }

    /* =========================================
        ESTILO MÓVIL (< 768px)
    ========================================= */
    @media (max-width: 1110px) {
        .custom_nav_details {
            overflow-x: auto;
            padding-bottom: 5px;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 5px; /* Espacio entre círculos y el texto nuevo */
        }

        .custom_nav_details::-webkit-scrollbar { display: none; }
        .custom_nav_details { -ms-overflow-style: none; scrollbar-width: none; }

        /* ESTILO DEL NUEVO LABEL */
        .mobile-section-label {
            display: block;
            text-align: center;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 5px;
            height: 20px; /* Altura fija para evitar saltos si no hay texto */
        }
    }
</style>
