@php
    $showKycWarning = false;
    $kycDaysRemaining = 0;

    if(auth()->check() && auth()->user()->role && auth()->user()->role->type === 'customer') {
        $ttlSetting = \Modules\GeneralSetting\Entities\ParameterSetting::where('slug', 'entrepreneur-data-ttl')
                        ->where('is_active', 1)
                        ->first();

        if ($ttlSetting && $ttlSetting->value_limit > 0) {
            $lastUpdate = auth()->user()->data_verified_at ?? auth()->user()->created_at;
            
            $expirationDate = \Carbon\Carbon::parse($lastUpdate)->addMonths($ttlSetting->value_limit);
            $kycDaysRemaining = now()->diffInDays($expirationDate, false);
            
            if ($kycDaysRemaining <= 15 && $kycDaysRemaining >= 0) {
                $showKycWarning = true;
            }
        }
    }
@endphp

@if($showKycWarning)
<div class="container mt-4 mb-2 kyc-alert-container">
    <div class="kyc-smart-banner">
        
        {{-- 1. Icono de Urgencia --}}
        <div class="kyc-banner-icon">
            <i class="ti-time"></i>
        </div>

        {{-- 2. Contenido del Mensaje --}}
        <div class="kyc-banner-content">
            <h6>{{ __('general_settings.attention') }}</h6>
            <p>
                {!! __('general_settings.kyc_warning', [
                    'days' => '<strong>' . ceil($kycDaysRemaining) . ' ' . __('general_settings.days') . '</strong>'
                ]) !!}
            </p>
        </div>

        {{-- 3. Acciones (Botón + Cerrar) --}}
        <div class="kyc-banner-actions">
            <a href="{{ route('kyc.update.index') }}" class="kyc-action-btn">
                {{ __('general_settings.update_now') }}
                <i class="ti-arrow-right ml-1"></i>
            </a>
            <button type="button" class="kyc-close-btn" data-dismiss="alert" aria-label="Close">
                <i class="ti-close"></i>
            </button>
        </div>

    </div>
</div>
@endif

<style>
    /* Animación de entrada fluida */
    @keyframes slideDownFade {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .kyc-alert-container {
        animation: slideDownFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Estructura principal del Banner */
    .kyc-smart-banner {
        display: flex;
        align-items: center;
        background: #fff5f5; /* Fondo rojo muy suave */
        border: 1px solid #fed7d7;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 4px 15px rgba(229, 62, 62, 0.08);
        position: relative;
        transition: opacity 0.4s ease, transform 0.4s ease;
        overflow: hidden;
    }

    /* Decoración lateral */
    .kyc-smart-banner::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--toolkit_corporative-red-color, #e53e3e);
    }

    /* Icono */
    .kyc-banner-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: rgba(229, 62, 62, 0.1);
        color: var(--toolkit_corporative-red-color, #e53e3e);
        border-radius: 50%;
        font-size: 24px;
        margin-right: 20px;
        flex-shrink: 0;
    }

    /* Textos */
    .kyc-banner-content {
        flex-grow: 1;
        padding-right: 20px;
    }

    .kyc-banner-content h6 {
        margin: 0 0 4px 0;
        font-weight: 700;
        color: #9b2c2c; /* Texto oscuro para buen contraste */
        font-size: 15px;
    }

    .kyc-banner-content p {
        margin: 0;
        font-size: 14px;
        color: #c53030;
        line-height: 1.4;
    }

    .kyc-banner-content p strong {
        font-weight: 800;
        font-size: 15px;
    }

    /* Contenedor de Botones */
    .kyc-banner-actions {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-shrink: 0;
    }

    /* Botón de Acción Principal */
    .kyc-action-btn {
        background: var(--toolkit_corporative-red-color, #e53e3e);
        color: #ffffff !important;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
    }

    .kyc-action-btn:hover {
        background: #c53030;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(229, 62, 62, 0.3);
    }

    /* Botón de cerrar */
    .kyc-close-btn {
        background: transparent;
        border: none;
        color: #fc8181;
        font-size: 16px;
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kyc-close-btn:hover {
        color: #c53030;
    }

    /* Estado oculto para la animación de cierre */
    .kyc-smart-banner.hide {
        opacity: 0;
        transform: translateY(-10px) scale(0.98);
    }

    /* Responsividad */
    @media (max-width: 768px) {
        .kyc-smart-banner {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }
        .kyc-banner-icon {
            margin-bottom: 12px;
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
        .kyc-banner-actions {
            margin-top: 15px;
            width: 100%;
            justify-content: space-between;
        }
        .kyc-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    }
</style>

<script>
    document.querySelectorAll('.kyc-close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const alert = this.closest('.kyc-smart-banner');
            alert.classList.add('hide');
            setTimeout(() => {
                alert.closest('.kyc-alert-container').remove();
            }, 400); // Tiempo alineado con la transición CSS
        });
    });
</script>