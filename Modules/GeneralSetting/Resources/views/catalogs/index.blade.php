@extends('backEnd.master')

@section('mainContent')

<style>
    /* Forzar que la tabla ocupe el 100% y no genere scroll */
    table.dataTable {
        width: 100% !important;
        margin-top: 20px !important;
        margin-bottom: 20px !important;
    }
    
    /* Arreglar el buscador y el selector de cantidad para que no se monten */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    
    /* Quitar el scrollbar feo si no es necesario */
    .dataTables_scrollBody {
        overflow-x: hidden !important; 
    }
    
    /* Arreglar botones del modal */
    .modal-footer-custom {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
</style>

<section class="sms-breadcrumb mb-40 white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>Catálogos del Sistema</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">{{__('common.dashboard')}}</a>
                <a href="#">{{__('general_settings.system_settings')}}</a>
                <a href="#">Catálogos</a>
            </div>
        </div>
    </div>
</section>

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="white_box_30px box_shadow_white mb-20">
                    
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex align-items-center justify-content-between w-100">
                            
                            <div class="mr-30" style="min-width: 300px;">
                                <h5 class="mb-2 text-muted" style="font-size: 12px;">Seleccione el catálogo:</h5>
                                <select id="catalog_selector" class="niceSelect w-100">
                                    @foreach($catalogs as $key => $config)
                                        <option value="{{ $key }}" data-has-code="{{ $config['has_code'] }}">
                                            {{ $config['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button class="primary-btn btn-sm d-inline-flex align-items-center" id="btn_create">
                                <span class="ti-plus pr-2"></span> {{__('common.add_new')}}
                            </button>
                        </div>
                    </div>

                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <table class="table Crm_table_active3" id="catalogs_table">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 50px;">ID</th>
                                        <th scope="col" style="width: 100px;">Código</th>
                                        <th scope="col">Nombre</th> <th scope="col" style="width: 100px;">{{__('common.status')}}</th>
                                        <th scope="col" style="width: 100px;">{{__('common.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="catalogModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">{{__('common.add_new')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="catalogForm">
                    @csrf
                    <input type="hidden" name="id" id="field_id">
                    <input type="hidden" name="type" id="field_type">

                    <div class="row mt-2" id="div_code" style="display:none;">
                        <div class="col-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">Código Interno</label>
                                <input type="text" class="primary_input_field form-control" name="code" id="field_code" placeholder="Ej: 001">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">
                                    {{__('common.name')}} ({{ strtoupper(app()->getLocale()) }}) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="primary_input_field form-control" name="name" id="field_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">{{__('common.status')}}</label>
                                <select class="primary_select form-control" name="is_active" id="field_active">
                                    <option value="1">{{__('common.active')}}</option>
                                    <option value="0">{{__('common.inactive')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-custom">
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">{{__('common.cancel')}}</button>
                        <button type="submit" class="primary-btn fix-gr-bg" id="btn_save">{{__('common.save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table;
        
        function loadTable() {
            let type = $('#catalog_selector').val();
            let hasCode = $('#catalog_selector').find(':selected').data('has-code');

            $('#field_type').val(type);

            if(hasCode) {
                $('#div_code').show();
            } else {
                $('#div_code').hide();
                $('#field_code').val('');
            }

            if ($.fn.DataTable.isDataTable('#catalogs_table')) {
                $('#catalogs_table').DataTable().destroy();
            }

            table = $('#catalogs_table').DataTable({
                processing: true,
                serverSide: true,
                // IMPORTANTE: autoWidth false evita que DataTables calcule mal y ponga scroll
                autoWidth: false, 
                responsive: true,
                ajax: {
                    url: "{{ route('admin.catalogs.datatable') }}",
                    data: { type: type }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'code', name: 'code', visible: hasCode },
                    { data: 'name', name: 'name' },
                    { data: 'is_active', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                drawCallback: function(settings) {
                    $('.dropdown-toggle').dropdown();
                }
            });
        }

        $('#catalog_selector').on('change', function() {
            loadTable();
        });

        loadTable();

        // Crear
        $('#btn_create').click(function() {
            $('#catalogForm')[0].reset();
            $('#field_id').val('');
            $('#field_type').val($('#catalog_selector').val());
            
            if ($('#field_active').next().hasClass('nice-select')) {
                 $('#field_active').val('1').niceSelect('update');
            }

            $('#modalTitle').text('{{__("common.add_new")}}');
            $('#catalogModal').modal('show');
        });

        // Editar
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            let btn = $(this);
            
            $('#field_id').val(btn.data('id'));
            $('#field_type').val($('#catalog_selector').val());
            $('#field_code').val(btn.data('code'));
            $('#field_name').val(btn.data('name')); 
            
            let status = btn.data('active');
            $('#field_active').val(status);
            
            if ($('#field_active').next().hasClass('nice-select')) {
                 $('#field_active').niceSelect('update');
            }
            
            $('#modalTitle').text('{{__("common.edit")}}');
            $('#catalogModal').modal('show');
        });

        // Guardar
        $('#catalogForm').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let btn = $('#btn_save');
            btn.prop('disabled', true).text('{{__("common.saving")}}...');

            $.ajax({
                url: "{{ route('admin.catalogs.store') }}",
                method: "POST",
                data: formData,
                success: function(response) {
                    $('#catalogModal').modal('hide');
                    table.ajax.reload();
                    toastr.success('{{__("common.saved_successfully")}}');
                },
                error: function(xhr) {
                    toastr.error('{{__("common.error_message")}}');
                },
                complete: function() {
                    btn.prop('disabled', false).text('{{__("common.save")}}');
                }
            });
        });

        // Eliminar
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let type = $('#catalog_selector').val();

            if(confirm('{{__("common.are_you_sure_to_delete")}}')) {
                $.ajax({
                    url: "/general-setting/catalogs/" + id,
                    method: "DELETE",
                    data: { 
                        _token: "{{ csrf_token() }}",
                        type: type 
                    },
                    success: function(response) {
                        table.ajax.reload();
                        toastr.success('{{__("common.deleted_successfully")}}');
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON.message || 'Error';
                        toastr.error(msg); 
                    }
                });
            }
        });
    });
</script>
@endpush