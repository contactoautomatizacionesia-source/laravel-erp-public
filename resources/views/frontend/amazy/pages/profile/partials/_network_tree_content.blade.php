@php
    $config = array_merge([
        'show_header' => true,
        'show_metrics' => true,
        'show_filters' => true,
        'is_admin' => false,
    ], $config ?? []);
@endphp

<div class="lif-network-page {{ $config['is_admin'] ? 'lif-admin-context' : '' }}">
    @if($config['show_header'])
    <div class="lif-network-header">
        <div>
            <h3 class="lif-network-title">{{ __('tree.network') }}</h3>
            <div class="lif-network-sub">{{ __('tree.network_desc') }}</div>
        </div>
        <div class="lif-header-actions">
            @if($config['show_metrics'])
            <button type="button" class="lif-ghost lif-metrics-toggle" id="lif-metrics-toggle">
                <span>{{ __('tree.metrics_toggle') }}</span>
                <i class="ti-angle-up"></i>
            </button>
            @endif
            @if($config['show_filters'])
            <button type="button" class="lif-ghost lif-filters-toggle" id="lif-filters-toggle">
                <span>{{ __('tree.filters_toggle') }}</span>
                <i class="ti-angle-up"></i>
            </button>
            @endif
            <div class="lif-chip">
                {!! __('tree.updated_ago', ['minutes' => '<span id="lif-updated-mins">0</span>']) !!}
                <button type="button" id="lif-refresh" title="{{ __('tree.refresh') }}"><i class="ti-reload"></i></button>
            </div>
        </div>
    </div>
    @endif

    @if($config['show_metrics'])
    <div class="lif-metrics" id="lif-metrics">
        <div class="row">
            <div class="col-12 col-xl-6" >
                <div class="row gap-2 justify-content-center px-5">
                    <div class="col-12 col-xl-5">
                        <div class="lif-metric-card">
                            <p class="lif-metric-title">{{ __('tree.total_represented') }}</p>
                            <div class="lif-metric-value" id="lif-stat-total"><span class="lif-metric-loader" aria-hidden="true"></span></div>
                            <div class="lif-metric-sub">{{ __('tree.network_desc') }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="lif-metric-card">
                            <p class="lif-metric-title">{{ __('tree.direct_represented') }}</p>
                            <div class="lif-metric-value" id="lif-stat-directs"><span class="lif-metric-loader" aria-hidden="true"></span></div>
                            <div class="lif-metric-sub">{{ __('tree.level_only', ['level' => 1]) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="lif-metric-card">
                            <p class="lif-metric-title">{{ __('tree.levels_depth') }}</p>
                            <div class="lif-metric-value" id="lif-stat-levels"><span class="lif-metric-loader" aria-hidden="true"></span></div>
                            <div class="lif-metric-sub" id="lif-stat-levels-sub"><span class="lif-metric-loader" aria-hidden="true"></span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="lif-metric-card">
                            <p class="lif-metric-title">{{ __('tree.points_month') }}</p>
                            <div class="lif-metric-value" id="lif-stat-points"><span class="lif-metric-loader" aria-hidden="true"></span></div>
                            <div class="lif-metric-sub">{{ __('tree.cycle_current') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6 px-4 mt-2">
                <div class="lif-metric-card" style="display:flex;gap:12px;align-items:center; padding: 12px !important;">
                    <div>
                        <p class="lif-metric-title">{{ __('tree.plan_distribution') }}</p>
                        <div class="lif-donut-legend" id="lif-donut-legend">
                            <span class="lif-donut-empty" style="font-size:12px;color:#aaa;">-</span>
                        </div>
                        <button type="button" class="lif-ghost lif-donut-details badge_5" id="lif-donut-details">{{ __('tree.view_details') }}</button>
                    </div>
                    <div class="lif-donut" id="lif-donut" title="{{ __('tree.plan_distribution') }}"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($config['show_filters'])
    <div class="lif-controls" id="lif-filters">
        <div class="lif-search-wrapper">
            <i class="ti-search lif-icon" id="lif-search-btn"></i>
            <input type="text" id="lif-search" placeholder="{{ __('tree.search_placeholder') }}">
        </div>
        <button type="button" class="lif-ghost" id="lif-reset-tree" style="display:none;">{{ __('tree.back_to_root') }}</button>
        <div class="d-flex flex-wrap justify-content-between gap-1">
            <select id="lif-level">
                <option value="all">{{ __('tree.all_generations') }}</option>
                <option value="1">{{ __('tree.level_only', ['level' => 1]) }}</option>
                <option value="2">{{ __('tree.level_up_to', ['level' => 2]) }}</option>
                <option value="3">{{ __('tree.level_up_to', ['level' => 3]) }}</option>
            </select>
            <select id="lif-plan">
                <option value="all">{{ __('tree.all_plans') }}</option>
            </select>
        </div>
        <select id="lif-plan-level" style="display:none;">
            <option value="all">{{ __('tree.all_levels') }}</option>
        </select>
        <button type="button" id="lif-expand-all">{{ __('tree.expand_all') }}</button>
        <button type="button" class="lif-ghost" id="lif-collapse-all">{{ __('tree.collapse_all') }}</button>
        <button type="button" class="lif-ghost" id="lif-load-next">{{ __('tree.load_next_level') }}</button>
        <button type="button" class="lif-ghost" id="lif-fullscreen" title="{{ __('tree.fullscreen') }}">
            <i class="ti-fullscreen"></i>
        </button>
    </div>
    @endif

    <div class="lif-tree-wrap">
        <div class="lif-skeleton active" id="lif-skeleton">
            <div class="sk-row">
                <div class="sk-node"></div>
            </div>
            <div class="sk-row sk-row-links">
                <span class="sk-vert"></span>
            </div>
            <div class="sk-row" style="margin-top:18px;">
                <div class="sk-node"></div>
                <div class="sk-node"></div>
                <div class="sk-node"></div>
            </div>
            <div class="sk-row sk-row-links">
                <span class="sk-vert"></span>
                <span class="sk-vert"></span>
                <span class="sk-vert"></span>
            </div>
            <div class="sk-row" style="margin-top:18px;">
                <div class="sk-node"></div>
                <div class="sk-node"></div>
                <div class="sk-node"></div>
                <div class="sk-node"></div>
            </div>
        </div>

        <div class="lif-empty" id="lif-empty">
            <p>{{ __('tree.empty_title') }}</p>
            <button type="button">{{ __('tree.empty_cta') }}</button>
        </div>

        <div class="lif-error" id="lif-error">
            <p>{{ __('tree.error_load') }}</p>
            <button type="button" id="lif-retry">{{ __('tree.retry') }}</button>
        </div>

        <div id="lif-d3" class="lif-d3"></div>
    </div>

    <div class="lif-panel" id="lif-panel">
        <div class="lif-panel-header">
            <button class="lif-panel-close" id="lif-close">&times;</button>
            <div class="lif-panel-header-content">
                <div class="lif-panel-badge">
                    <!-- <span id="lif-panel-plan-icon-top"></span> -->
                    <!-- <span id="lif-panel-plan-label">LIFE MEMBER</span> -->
                </div>
                <h2 id="lif-panel-name">{{ __('tree.loading') }}</h2>
                <p id="lif-panel-subtitle" style="color: #FFF;">—</p>
            </div>
            <div class="lif-panel-avatar-wrap">
                <div id="lif-panel-avatar-initials" class="lif-avatar-initials"></div>
            </div>
        </div>

        <div class="lif-panel-body">
            <!-- Plan Card -->
            <div class="lif-card lif-plan-card">
                <div class="lif-plan-icon-box" id="lif-panel-icon-inner"></div>
                <div class="lif-plan-info">
                    <div class="lif-info-group">
                        <label>{{ __('tree.panel_plan') }}</label>
                        <span id="lif-panel-plan-name">{{ __('tree.no_plan') }}</span>
                    </div>
                    <div class="lif-info-group text-right">
                        <label>{{ __('tree.panel_discount') }}</label>
                        <span id="lif-panel-discount-val">0%</span>
                    </div>
                </div>
            </div>

            <!-- Next Plan Progress -->
            <div class="lif-card lif-plan-progress">
                <div class="lif-plan-progress-head">
                    <label>{{ __('tree.next_plan') }}</label>
                    <span id="lif-panel-next-name">—</span>
                </div>
                <div class="lif-progress-bar">
                    <span id="lif-panel-progress-bar"></span>
                </div>
                <div class="lif-progress-meta">
                    <span id="lif-panel-progress-val">—</span>
                    <span id="lif-panel-target-points">{{ __('tree.target_points_label') }}: —</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="lif-stats-grid">
                <div class="lif-card lif-stat-card">
                    <label><i class="ti-user"></i> {{ __('tree.personal_points') }}</label>
                    <div class="lif-stat-value" id="lif-panel-personal-val">0</div>
                </div>
                <div class="lif-card lif-stat-card">
                    <label><i class="ti-stats-up"></i> {{ __('tree.network_points') }}</label>
                    <div class="lif-stat-value" id="lif-panel-network-val">0</div>
                </div>
            </div>

            @if($config['is_admin'])
            <div class="lif-card" style="padding: 0; background: transparent; box-shadow: none;">
                <a href="#" id="lif-panel-admin-link" class="btn-toolkit text-white w-100">
                    {{ __('tree.view_profile') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="lif-modal modal" id="lif-plans-modal" aria-hidden="true">
        <div class="lif-modal-backdrop" data-close="true"></div>
        <div class="lif-modal-card" role="dialog" aria-modal="true" aria-labelledby="lif-plans-title">
            <div class="lif-modal-head">
                <h4 id="lif-plans-title">{{ __('tree.plan_distribution_details') }}</h4>
                <button type="button" class="lif-modal-close" id="lif-plans-close">{{ __('tree.close') }}</button>
            </div>
            <div class="lif-modal-body">
                <div id="lif-plans-list" class="lif-plan-list"></div>
            </div>
        </div>
    </div>
</div>
