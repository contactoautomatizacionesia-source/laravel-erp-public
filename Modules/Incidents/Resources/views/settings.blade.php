@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-incidents-settings">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header mb-15">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('incidents::menu.settings') }}</h3>
                </div>
            </div>

            <form id="form-settings">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        {{-- Plazo de pronunciamiento --}}
                        <div class="form-card mb-15">
                            <h3>{{ __('incidents::messages.settings_deadline_section') }}</h3>
                            <div class="">
                                <div class="form-group">
                                    <label class="primary_input_label" for="statement_deadline_hours">{{ __('incidents::messages.statement_deadline_hours') }}</label>
                                    <input type="number" name="statement_deadline_hours" class="primary_input_field"
                                        value="{{ $setting->statement_deadline_hours }}" min="1" max="8760" required>
                                    <small class="text-muted">{{ __('incidents::messages.statement_deadline_hint') }}</small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control ">
                                        <input type="hidden" name="auto_escalate_on_deadline" value="0">
                                       
                                        <x-backEnd.switch-toggle
                                            name="auto_escalate_on_deadline"
                                            id="auto-escalate"
                                            label=""
                                            :hint=" __('incidents::messages.auto_escalate')"
                                            :checked="$setting->auto_escalate_on_deadline"
                                        />
                                       
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control ">
                                        <input type="hidden" name="send_deadline_reminder" value="0">
                                        <x-backEnd.switch-toggle
                                            name="send_deadline_reminder"
                                            id="send-reminder"
                                            label=""
                                            :hint=" __('incidents::messages.send_reminder')"
                                            :checked="$setting->send_deadline_reminder"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="primary_input_label" for="reminder_hours_before">{{ __('incidents::messages.reminder_hours_before') }}</label>
                                    <input type="number" name="reminder_hours_before" class="primary_input_field"
                                        value="{{ $setting->reminder_hours_before }}" min="1" max="8760" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- Notificaciones --}}
                        <div class="form-card mb-15">
                            <h3>{{ __('incidents::messages.settings_notifications_section') }}</h3>
                            <div class="">
                                <div class="form-group">
                                    <div class="custom-control">
                                        <input type="hidden" name="send_email_notifications" value="0">
                                
                                        <x-backEnd.switch-toggle
                                            name="send_email_notifications"
                                            id="send-email"
                                            label=""
                                            :hint=" __('incidents::messages.send_email')"
                                            :checked="$setting->send_email_notifications"
                                        />
                                       
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control ">
                                        <input type="hidden" name="send_system_notifications" value="0">
                                        <x-backEnd.switch-toggle
                                            name="send_system_notifications"
                                            id="send-system"
                                            label=""
                                            :hint=" __('incidents::messages.send_system_notif')"
                                            :checked="$setting->send_system_notifications"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Precio de referencia --}}
                        <div class="form-card mb-15">
                            <h3>{{ __('incidents::messages.settings_price_section') }}</h3>
                            <div class="">
                                <div class="form-group">
                                    <label class="primary_input_label" for="price-reference">{{ __('incidents::messages.price_reference') }}</label>
                                    <select name="price_reference" id="price-reference" class="primary_input_select" required>
                                        <option value="public_price"    {{ $setting->price_reference === 'public_price'    ? 'selected' : '' }}>{{ __('incidents::messages.price_public') }}</option>
                                        <option value="cost_price"      {{ $setting->price_reference === 'cost_price'      ? 'selected' : '' }}>{{ __('incidents::messages.price_cost') }}</option>
                                        <option value="transfer_price"  {{ $setting->price_reference === 'transfer_price'  ? 'selected' : '' }}>{{ __('incidents::messages.price_transfer') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                

                <div class="d-flex justify-content-center mt-5">
                    <button type="submit" class="btn-toolkit btn-primary btn-icon">
                        <i class="ti-save mr-1"></i>{{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin.section>
@endsection

@push('scripts')
<script>
$('#form-settings').on('submit', function (e) {
    e.preventDefault();
    $('#pre-loader').removeClass('d-none');
    const fd  = new FormData(this);
    const obj = {};
    fd.forEach((val, key) => {
        // Los checkboxes con hidden input generan dos valores; tomar el último (checkbox o 0)
        obj[key] = val;
    });

    $.ajax({
        url: '{{ route("incidents.settings.update") }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(obj),
        success: function (res) {
            if (res.success) { toastr.success(res.message); }
            else { toastr.error(res.message); }
            $('#pre-loader').addClass('d-none');
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
            $('#pre-loader').addClass('d-none');
        }
    });
});
</script>
@endpush
