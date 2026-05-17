@php
    $name = trans($Module->translation);

    // 1. Filtrar los submódulos válidos según las reglas de tu sistema
    $filteredSubMenus = $SubMenuList->where('parent_id', $Module->id)->filter(function($SubMenu) {
        if(isModuleActive('MultiVendor') && $SubMenu->name == 'Company Reviews') return false;
        if(app('theme')->folder_path == 'amazy' && $SubMenu->route == 'frontendcms.features.index') return false;
        if(app('theme')->folder_path == 'default' && in_array($SubMenu->route, ['frontendcms.ads_bar.index', 'frontendcms.promotionbar.index', 'frontendcms.login_page'])) return false;
        
        if(!$SubMenu->module or isModuleActive($SubMenu->module)) return true;
        return false;
    });

    // 2. Separar los que NO tienen hijos de los que SÍ tienen hijos
    $subWithActions = [];
    $subWithoutActions = [];

    foreach($filteredSubMenus as $sub) {
        // Buscamos si tiene acciones en ActionList
        $actionsCount = $ActionList->where('parent_id', $sub->id)->filter(function($action) {
            return (!$action->module or isModuleActive($action->module));
        })->count();

        if($actionsCount > 0) {
            $subWithActions[] = $sub;
        } else {
            $subWithoutActions[] = $sub;
        }
    }
@endphp

<div class="permission_header_panel d-flex align-items-center justify-content-between">
    <input type="checkbox" name="module_id[]" value="{{ $Module->id }}" id="Main_Module_{{ $key }}" class="common-radio permission-checkAll main_module_id_{{ $Module->id }}" {{ $role->permissions->contains('id',$Module->id) ? 'checked' : '' }} >
    <label for="Main_Module_{{ $key }}"> {{ __('permission.activate_all_module') }} {{ __(is_string($Module->translation) ? $Module->translation : $Module->name) }} </label>
    @if(permissionCheck('permission.permissions.store'))
        <div class="row">
            <div class="col-lg-12 text-right">
                <button class="btn-toolkit btn-primary btn-icon">
                    <span class="ti-check"></span>
                    @lang('common.submit') {{ __('permission.permissions') }}
                </button>
            </div>
        </div>
    @endif
</div>

@if(count($subWithoutActions) > 0)
    <div class="ign-rp-section-title">
        <i class="ti-check-box text-success"></i> {{ __('permission.general_permissions') }}
    </div>
    <div class="ign-rp-submodules-grid">
        @foreach ($subWithoutActions as $SubMenu)
            <div class="submodule-block-simple">
                <input id="Sub_Module_{{ $SubMenu->id }}" name="module_id[]" value="{{ $SubMenu->id }}" class="infix_csk common-radio module_id_{{ $Module->id }} module_link" {{ $role->permissions->contains('id',$SubMenu->id) ? 'checked' : '' }} type="checkbox" >
                <label for="Sub_Module_{{ $SubMenu->id }}">
                    @if($SubMenu->name == 'Seller Reviews' && !isModuleActive('MultiVendor')) {{__('review.company_reviews')}}
                    @elseif($SubMenu->name == 'Inhouse Product Sale' && !isModuleActive('MultiVendor')) {{__('product.product_sale')}}
                    @else {{ __(is_string($SubMenu->translation) ? $SubMenu->translation : $SubMenu->name) }}
                    @endif
                </label>
            </div>
        @endforeach
    </div>
@endif

@if(count($subWithActions) > 0)
    <div class="ign-rp-section-title mt-4">
        <i class="ti-layers text-primary"></i> {{ __('permission.detailed_permissions') }}
        <small>{{ __('permission.contains_specific_actions') }}</small>
    </div>
    
    <div class="ign-rp-inner-container">
        
        <div class="ign-rp-inner-sidebar">
            @foreach ($subWithActions as $index => $SubMenu)
                <div class="ign-rp-inner-tab {{ $index === 0 ? 'active' : '' }}" data-target="#inner-panel-{{ $SubMenu->id }}">
                    {{-- FIX: Removido el onclick en línea para cumplir con reglas de Accesibilidad y SonarQube --}}
                    <div class="tab-text">
                        <div>
                            <input id="Sub_Module_{{ $SubMenu->id }}" name="module_id[]" value="{{ $SubMenu->id }}" class="infix_csk common-radio module_id_{{ $Module->id }} module_link" {{ $role->permissions->contains('id',$SubMenu->id) ? 'checked' : '' }} type="checkbox" >
                            <label for="Sub_Module_{{ $SubMenu->id }}">
                                @if($SubMenu->name == 'Seller Reviews' && !isModuleActive('MultiVendor')) {{__('review.company_reviews')}}
                                @elseif($SubMenu->name == 'Inhouse Product Sale' && !isModuleActive('MultiVendor')) {{__('product.product_sale')}}
                                @else {{ __(is_string($SubMenu->translation) ? $SubMenu->translation : $SubMenu->name) }}
                                @endif
                            </label>
                        </div>
                        <span class="tab-badge">{{ __('permission.configure_options') }} </span>
                    </div>
                    <i class="ti-angle-right"></i>
                </div>
            @endforeach
        </div>

        <div class="ign-rp-inner-content">
            @foreach ($subWithActions as $index => $SubMenu)
                <div class="ign-rp-inner-panel {{ $index === 0 ? 'active' : '' }}" id="inner-panel-{{ $SubMenu->id }}">
                    <div class="inner-panel-title">
                        {{ __('permission.options_for') }} {{ __(is_string($SubMenu->translation) ? $SubMenu->translation : $SubMenu->name) }}
                    </div>
                    
                    <ul class="ign-rp-option-grid">
                        @foreach ($ActionList->where('parent_id',$SubMenu->id) as $action)
                            @if(!$action->module or isModuleActive($action->module))
                                <li>
                                    <div class="module_link_option_div" id="{{ $SubMenu->id }}">
                                        <input id="Option_{{  $action->id }}" name="module_id[]" value="{{  $action->id }}" class="infix_csk common-radio module_id_{{ $Module->id }} module_option_{{ $Module->id }}_{{ $SubMenu->id }} module_link_option" {{ $role->permissions->contains('id',$action->id) ? 'checked' : ''  }} type="checkbox" >
                                        <label for="Option_{{  $action->id }}">
                                            {{ __(is_string($action->translation) ? $action->translation : $action->name) }}
                                        </label>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        
    </div>
@endif

{{-- Script dinámico para aplicar el stopPropagation sin ensuciar el HTML --}}
@push('scripts')
<script>
    $(document).off('click', '.tab-text').on('click', '.tab-text', function(e) {
        e.stopPropagation();
    });
</script>
@endpush
