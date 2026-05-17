@php
    $currentBenefits = data_get($planContext, 'current_plan_benefits', []);
    $hasNextPlan     = !empty($planContext['next_plan']);
    $planName        = data_get($planContext, 'display_name', '');
    $nextPlanName    = data_get($planContext, 'next_plan_child.display_name');
@endphp
<div id="benefits_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="mb-3">
                    <h4 class="mb-0 font-weight-bold">{{ __('common.Benefits') }}</h4>
                    <span class="badge_1">{{ $planName }}</span>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @forelse($currentBenefits as $benefit)
                        @php $card = $benefit['card'] ?? null; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="form-card h-100 mb-0 d-flex flex-column">

                                {{-- Header: icon + title --}}
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    @if($card)
                                        <i class="{{ $card['icon'] }} fs-5 text-success"></i>
                                        <h6 class="font-weight-bold mb-0">{{ $card['title'] }}</h6>
                                    @else
                                        <i class="ti-gift fs-5 text-success"></i>
                                        <h6 class="font-weight-bold mb-0">{{ $benefit['label'] }}</h6>
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
                                        <div class="progress-bar bg-success"
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
                                    <ul class="list-unstyled mb-0 small text-muted mt-auto">
                                        @foreach($card['details'] as $detail)
                                            <li class="d-flex justify-content-between">
                                                <span>{{ $detail['label'] }}</span>
                                                <span class="fw-500 text-dark">{{ $detail['value'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">{{ __('common.no_results_found') }}</p>
                        </div>
                    @endforelse

                    @if($hasNextPlan)
                    <div class="col-12">
                        <div class="border rounded p-3 mt-3 w-100" style="background-color:#fff5f0;">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded p-2 me-3">
                                    <i class="ti-stats-up"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 font-weight-bold">{{ __('common.benefit_modal_tip_title') }}</h6>
                                    <small class="text-muted text-start d-block">
                                        {!! __('common.benefit_modal_tip_body', ['plan' => e($nextPlanName)]) !!}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-bs-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
