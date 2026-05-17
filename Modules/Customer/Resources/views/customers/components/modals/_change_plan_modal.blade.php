<div id="change_plan_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">{{ __('common.change_plan_manual') }}</h4>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <input type="hidden" id="assign_plan_url" value="{{ route('customer.assign_plan_manually') }}">
            <input type="hidden" id="assign_plan_customer_id" value="{{ $customer->id }}">
            <form action="" id="form_change_plan" method="POST">
                <div class="modal-body">
                    <div class="wizard-step" id="step-1">
                        <div class="form-card">
                           
                            <h3>{{__('common.current_plan')}}</h3>
                        
                            <div class="row">
                                <div class="col-md-auto col-12">
                                    <div class="d-inline-block rounded-circle mb_10"
                                        style="border: 3px solid var(--border_color);">
                                        <img class="rounded-circle"
                                            src="{{ showImage($customer->avatar ??'frontend/default/img/avatar.jpg') }}"
                                            alt="avatar"
                                            style="width: 110px; height: 110px; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-md-auto col-12">
                                    <h2 class="mb-2 fs-18 text-dark-green font-weight-bold">{{ $customer->first_name }} {{ $customer->last_name }}</h2>
                                    <h3 class="fs-20 text-dark-green uppercase fw-500">
                                        <span class="svg-icon-plan svg-sm">
                                            {!! data_get($planContext, 'current_plan.styles.icon') ?? '-' !!}
                                        </span>
                                        {{data_get($planContext, 'display_name') ?? 'Sin plan'}}
                                    </h3>
                                    <p>
                                        <span class="badge_5 mr-2">% Descuento</span>
                                        @if ($customer->is_active == 1)
                                            <span class="badge_1 ml_5">{{ __('common.active') }}</span>
                                        @elseif ($customer->is_active == 0)
                                            <span class="badge_4 ml_5">{{ __('common.disabled') }}</span>
                                        @else
                                            <span class="badge_4 ml_5">{{ __('common.in-active') }}</span>
                                        @endif
                                    </p>
                                    <p class="mt-2">{{$customer->customerProfile?->document_number}} • {{$customer->email}} • {{getNumberTranslate($customer->phone) ?? $customer->username}}</p>
                                    <p class="text-muted">{{__('common.member_since')}} {{dateConvert($customer->created_at)}} </p>
                                </div>
                            </div>
                        </div>

                        <div class="animate from-top delay-4">
                            <div class="arrow-change-vertical">
                                <i class="ti-arrow-down"></i>
                            </div>
                        </div>
                        
                        <div class="form-card">
                            <h3>{{__('common.select_new_plan')}}</h3>
                            <div id="plan-error" class="text-danger d-none">
                                Debes seleccionar un plan
                            </div>
                            <div class="row">
                                @forelse ($plans as $plan) {{-- ESTE ES EL PADRE --}}
                                    @php
                                        $hasMultipleLevels = $plan->PlanChildren->count() > 1;
                                        $planIcon = data_get($plan, 'styles.icon', '');
                                        $planPrimaryColor = data_get($plan, 'styles.primaryColor', 'var(--border_color)');
                                    @endphp

                                    @foreach ($plan->PlanChildren as $planChild) {{-- HIJOS --}}
                                        
                                        @if($customer->customerProfile?->plan_child_id !== $planChild->id)
                                            <div class="col-12 mb-3 animate from-bottom delay-1">
                                                
                                                <input
                                                    type="radio"
                                                    name="plan"
                                                    id="plan_{{ $planChild->id }}"
                                                    class="d-none"
                                                    required
                                                    value="{{ $planChild->id }}"
                                                    data-title="{{ e($hasMultipleLevels
                                                    ? $plan->title . ' > ' . $planChild->title
                                                    : $plan->title) }}"
                                                    data-description="{{ e($planChild->description ?? '') }}"
                                                    data-status="{{ $planChild->is_active ? 'Activo' : 'Inactivo' }}"
                                                    data-svg="{{ $planIcon }}"
                                                    data-color="{{ $planPrimaryColor }}"
                                                >

                                                <label
                                                    for="plan_{{ $planChild->id }}"
                                                    class="plan-card w-100 p-3 d-block"
                                                    style="border-color:{!! $planPrimaryColor !!}; background-color:{!! $planPrimaryColor !!}24"
                                                >
                                                    <div>
                                                        <span class="icon-select"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h6 class="mb-1 font-weight-bold fs-20">
                                                            <div class="svg-icon-plan svg-sm mr-2">
                                                                {!! $planIcon ?: '-' !!}
                                                            </div>
                                                            {{ $hasMultipleLevels
                                                                ? $plan->title . ' > ' . $planChild->title
                                                                : $plan->title
                                                            }}
                                                        </h6>

                                                        @if($planChild->is_active)
                                                            <span class="badge_1">{{ __('common.active') }}</span>
                                                        @else
                                                            <span class="badge_2">{{ __('common.inactive') }}</span>
                                                        @endif
                                                    </div>

                                                    <p class="mb-2 font-weight-bold">
                                                        {{ $planChild->description }}
                                                    </p>

                                                    <ul class="list-unstyled mb-0  text-muted">
                                                        @foreach ($planChild->benefits as $benefits)
                                                        <li>{{$benefits->title}}</li>
                                                        @endforeach
                                                    </ul>

                                                </label>
                                            </div>
                                        @endif

                                    @endforeach

                                @empty
                                     <div class="col-12 text-center">
                                        <p class="text-muted my-3">{{__('common.no_plans_to_select')}}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="wizard-step d-none" id="step-2">
                        <div class="form-card previous-plan previous-state">
                            <h3>{{__('common.plan_change_summary')}}</h3>
                            <div class="row">
                                <div class="col-lg-6 mb-md-0 mb-4">
                                    <div class="form-card shadow-none position-relative h-100">
                                        <h4 class="text-black">{{__('common.current_plan')}}</h4>
                                        <h2 class="text-dark-green fs-20 fw-500 mb-3">
                                            <span class="svg-icon-plan svg-sm">
                                                {!! data_get($planContext, 'current_plan.styles.icon') ?? '-' !!}
                                            </span>
                                            {{ data_get($planContext, 'display_name') ?? 'Sin plan' }}
                                        </h2>
                                        <span class="badge_6">% Descuento</span>
                                        
                                        <div class="arrow-change  ">
                                            <i class="ti-arrow-right arrow-hint"></i>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-lg-6">
                                    <div class="form-card  shadow-none h-100" id="select_plan" >
                                        <h4 class="text-color">{{__('common.new_plan_select')}}</h4>
                                        <h2 id="selected-plan-title" class="text-dark-green fs-20 fw-500 mb-3">Selecciona un plan</h2>
                                        <p id="selected-plan-description" class="mt-3 mb-2 text-muted">
                                            Aquí verás el resumen básico del plan que elijas en el paso anterior.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-card">
                            <h3>{{__('common.confirm_change')}}</h3>
                            <div>
                                <div>
                                    <x-admin.textarea-counter
                                        name="razon_for_change"
                                        label="{{__('common.chan_plan_razon')}} *"
                                        :max="300"
                                        :min="20"
                                        rows="4"
                                    />
                                </div>
                                <div class="mt-3 ">
                                    <div class="d-flex">
                                        <label data-id="acep_term" class="primary_checkbox d-flex mr-2 h-auto">
                                            <input name="acep_term" id="acep_term" value="1"  type="checkbox">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.change_plan_terms') }}</span>
                                            <p class="mb-0 ml-2">{{ __('common.change_plan_terms') }}</p>
                                        </label>
                                    </div>
                                    <span id="error-acep_term" class="d-none text-danger">Campo requerido</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                
                </div>
                <div class="modal-footer d-flex justify-content-between flex-column flex-md-row align-items-center border-0 w-100 mt-2">
                    <div >
                        <button type="button" class="btn-toolkit btn-ghost btn-icon d-none" id="btn-prev">
                            <i class="ti-arrow-left mr-1"></i> {{ __('common.btn_prev') }}
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-toolkit btn-secondary-outline mr-2" data-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn-toolkit btn-primary btn-icon" id="btn-next">
                            {{ __('common.btn_next') }} <i class="ti-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn-toolkit btn-danger d-none" id="btn-save">
                            <i class="ti-alert mr-1"></i> {{ __('common.change_plan') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        function updateSelectedPlanSummary() {
            const selectedPlan = $('input[name="plan"]:checked');

            if (!selectedPlan.length) {
                $('#selected-plan-title').text('Selecciona un plan');
                $('#selected-plan-description').text('-');
                return false;
            }

            const title = selectedPlan.data('title') || '-';
            const description = selectedPlan.data('description') || '-';
            const svg = selectedPlan.data('svg') || '';
            const color = selectedPlan.data('color') || '';
            const status = selectedPlan.data('status') || '';

            $('#select_plan').css({
            'background-color': toRgba(color),
            'border-color': color,
            });
            $('#selected-plan-title').html(`<div class="svg-icon-plan svg-sm mr-1">${svg}</div>${title}`);
            $('#selected-plan-description').text(description);

            if (status) {
                $('#selected-plan-status').removeClass('d-none').text(status);
            } else {
                $('#selected-plan-status').addClass('d-none').text('');
            }

            return true;
        }

        $('#form_change_plan').on('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            const textarea = $('#razon_for_change');
            const min = parseInt(textarea.data('min')) || 0;
            const max = parseInt(textarea.data('max')) || Infinity;
            const length = textarea.val().trim().length;
            const error = $('#error-razon_for_change');

            textarea.removeClass('is-invalid');
            if (length < min || length > max) {
                isValid = false;
                textarea.addClass('is-invalid');
                if (error.length) {
                    error.removeClass('d-none');
                    error.text(length < min ? 'Mínimo ' + min + ' caracteres' : 'Máximo ' + max + ' caracteres');
                }
            } else {
                error.addClass('d-none');
            }

            if (!$('#acep_term').is(':checked')) {
                $('#error-acep_term').removeClass('d-none');
                isValid = false;
            } else {
                $('#error-acep_term').addClass('d-none');
            }

            if (!isValid) return;

            const planChildId = $('input[name="plan"]:checked').val();
            const customerId  = $('#assign_plan_customer_id').val();
            const url         = $('#assign_plan_url').val();

            $("#pre-loader").removeClass('d-none');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token:         '{{ csrf_token() }}',
                    customer_id:    customerId,
                    plan_child_id:  planChildId,
                    reason_notes:   textarea.val().trim(),
                },
                success: function(response) {
                    $("#pre-loader").addClass('d-none');
                    $('#change_plan_modal').modal('hide');
                    toastr.success(response.message);
                    setTimeout(function(){ location.reload(); }, 1200);
                },
                error: function(xhr) {
                    $("#pre-loader").addClass('d-none');
                    const msg = xhr.responseJSON?.message ?? 'Error al asignar el plan';
                    toastr.error(msg);
                }
            });
        })


        // ── Wizard paso siguiente / anterior ──────────────────
        $('#btn-next').on('click', function () {
            let valid = true;

            if (!$('input[name="plan"]:checked').length) {
                $('#plan-error').removeClass('d-none');
                valid = false;
            } else {
                $('#plan-error').addClass('d-none');
            }

            

            if (!valid) return false;
            updateSelectedPlanSummary();
            goToStep(2);
        });

        $('#btn-prev').on('click', function () { goToStep(1); });

        $('input[name="plan"]').on('change', function () {
            $('#plan-error').addClass('d-none');
            updateSelectedPlanSummary();
        });
        
    });

    function goToStep(step) {
        $('.wizard-step').addClass('d-none');
        $('#step-' + step).removeClass('d-none');

        if (step === 1) {
            $('#btn-prev').addClass('d-none');
            $('#btn-next').removeClass('d-none');
            $('#btn-save').addClass('d-none');
        } else {
            $('#btn-prev').removeClass('d-none');
            $('#btn-next').addClass('d-none');
            $('#btn-save').removeClass('d-none');
        }
    }

    function toRgba(color, opacity = 0.15) {

        // 🎯 HEX (#RRGGBB o #RGB)
        if (color.startsWith('#')) {
            let r, g, b;

            if (color.length === 4) {
                r = parseInt(color[1] + color[1], 16);
                g = parseInt(color[2] + color[2], 16);
                b = parseInt(color[3] + color[3], 16);
            } else {
                r = parseInt(color.substring(1, 3), 16);
                g = parseInt(color.substring(3, 5), 16);
                b = parseInt(color.substring(5, 7), 16);
            }

            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        }

        // 🎯 RGB o RGBA
        if (color.startsWith('rgb')) {
            const values = color.match(/\d+(\.\d+)?/g);

            return `rgba(${values[0]}, ${values[1]}, ${values[2]}, ${opacity})`;
        }

        // 🎯 fallback (por si llega algo raro)
        return color;
    }
    
</script>
