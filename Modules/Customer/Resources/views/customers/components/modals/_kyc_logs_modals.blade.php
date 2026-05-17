@php
    // Helpers para traducir IDs a nombres legibles dentro de las modales
    $resolveValue = function($key, $value) {
        // NUEVO: Caché en memoria para evitar el problema N+1
        static $memoryCache = [];

        if (is_null($value) || $value === '') return '--';
        $booleans = ['public_resources', 'marital_society', 'is_pep', 'pep_family', 'ops_foreign_currency', 'has_foreign_accounts', 'iva_responsibility', 'rent_retention_agent', 'ica_retention_agent', 'sales_tax_responsible', 'grand_contributor', 'self_withholder', 'source_retention', 'ica_tax', 'has_rut'];

        // NUEVO: Manejo de Imágenes con Loader Interactivo
        $imageFields = ['front_id_image', 'back_id_image'];
        if (in_array($key, $imageFields) && !empty($value)) {
            $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                return '<a href="' . digital_file_url($value) . '" target="_blank" class="btn-toolkit btn-primary-outline btn-sm mt-1" style="font-size: 11px; padding: 2px 8px;"><i class="ti-download mr-1"></i> ' . __('common.view') . '</a>';
            }

            // Retornamos un contenedor con el loader de fondo y la imagen oculta (opacity: 0)
            // Cuando la imagen carga (onload), ocultamos el loader y aparecemos la imagen suavemente
            return '<div class="mt-2" style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 100px; min-height: 80px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="position: absolute; color: var(--base_color);">
                            <i class="ti-reload loader-spin" style="font-size: 20px;"></i>
                        </div>
                        <img src="' . digital_file_url($value) . '" alt="Documento" style="max-height: 80px; position: relative; z-index: 2; opacity: 0; transition: opacity 0.4s ease;" onload="this.previousElementSibling.style.display=\'none\'; this.style.opacity=\'1\';" onerror="this.previousElementSibling.style.display=\'none\'; this.style.opacity=\'1\';">
                    </div>';
        }

        // Manejo de Documentos/PDFs
        $documentFields = ['declaration_pdffile', 'rut_file'];
        if (in_array($key, $documentFields) && !empty($value)) {
            return '<a href="' . digital_file_url($value) . '" target="_blank" class="btn-toolkit btn-primary-outline btn-sm mt-1" style="font-size: 11px; padding: 2px 8px;"><i class="ti-download mr-1"></i> ' . __('common.view') . '</a>';
        }

        // Prevención del error htmlspecialchars si el valor es un arreglo
        if (is_array($value)) {
            return count(array_filter($value, 'is_array')) > 0 ? json_encode($value, JSON_UNESCAPED_UNICODE) : implode(', ', $value);
        }

        if (in_array($key, $booleans)) return ($value == 1 || $value === 'true' || $value === true) ? __('common.yes') : __('common.no');
        
        $modelMap = [
            'bank' => \Modules\GeneralSetting\Entities\Catalogs\Bank::class,
            'bank_id' => \Modules\GeneralSetting\Entities\Catalogs\Bank::class,
            'city' => \Modules\Setup\Entities\City::class,
            'state' => \Modules\Setup\Entities\State::class,
            'country' => \Modules\Setup\Entities\Country::class,
            'bank_account_type_id' => \Modules\GeneralSetting\Entities\Catalogs\BankAccountType::class,
            'representative' => \App\Models\User::class,
            'document_type_id' => \App\Models\TypeDocument::class,
            'gender_id' => \Modules\GeneralSetting\Entities\Catalogs\Gender::class,
            'civil_status_id' => \Modules\GeneralSetting\Entities\Catalogs\CivilStatus::class,
            'economic_activity_id' => \Modules\GeneralSetting\Entities\Catalogs\EconomicActivity::class,
            'profession_id' => \Modules\GeneralSetting\Entities\Catalogs\Profession::class,
            'lead_source_id' => \Modules\GeneralSetting\Entities\Catalogs\LeadSource::class,
            'product_id' => \Modules\Product\Entities\Product::class,
            'nationality_id' => \Modules\Setup\Entities\Country::class,
            'foreign_country_id' => \Modules\Setup\Entities\Country::class,
            'birth_city_id' => \Modules\Setup\Entities\City::class,
            'issue_city_id' => \Modules\Setup\Entities\City::class,
            'foreign_city_id' => \Modules\Setup\Entities\City::class,
            'declaration_city_id' => \Modules\Setup\Entities\City::class,
            'whatsapp_country_code_id' => \Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode::class,
        ];
        
        // NUEVA LÓGICA CON CACHÉ: Solo consulta a la base de datos si no lo ha buscado antes
        if (array_key_exists($key, $modelMap)) {
            $cacheKey = $modelMap[$key] . '_' . $value;
            
            if (!array_key_exists($cacheKey, $memoryCache)) {
                $record = $modelMap[$key]::find($value);
                $memoryCache[$cacheKey] = $record ? ($record->display_name ?? $record->name ?? $record->product_name ?? $record->code ?? $value) : $value;
            }
            
            return $memoryCache[$cacheKey];
        }
        return $value;
    };

    $formatKey = function($key) {
        $key = str_replace('_id', '', $key);
        return ucwords(str_replace('_', ' ', $key));
    };
@endphp

@if($customer->dataUpdateLogs && $customer->dataUpdateLogs->count() > 0)
    @foreach($customer->dataUpdateLogs as $log)
        @include('customer::customers.components.modals._kyc_log_details_modal', [
            'log' => $log,
            'resolveValue' => $resolveValue,
            'formatKey' => $formatKey
        ])
    @endforeach
@endif

{{-- ESTILOS PARA EL LOADER DE IMÁGENES --}}
<style>
    @keyframes spin-loader {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .loader-spin {
        display: inline-block;
        animation: spin-loader 1s linear infinite;
    }
</style>