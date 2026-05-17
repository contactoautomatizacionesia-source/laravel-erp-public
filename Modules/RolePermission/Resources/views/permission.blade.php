@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset('public/css/role_permissions.css')}}">
@endsection

@section('mainContent')
<x-admin.section >
    <div class="role_permission_wrap d-flex align-items-center mb-4">
        <x-backEnd.back-button :text="false" />
        <div class="main-title">
            <h3 class="mb-0">
                @lang('hr.assign_permission') ({{@$role->name}})
            </h3>
        </div>
    </div>
    
    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'permission.permissions.store','method' => 'POST']) }}
    <div class="erp_role_permission_area px-1">
        <input type="hidden" name="role_id" value="{{@$role->id}}">
        
        @php $firstActive = true; @endphp

        <div class="ign-rp-container">
            
            <div class="ign-rp-sidebar">
                @foreach ($MainMenuList as $key => $Module)
                    @if(!$Module->module or isModuleActive($Module->module))
                        <div class="ign-rp-tab {{ $firstActive ? 'active' : '' }}" data-target="#panel-{{ $Module->id }}">
                            <span>{{ __(is_string($Module->translation) ? $Module->translation : $Module->name) }}</span>
                            <i class="ti-angle-right"></i>
                        </div>
                        @php $firstActive = false; @endphp
                    @endif
                @endforeach
            </div>

            <div class="ign-rp-content">
                @php $firstActive = true; @endphp
                @foreach ($MainMenuList as $key => $Module)
                    @if(!$Module->module or isModuleActive($Module->module))
                        <div class="ign-rp-panel {{ $firstActive ? 'active' : '' }}" id="panel-{{ $Module->id }}">
                            @include('rolepermission::page-components.permissionModule',[ 'key' =>$key, 'Module' =>$Module ])
                        </div>
                        @php $firstActive = false; @endphp
                    @endif
                @endforeach
            </div>

        </div>

    </div>
</x-admin.section>
    {{ Form::close() }}
@endsection

@push('scripts')
<script type="text/javascript">
    (function($) {
        "use strict";

        // Lógica para cambiar de pestaña en el Panel Principal (Padres)
        $('.ign-rp-tab').on('click', function() {
            var target = $(this).data('target');
            $('.ign-rp-tab').removeClass('active');
            $(this).addClass('active');
            $('.ign-rp-panel').removeClass('active');
            $(target).addClass('active');
        });

        // Lógica para cambiar de pestaña en el Sub-Panel (Hijos con acciones)
        $(document).on('click', '.ign-rp-inner-tab', function() {
            var target = $(this).data('target');
            var container = $(this).closest('.ign-rp-inner-container');
            
            container.find('.ign-rp-inner-tab').removeClass('active');
            $(this).addClass('active');
            
            container.find('.ign-rp-inner-panel').removeClass('active');
            $(target).addClass('active');
        });

        // Lógica de Checkbox (Seleccionar Todo el Módulo Principal)
        $('.permission-checkAll').on('click', function () {
            if($(this).is(":checked")){
                    $( '.module_id_'+$(this).val() ).each(function() {
                        $(this).prop('checked', true);
                    });
            }else{
                    $( '.module_id_'+$(this).val() ).each(function() {
                        $(this).prop('checked', false);
                    });
            }
        });

        // Lógica de Submódulo (Seleccionar acciones hijas asociadas)
        $('.module_link').on('click', function () {
            var module_id = $(this).parents('.ign-rp-panel').attr("id").replace('panel-', '');
            var module_link_id = $(this).val();
            
            if($(this).is(":checked")){
                $(".module_option_"+module_id+'_'+module_link_id).prop('checked', true);
            }else{
                $(".module_option_"+module_id+'_'+module_link_id).prop('checked', false);
            }
            
            var checked = 0;
            $( '.module_id_'+module_id ).each(function() {
                if($(this).is(":checked")){
                    checked++;
                }
            });
            if(checked > 0){
                $(".main_module_id_"+module_id).prop('checked', true);
            }else{
                $(".main_module_id_"+module_id).prop('checked', false);
            }
        });

         // Lógica de Opción individual (Nietos)
        $('.module_link_option').on('click', function () {
            var module_id = $(this).parents('.ign-rp-panel').attr("id").replace('panel-', '');
            var module_link = $(this).parents('.module_link_option_div').attr("id");

            var link_checked = 0;
            $( '.module_option_'+module_id+'_'+ module_link).each(function() {
                if($(this).is(":checked")){
                    link_checked++;
                }
            });
            if(link_checked > 0){
                $("#Sub_Module_"+module_link).prop('checked', true);
            }else{
                $("#Sub_Module_"+module_link).prop('checked', false);
            }

            var checked = 0;
            $( '.module_id_'+module_id ).each(function() {
                if($(this).is(":checked")){
                    checked++;
                }
            });
            if(checked > 0){
                $(".main_module_id_"+module_id).prop('checked', true);
            }else{
                $(".main_module_id_"+module_id).prop('checked', false);
            }
        });
        
    })(jQuery);
</script>
@endpush
