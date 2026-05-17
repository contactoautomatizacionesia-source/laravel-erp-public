@php
    $hasNextPlan   = !empty($planContext['next_plan']);
    $nextPlanName  = data_get($planContext, 'next_plan_child.display_name');
    $nextPlanRules = data_get($planContext, 'next_plan_requirements', []);
@endphp
@if($hasNextPlan)
<div id="next_plan_rules_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="mb-3">
                    <h4 class="mb-0 font-weight-bold">{{ __('common.requirements_to_next_plan') }}</h4>
                    <span class="badge_1">{{ $nextPlanName }}</span>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @forelse($nextPlanRules as $rule)
                        @php $card = $rule['card'] ?? null; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="form-card h-100 mb-0 d-flex flex-column">

                                {{-- Header: icon + title --}}
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    @if($card)
                                        <i class="{{ $card['icon'] }} fs-5 text-color"></i>
                                        <h6 class="font-weight-bold mb-0">{{ $card['title'] }}</h6>
                                    @else
                                        <i class="ti-check-box fs-5 text-color"></i>
                                        <h6 class="font-weight-bold mb-0">{{ $rule['label'] }}</h6>
                                    @endif
                                </div>

                                {{-- Summary --}}
                                @if($card)
                                    <p class="text-muted small mb-2">{{ $card['summary'] }}</p>
                                @endif

                                {{-- Progress bar --}}
                                @if($card && !empty($card['progress']) && $card['progress']['target'] > 0)
                                    @php $pct = $card['progress']['percent'] ?? 0; @endphp
                                    <div class="progress mb-2" style="height:6px;">
                                        <div class="progress-bar {{ $rule['passed'] ? 'bg-success' : 'bg-warning' }}"
                                             role="progressbar"
                                             style="width: {{ $pct }}%"
                                             aria-valuenow="{{ $pct }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                @endif

                                {{-- Details --}}
                                @if($card && !empty($card['details']))
                                    <ul class="list-unstyled mb-2 small text-muted mt-auto">
                                        @foreach($card['details'] as $detail)
                                            <li class="d-flex justify-content-between">
                                                <span>{{ $detail['label'] }}</span>
                                                <span class="fw-500 text-dark">{{ $detail['value'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Required badge --}}
                                @if($rule['is_required'])
                                    <span class="badge_2 mt-auto py-1 px-3 text-uppercase align-self-start">
                                        {{ __('common.required') }}
                                    </span>
                                @endif

                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <i class="ti-info-alt fs-3 text-muted mb-2"></i>
                            <p class="text-muted mb-0">{{ __('common.no_rules_found_for_next_plan') }}</p>
                        </div>
                    @endforelse
                </div>

                @if(count($nextPlanRules) > 0)
                <div class="border rounded p-3 mt-3 w-100" style="background-color: #f0f7ff;">
                    <div class="d-flex align-items-start">
                        <div class="bg-primary text-white rounded p-2 me-3">
                            <i class="ti-light-bulb"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 font-weight-bold">{{ __('common.rule_card_next_plan_tip_title') }}</h6>
                            <small class="text-muted text-start d-block">
                                {!! __('common.rule_card_next_plan_tip_body', ['plan' => e($nextPlanName)]) !!}
                            </small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-bs-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endif
