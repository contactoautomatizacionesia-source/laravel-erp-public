<x-admin.section class="ign-customer-list position-relative">
    {{-- SECCIÓN 1: Sticky Header --}}
    <div class="bg-white shadow-sm rounded-lg py-3 px-1 px-md-4 mb-3 position-md-sticky" style="top: 20px; z-index: 100;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge_5">
                    <i class="ti-clipboard mr-1"></i> {{ $countCode }}
                </span>
                <span class="text-muted">
                    <i class="ti-time mr-1"></i> {{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge_5">
                    {{ __('inventorycount::messages.products_counted') }}: {{ $countedCount }} / {{ $totalCount }}
                </span>
            </div>
        </div>
    </div>
    <div class="row">
     
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('inventorycount::menu.counts') }}</h3>
                </div>
            </div>
            {{-- Notificaciones Livewire via Toastr --}}
            @if(session()->has('count_warning'))
            <script>
                document.addEventListener('livewire:load', function () {
                    toastr.warning('{{ session('count_warning') }}');
                });
            </script>
            @endif

            @if(!$countStarted)
            {{-- Botón para iniciar el conteo (captura device_info al hacer click) --}}
            <div class="text-center py-5">
                <p class="text-muted mb-4">{{ __('inventorycount::messages.start_count_hint') }}</p>
                <button type="button" class="btn-toolkit btn-primary btn-icon"
                        onclick="startCountWithDeviceInfo(this)"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="ti-check-box mr-2"></i> {{ __('inventorycount::messages.start_count') }}</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin mr-2"></i> {{ __('common.loading') }}...</span>
                </button>
            </div>
            @else

            {{-- SECCIÓN 2: Barra de herramientas --}}
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <div class="input-group" style="max-width: 320px;">
                    <input type="text" class="primary_input_field" wire:model="searchTerm"
                        placeholder="{{ __('inventorycount::messages.search_product') }}">
                </div>
                <button type="button"
                        class="btn-toolkit {{ $filterMode === 'all' && !$searchTerm ? 'btn-secondary' : 'btn-secondary-outline' }}"
                        wire:click="resetFilters">
                    {{ __('inventorycount::messages.filter_all') }}
                </button>
                <button type="button"
                        class="btn-toolkit {{ $filterMode === 'pending' ? 'btn-secondary' : 'btn-secondary-outline' }}"
                        wire:click="$set('filterMode', 'pending')">
                    {{ __('inventorycount::messages.filter_pending') }}
                </button>
            </div>

            {{-- SECCIÓN 3: Tabla dinámica de productos --}}
            <div class="dataTables_wrapper">
                <x-admin.table-container>
                    <div class="table-responsive ign-scrollbar">
                        <table class="table dataTable  table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>{{ __('inventorycount::messages.product') }}</th>
                                    <th style="width: 160px;">{{ __('inventorycount::messages.physical_quantity') }}</th>
                                    <th style="width: 200px;">{{ __('inventorycount::messages.observation_type') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($filteredProducts as $i => $product)
                                @php
                                    $pid  = $product['product_id'];
                                    $name = json_decode($product['product_name'] ?? '{}', true);
                                    $displayName = is_array($name) ? ($name[app()->getLocale()] ?? reset($name) ?? '-') : ($product['product_name'] ?? '-');
                                    $qty  = $quantities[$pid] ?? null;
                                @endphp
                                <tr wire:key="product-{{ $pid }}" class="{{ $qty !== null ? 'table-light' : '' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($product['thumbnail_image_source'])
                                            <img src="{{ showImage($product['thumbnail_image_source']) }}" width="36" height="36"
                                                class="rounded" style="object-fit: cover;" alt="{{ $displayName }}">
                                            @endif
                                            <span>{{ $displayName }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number"
                                            class="primary_input_field"
                                            min="0" step="1"
                                            wire:model.lazy="quantities.{{ $pid }}"
                                            data-index="{{ $i }}"
                                            placeholder="0">
                                    </td>
                                    <td>
                                        <select class="primary_input_select"
                                                wire:model.lazy="observations.{{ $pid }}">
                                            <option value="">—</option>
                                            @foreach($observationTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        {{ __('common.no_data_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.table-container>
            </div>

            {{-- SECCIÓN 4: Footer de control --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3 border-top mt-3">
                <div class=" mb-md-0 mb-3  mb-md-0 mb-3 main-title d-flex align-items-center">
                    <p class="text-black">{{ __('inventorycount::messages.products_counted') }}:</p>
                    <h3 class="ml-2">{{ $countedCount }} / {{ $totalCount }}</h3>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('inventory_count.index') }}" class="btn-toolkit btn-secondary-outline">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="button" class="btn-toolkit btn-primary btn-icon"
                            wire:click="openConfirmModal"
                            {{ $countedCount === 0 ? 'disabled' : '' }}>
                        <i class="ti-check mr-1"></i> {{ __('inventorycount::messages.submit_count') }}
                    </button>
                </div>
            </div>

            @endif {{-- end if countStarted --}}
        </div>

        
    </div>
 
    {{-- Modal de confirmación de envío --}}
    @if($showConfirmModal)
    <dialog class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger-toolkit">
                    <h5 class="modal-title">{{ __('common.confirm') }}</h5>
                    <button type="button" class="close" wire:click="closeConfirmModal"><i class="ti-close"></i></button>
                </div>
                <div class="modal-body text-center">
                    <p>{{ __('inventorycount::messages.submit_confirm_text') }}</p>
                    <div class="badge_5 main-title text-center mt-3">
                        <p class="text-black">{{ __('inventorycount::messages.products_counted') }}:</p>
                        <h3 class="ml-2">{{ $countedCount }} / {{ $totalCount }}</h3>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" wire:click="closeConfirmModal">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="button" class="btn-toolkit btn-danger" onclick="confirmSubmitWithDeviceInfo()">
                        {{ __('common.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </dialog>
    @endif

</x-admin.section>

@push('scripts')
<script>
    document.addEventListener("livewire:load", () => {

    Livewire.hook('message.sent', (message, component) => {
        // empieza petición
        $('#pre-loader').removeClass('d-none');
    })

    Livewire.hook('message.processed', (message, component) => {
        // terminó
        $('#pre-loader').addClass('d-none');

    })

});
</script>
<script>
function getDeviceInfo() {
    return {
        ip:          null, // Se obtiene en el backend via request()->ip()
        user_agent:  navigator.userAgent,
        browser:     getBrowser(),
        os:          getOS(),
        screen:      screen.width + 'x' + screen.height,
        timezone:    Intl.DateTimeFormat().resolvedOptions().timeZone,
        timestamp:   new Date().toISOString(),
        location:    null, // Se intenta obtener geolocalización abajo
    };
}

function getBrowser() {
    const ua = navigator.userAgent;
    if (ua.includes('Chrome'))  return 'Chrome';
    if (ua.includes('Firefox')) return 'Firefox';
    if (ua.includes('Safari'))  return 'Safari';
    if (ua.includes('Edge'))    return 'Edge';
    return 'Desconocido';
}

function getOS() {
    const ua = navigator.userAgent;
    if (ua.includes('Windows')) return 'Windows';
    if (ua.includes('Mac'))     return 'macOS';
    if (ua.includes('Android')) return 'Android';
    if (ua.includes('iOS'))     return 'iOS';
    if (ua.includes('Linux'))   return 'Linux';
    return 'Desconocido';
}

let _startCountCalled = false;
function startCountWithDeviceInfo() {
    if (_startCountCalled) return;
    _startCountCalled = true;

    const info = getDeviceInfo();
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                info.location = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                @this.call('startCount', info);
            },
            () => { @this.call('startCount', info); },
            { timeout: 5000 }
        );
    } else {
        @this.call('startCount', info);
    }
}

function confirmSubmitWithDeviceInfo() {
    const info = getDeviceInfo();
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                info.location = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                @this.call('confirmSubmit', info);
            },
            () => { @this.call('confirmSubmit', info); },
            { timeout: 5000 }
        );
    } else {
        @this.call('confirmSubmit', info);
    }
}

// Navegación con Enter entre inputs
document.addEventListener('livewire:load', function () {
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.classList.contains('count-input')) {
            e.preventDefault();
            const idx = parseInt(e.target.dataset.index);
            const next = document.querySelector(`.count-input[data-index="${idx + 1}"]`);
            if (next) next.focus();
        }
    });
});

// Notificaciones Toastr desde Livewire (v2)
document.addEventListener('livewire:load', function () {
    Livewire.on('toastr-success', (message) => toastr.success(message));
    Livewire.on('toastr-warning', (message) => toastr.warning(message));
    Livewire.on('toastr-error',   (message) => toastr.error(message));
});
</script>
@endpush
