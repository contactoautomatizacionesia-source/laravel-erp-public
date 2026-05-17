
(function () {
    const config = window.LifNetworkConfig || {};
    const labels = window.LifNetworkLabels || {};
    const maxDepthLimit = Number.isFinite(config.maxDepthLimit) ? config.maxDepthLimit : 5;

    const container = document.getElementById('lif-d3');
    const skeleton = document.getElementById('lif-skeleton');
    const emptyState = document.getElementById('lif-empty');
    const errorState = document.getElementById('lif-error');
    const panel = document.getElementById('lif-panel');
    const closePanel = document.getElementById('lif-close');
    const refreshBtn = document.getElementById('lif-refresh');
    const updatedMins = document.getElementById('lif-updated-mins');

    const searchInput = document.getElementById('lif-search');
    const levelSelect = document.getElementById('lif-level');
    const planSelect = document.getElementById('lif-plan');
    const planLevelSelect = document.getElementById('lif-plan-level');
    const expandAll = document.getElementById('lif-expand-all');
    const collapseAll = document.getElementById('lif-collapse-all');
    const loadNext = document.getElementById('lif-load-next');
    const plansModal = document.getElementById('lif-plans-modal');
    const plansClose = document.getElementById('lif-plans-close');
    const plansList = document.getElementById('lif-plans-list');
    const plansDetailsBtn = document.getElementById('lif-donut-details');
    const searchBtn = document.getElementById('lif-search-btn');
    const resetTreeBtn = document.getElementById('lif-reset-tree');
    const metricsToggle = document.getElementById('lif-metrics-toggle');
    const metricsContainer = document.getElementById('lif-metrics');
    const filtersToggle = document.getElementById('lif-filters-toggle');
    const filtersContainer = document.getElementById('lif-filters');

    const state = { loading: true, empty: false, error: false };
    let nodeById = new Map();
    let root = null;
    let loadedDepth = 2;
    let lastPlansDistribution = [];
    let allPlansData = [];
    const baseUserId = config.baseUserId || null;
    let currentRootId = baseUserId;
    let maxDepth = null;
    let hasMoreLevels = null;

    function parsePlanStyles(styles) {
        try {
            if (!styles) return null;
            if (typeof styles === 'string') return JSON.parse(styles);
            if (typeof styles === 'object') return styles;
        } catch (_) {
            return null;
        }
        return null;
    }

    function formatDiscount(discount) {
        if (!discount) return '0%';
        if (typeof discount === 'number') return `${discount}%`;
        const qty = discount.discount_quantity ?? discount.quantity ?? null;
        const typeLabel = discount.discount_type_label ?? discount.discount_type ?? '';
        if (qty === null) return '0%';
        if (String(typeLabel).toLowerCase().includes('percent')) {
            return `${qty}%`;
        }
        return `${qty}`;
    }

    function applyState() {
        if (!container) return;
        if (skeleton) skeleton.classList.toggle('active', state.loading);
        container.style.display = (!state.loading && !state.empty && !state.error) ? 'block' : 'none';
        if (emptyState) emptyState.style.display = state.empty ? 'block' : 'none';
        if (errorState) errorState.style.display = state.error ? 'block' : 'none';
    }

    function getEffectiveMaxDepth() {
        let cap = Number.isFinite(maxDepthLimit) ? maxDepthLimit : null;
        if (typeof maxDepth === 'number' && !Number.isNaN(maxDepth) && maxDepth > 0) {
            cap = cap ? Math.min(cap, maxDepth) : maxDepth;
        }
        return cap;
    }

    // -- Carga del árbol --------------------------------------------------
    function loadTree(depth, search, plan, targetId) {
        state.loading = true;
        state.error = false;
        applyState();

        const params = new URLSearchParams({ depth: depth || loadedDepth });
        if (search) params.set('search', search);
        if (plan && plan !== 'all') params.set('plan', plan);
        if (targetId) params.set('target', targetId);

        fetch(`${config.treeUrl}${config.treeUrl.includes('?') ? '&' : '?'}${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(res => {
                state.loading = false;
                if (!res.success || !res.data) {
                    state.empty = true;
                    applyState();
                    return;
                }
                try {
                    root = d3.hierarchy(res.data);
                    applyLevel(root, loadedDepth);
                    render();
                    applyState();
                    updateMins();
                    if (res.meta && typeof res.meta.max_depth !== 'undefined') {
                        maxDepth = typeof res.meta.max_depth === 'number'
                            ? res.meta.max_depth
                            : parseInt(res.meta.max_depth || 0, 10);
                    }
                    if (res.meta && typeof res.meta.has_more_levels !== 'undefined') {
                        hasMoreLevels = !!res.meta.has_more_levels;
                    } else {
                        hasMoreLevels = null;
                    }
                    updateLoadNextAvailability();
                    updateGenerationsSelector(getEffectiveMaxDepth() || loadedDepth);
                } catch (err) {
                    console.error('Error renderizando árbol:', err);
                    state.error = true;
                    applyState();
                }
            })
            .catch(() => {
                state.loading = false;
                state.error = true;
                console.error('Error cargando árbol');
                applyState();
            });
    }

    // -- Carga de métricas ------------------------------------------------
    const metricLoaderHtml = '<span class="lif-metric-loader" aria-hidden="true"></span>';
    function setMetricsLoading() {
        ['lif-stat-total', 'lif-stat-directs', 'lif-stat-levels', 'lif-stat-levels-sub', 'lif-stat-points']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = metricLoaderHtml;
            });
    }

    function loadStats(targetId) {
        setMetricsLoading();
        const params = new URLSearchParams();
        if (targetId) params.set('target', targetId);
        const statsUrl = params.toString()
            ? `${config.statsUrl}${config.statsUrl.includes('?') ? '&' : '?'}${params}`
            : config.statsUrl;
        fetch(statsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                const d = res.data;
                const totalEl = document.getElementById('lif-stat-total');
                if (totalEl) totalEl.textContent = d.total_network_count !== null ? d.total_network_count.toLocaleString('es-CO') : '0';

                const directsEl = document.getElementById('lif-stat-directs');
                if (directsEl) directsEl.textContent = d.direct_children_count !== null ? d.direct_children_count.toLocaleString('es-CO') : '0';

                const levelsEl = document.getElementById('lif-stat-levels');
                if (levelsEl) levelsEl.textContent = d.max_depth !== null ? d.max_depth : '0';

                const levelsSubEl = document.getElementById('lif-stat-levels-sub');
                if (levelsSubEl) {
                    levelsSubEl.textContent = d.max_depth
                        ? String(labels.upToGeneration || '').replace(':level', d.max_depth)
                        : '—';
                }

                const pointsEl = document.getElementById('lif-stat-points');
                if (pointsEl) pointsEl.textContent = d.total_network_points !== null ? d.total_network_points : '—';

                maxDepth = typeof d.max_depth === 'number' ? d.max_depth : parseInt(d.max_depth || 0, 10);
                updateLoadNextAvailability();
                updateGenerationsSelector(getEffectiveMaxDepth() || loadedDepth);
                renderPlanDistribution(d.distribution_by_plan || []);
            })
            .catch(() => {
                const setText = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                };
                setText('lif-stat-total', '0');
                setText('lif-stat-directs', '0');
                setText('lif-stat-levels', '0');
                setText('lif-stat-levels-sub', '');
                setText('lif-stat-points', '0');
                maxDepth = null;
                updateLoadNextAvailability();
            });
    }

    function renderPlanDistribution(plans) {
        const legend = document.getElementById('lif-donut-legend');
        const donut = document.getElementById('lif-donut');
        if (!legend || !donut) return;

        if (!Array.isArray(plans) || !plans.length) {
            legend.innerHTML = '<span style="font-size:12px;color:#aaa;">—</span>';
            donut.style.background = '';
            return;
        }

        const sorted = [...plans].sort((a, b) => (b.percentage || 0) - (a.percentage || 0));
        lastPlansDistribution = sorted;
        const TOP_N = 3;
        const topPlans = sorted.slice(0, TOP_N);
        const restPlans = sorted.slice(TOP_N);
        const othersPct = restPlans.reduce((sum, p) => sum + (p.percentage || 0), 0);

        const displayPlans = [...topPlans];
        if (othersPct > 0) {
            displayPlans.push({
                plan_name: labels.others || 'Otros',
                percentage: Number(othersPct.toFixed(2)),
                badge_color: '#e5e7eb',
                is_others: true
            });
        }

        let acc = 0;
        const slices = displayPlans.map(p => {
            const start = acc;
            acc += p.percentage || 0;
            return `${p.badge_color} ${start}% ${acc}%`;
        });
        donut.style.background = `conic-gradient(${slices.join(', ')})`;

        legend.innerHTML = displayPlans.map(p => {
            const label = `${p.percentage ?? 0}% ${p.plan_name}`;
            if (p.is_others) {
                return `<button type="button" class="lif-donut-other" data-open-plans="true"><span class="lif-dot" style="background:${p.badge_color}"></span>${label}</button>`;
            }
            return `<span><span class="lif-dot" style="background:${p.badge_color}"></span>${label}</span>`;
        }).join('');

        if (plansDetailsBtn) {
            plansDetailsBtn.style.display = restPlans.length ? 'inline-flex' : 'none';
        }
        legend.querySelectorAll('[data-open-plans]').forEach(btn => {
            btn.addEventListener('click', () => openPlansModal(sorted));
        });
    }
    function openPlansModal(plans) {
        if (!plansModal || !plansList) return;
        plansList.innerHTML = plans.map(p => {
            const pct = p.percentage ?? 0;
            return `<div class="lif-plan-row">
                        <span class="lif-dot" style="background:${p.badge_color}"></span>
                        <span class="lif-plan-name">${p.plan_name}</span>
                        <span class="lif-plan-pct">${pct}%</span>
                    </div>`;
        }).join('');
        plansModal.classList.add('open');
        plansModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closePlansModal() {
        if (!plansModal) return;
        plansModal.classList.remove('open');
        plansModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function loadPlans() {
        if (!planSelect) return;
        fetch(config.plansUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !Array.isArray(res.data)) return;
                allPlansData = res.data;
                const current = planSelect.value;
                planSelect.innerHTML = '';

                const allOpt = document.createElement('option');
                allOpt.value = 'all';
                allOpt.textContent = labels.allPlans || 'Todos los planes';
                planSelect.appendChild(allOpt);

                res.data.forEach(p => {
                    if (!p || !p.name) return;
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    planSelect.appendChild(opt);
                });

                if ([...planSelect.options].some(o => o.value === current)) {
                    planSelect.value = current;
                }
                updatePlanLevels();
                if (root) render();
            })
            .catch(() => {
                // silenciar errores de planes
            });
    }

    function updatePlanLevels() {
        if (!planLevelSelect) return;
        const planId = planSelect.value;
        const plan = allPlansData.find(p => String(p.id) === planId);

        if (plan && plan.levels && plan.levels.length > 1) {
            planLevelSelect.style.display = 'inline-block';
            const current = planLevelSelect.value;
            planLevelSelect.innerHTML = `<option value="all">${labels.allLevels || 'Todos los niveles'}</option>`;
            plan.levels.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.id;
                opt.textContent = l.name;
                planLevelSelect.appendChild(opt);
            });
            if ([...planLevelSelect.options].some(o => o.value === current)) planLevelSelect.value = current;
        } else {
            planLevelSelect.style.display = 'none';
            planLevelSelect.value = 'all';
        }
    }

    function updateGenerationsSelector(maxDepthValue) {
        if (!levelSelect) return;
        const current = levelSelect.value;
        levelSelect.innerHTML = `<option value="all">${labels.allGenerations || 'Todas las generaciones'}</option>`;
        for (let i = 1; i <= maxDepthValue; i++) {
            const opt = document.createElement('option');
            opt.value = i;
            const label = (i === 1)
                ? (labels.generationOnly || 'Generación :level').replace(':level', i)
                : (labels.upToGeneration || 'Hasta generación :level').replace(':level', i);
            opt.textContent = label;
            levelSelect.appendChild(opt);
        }
        if (!levelSelect.querySelector(`option[value="${current}"]`)) {
            levelSelect.value = 'all';
        } else {
            levelSelect.value = current;
        }
    }

    // -- Temporizador "actualizado hace X min" ----------------------------
    let lastLoadTime = Date.now();
    function updateMins() {
        lastLoadTime = Date.now();
        if (updatedMins) updatedMins.textContent = '0';
    }
    setInterval(() => {
        if (updatedMins) updatedMins.textContent = Math.floor((Date.now() - lastLoadTime) / 60000);
    }, 60000);

    // -- Eventos ----------------------------------------------------------
    if (metricsToggle && metricsContainer) {
        metricsToggle.addEventListener('click', function () {
            const next = !metricsContainer.classList.contains('is-collapsed');
            metricsContainer.classList.toggle('is-collapsed', next);
            metricsToggle.classList.toggle('is-collapsed', next);
        });
    }

    if (filtersToggle && filtersContainer) {
        filtersToggle.addEventListener('click', function () {
            const next = !filtersContainer.classList.contains('is-collapsed');
            filtersContainer.classList.toggle('is-collapsed', next);
            filtersToggle.classList.toggle('is-collapsed', next);
        });
    }

    document.getElementById('lif-retry')?.addEventListener('click', function () {
        loadTree(loadedDepth, null, null, currentRootId);
    });

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            loadTree(loadedDepth, null, null, currentRootId);
            loadStats(currentRootId);
            loadPlans();
        });
    }

    plansClose?.addEventListener('click', closePlansModal);
    plansModal?.addEventListener('click', function (e) {
        if (e.target && e.target.dataset && e.target.dataset.close) closePlansModal();
    });
    plansDetailsBtn?.addEventListener('click', function () {
        if (lastPlansDistribution.length) {
            openPlansModal(lastPlansDistribution);
            return;
        }
    });

    if (closePanel) {
        closePanel.addEventListener('click', function () {
            panel.classList.remove('open');
        });
    }

    if (container) {
        container.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.lif-node-toggle');
            if (!toggleBtn) return;
            e.preventDefault();
            e.stopPropagation();
            const nodeEl = toggleBtn.closest('.lif-node');
            const nodeId = nodeEl?.dataset?.id;
            const node = nodeById.get(nodeId);
            if (node) toggle(node);
        });
    }

    function openPanel(data) {
        if (!panel) return;

        const accent = data.plan_color || '#10b981';
        panel.style.setProperty('--lif-accent', accent);

        const nameEl = document.getElementById('lif-panel-name');
        const subEl = document.getElementById('lif-panel-subtitle');
        const iconTopWrap = document.getElementById('lif-panel-plan-icon-top');
        const initialsEl = document.getElementById('lif-panel-avatar-initials');

        const initials = (data.name || '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();

        if (nameEl) nameEl.textContent = data.name || '—';
        if (subEl) {
            const plan = data.plan || labels.noPlan || '—';
            const since = data.reg_year ? String(labels.memberSince || '').replace(':year', data.reg_year) : '';
            subEl.textContent = since ? `${plan} • ${since}` : plan;
        }
        if (iconTopWrap) iconTopWrap.innerHTML = data.plan_icon ? `<span class="svg-icon-plan">${data.plan_icon}</span>` : '';
        if (initialsEl) initialsEl.textContent = initials;

        const profileLink = document.getElementById('lif-panel-profile-link');
        const adminLink = document.getElementById('lif-panel-admin-link');
        if (profileLink) profileLink.style.background = accent;
        if (adminLink) adminLink.style.background = accent;
        if (adminLink && config.adminProfileUrl) {
            adminLink.setAttribute('href', `${config.adminProfileUrl}/${data.id}`);
        }

        const docLabel = document.getElementById('lif-panel-document');
        if (docLabel) docLabel.textContent = data.document_number || '—';

        const statsPersonal = document.getElementById('lif-panel-personal-val');
        const statsNetwork = document.getElementById('lif-panel-network-val');
        if (statsPersonal) statsPersonal.textContent = data.personal_points_formatted || '0';
        if (statsNetwork) statsNetwork.textContent = data.network_points_formatted || '0';

        if (panel) panel.classList.add('open');

        if (!config.panelUrl) return;

        // =========================================================
        // 1. APLICAR ANIMACIONES DE CARGA (SKELETONS)
        // =========================================================
        const planNameEl = document.getElementById('lif-panel-plan-name');
        const discValEl = document.getElementById('lif-panel-discount-val');
        const iconInnerWrap = document.getElementById('lif-panel-icon-inner');

        const nextNameEl = document.getElementById('lif-panel-next-name');
        const progressBar = document.getElementById('lif-panel-progress-bar');
        const progressVal = document.getElementById('lif-panel-progress-val');
        const targetPoints = document.getElementById('lif-panel-target-points');

        // Reutilizamos la misma clase CSS que ya tienes para las métricas
        const textLoader = '<span class="lif-metric-loader" style="width: 10px; height: 10px; display: inline-block;"></span>';
        const iconLoader = '<span class="lif-metric-loader" style="width: 100%; height: 100%; display: block; border-radius: 50%;"></span>';

        if (planNameEl) planNameEl.innerHTML = textLoader;
        if (discValEl) discValEl.innerHTML = textLoader;
        if (iconInnerWrap) iconInnerWrap.innerHTML = iconLoader; // Animación en el icono
        if (nextNameEl) nextNameEl.innerHTML = textLoader;
        if (progressVal) progressVal.innerHTML = textLoader;
        if (targetPoints) targetPoints.innerHTML = textLoader;
        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.style.background = '#e5e7eb';
        }

        // =========================================================
        // 2. FETCH AJAX Y RENDERIZADO FINAL
        // =========================================================
        const params = new URLSearchParams({ user: data.id });
        fetch(`${config.panelUrl}${config.panelUrl.includes('?') ? '&' : '?'}${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                const ctx = res.data;

                const styles = ctx.current_plan?.styles ? parsePlanStyles(ctx.current_plan.styles) : null;
                const accentColor = styles?.primaryColor || accent;

                if (planNameEl) {
                    planNameEl.textContent = ctx.display_name || data.plan || labels.noPlan || '—';
                    planNameEl.style.color = accentColor;
                }

                if (discValEl) {
                    discValEl.textContent = formatDiscount(ctx.current_explicit_discount);
                }

                if (iconInnerWrap) {
                    const iconSvg = styles?.icon || data.plan_icon;
                    iconInnerWrap.innerHTML = iconSvg ? `<span class="svg-icon-plan svg-md">${iconSvg}</span>` : '<i class="ti-package"></i>';
                    iconInnerWrap.style.color = accentColor;
                }

                // =========================================================
                // 3. CORRECCIÓN LÓGICA DE PROGRESO (.lif-plan-progress)
                // =========================================================
                const hasNextPlan = !!ctx.next_plan; // Verifica si hay un nivel superior

                if (hasNextPlan) {
                    // TIENE PLAN SIGUIENTE
                    if (nextNameEl) nextNameEl.textContent = ctx.next_plan_child?.display_name || labels.nextPlan || 'Siguiente Nivel';

                    const progress = typeof ctx.progress_to_next_plan === 'number'
                        ? Math.max(0, Math.min(100, ctx.progress_to_next_plan))
                        : 0;

                    if (progressBar) {
                        progressBar.style.width = `${progress}%`;
                        progressBar.style.background = accentColor;
                    }
                    if (progressVal) progressVal.textContent = `${progress}%`;

                    if (targetPoints) {
                        // Formato: 1500 / 5000 pts
                        const currentPts = ctx.current_points || 0;
                        const targetPts = ctx.next_plan_target_points || 0;
                        targetPoints.textContent = `${currentPts} / ${targetPts} pts`;
                    }
                } else {
                    // NIVEL MÁXIMO ALCANZADO
                    if (nextNameEl) nextNameEl.textContent = labels.maxPlanTitle || 'Nivel Máximo';
                    if (progressBar) {
                        progressBar.style.width = '100%';
                        progressBar.style.background = accentColor;
                    }
                    if (progressVal) progressVal.textContent = '100%';
                    if (targetPoints) targetPoints.textContent = labels.maxPlanDesc || 'Beneficios máximos alcanzados';
                }
            })
            .catch(() => {
                // Si falla la red, limpiamos la animación para que no se quede cargando infinito
                if (planNameEl) planNameEl.textContent = '—';
                if (iconInnerWrap) iconInnerWrap.innerHTML = '<i class="ti-na"></i>';
                if (nextNameEl) nextNameEl.textContent = '—';
                if (progressVal) progressVal.textContent = '—';
                if (targetPoints) targetPoints.textContent = '—';
            });
    }

    if (container) {
        const svg = d3.select(container).append('svg');
        const g = svg.append('g');
        const linkGroup = g.append('g');
        const nodeGroup = g.append('g');

        const zoom = d3.zoom().scaleExtent([0.01, 20]).on('zoom', (event) => {
            g.attr('transform', event.transform);
        });
        svg.call(zoom);
        container.addEventListener('wheel', function (e) {
            e.preventDefault();
        }, { passive: false });

        function render() {
            const treeLayout = d3.tree().nodeSize([340, 200]);
            if (!root) return;
            treeLayout(root);

            // --- NUEVO AJUSTE: Elevar el nodo raíz ---
            root.each(d => {
                if (d.depth === 0) {
                    d.y -= 70;
                }
            });
            // -----------------------------------------

            const nodes = root.descendants();
            const links = root.links();

            const nodeWidth = 240;
            const nodeHeight = 180;
            const padding = 220;

            const xMin = d3.min(nodes, d => d.x + (d.data._dx || 0));
            const xMax = d3.max(nodes, d => d.x + (d.data._dx || 0));
            const yMin = d3.min(nodes, d => d.y + (d.data._dy || 0));
            const yMax = d3.max(nodes, d => d.y + (d.data._dy || 0));

            const width = Math.max(900, (xMax - xMin) + nodeWidth + padding * 2);
            const height = Math.max(520, (yMax - yMin) + nodeHeight + padding * 2);

            svg.attr('height', height);
            svg.attr('viewBox', [xMin - nodeWidth / 2 - padding, yMin - nodeHeight / 2 - padding, width, height]);

            nodeById = new Map(nodes.map(n => [String(n.data.id), n]));

            const linkSel = linkGroup.selectAll('path').data(links.filter(d => d?.target?.data?.id), d => d.target.data.id);
            linkSel.join(
                enter => enter.append('path')
                    .attr('class', 'lif-d3-link is-drawing')
                    .attr('d', linkPath)
                    .each(function () {
                        const path = d3.select(this);
                        const len = this.getTotalLength ? this.getTotalLength() : 120;
                        path.style('stroke-dasharray', len)
                            .style('stroke-dashoffset', len);
                        path.transition()
                            .duration(900)
                            .ease(d3.easeCubicOut)
                            .style('stroke-dashoffset', 0)
                            .on('end', function () {
                                path.classed('is-drawing', false)
                                    .style('stroke-dasharray', null)   // <-- Libera la restricción de longitud
                                    .style('stroke-dashoffset', null); // <-- Libera el desplazamiento
                            });
                    }),
                update => update.attr('d', linkPath),
                exit => exit.remove()
            );

            const nodeSel = nodeGroup.selectAll('g').data(nodes, d => d?.data?.id ?? Math.random());
            const nodeEnter = nodeSel.enter().append('g')
                .attr('class', 'lif-d3-node')
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .on('click', (event, d) => {
                    if (event.defaultPrevented) return;
                    if (event.target && event.target.closest && event.target.closest('.lif-node-toggle')) return;

                    if (config.clickBehavior === 'redirect') {
                        window.location.href = `${config.adminProfileUrl}/${d.data.id}`;
                    } else {
                        openPanel(d.data);
                    }
                });

            nodeEnter
                .classed('is-new', true)
                .style('--lif-delay', (d, i) => `${Math.min(600, (d.depth || 0) * 120 + i * 30)}ms`)
                .each(function () {
                    const el = d3.select(this);
                    setTimeout(() => el.classed('is-new', false), 700);
                });

            nodeEnter.append('foreignObject')
                .attr('x', -120)
                .attr('y', -20)
                .attr('width', 240)
                .attr('height', 180)
                .append('xhtml:div');

            const nodeMerge = nodeEnter.merge(nodeSel);
            nodeMerge.attr('transform', d => `translate(${d.x + (d.data._dx || 0)},${d.y + (d.data._dy || 0)})`);
            nodeMerge.select('foreignObject > div').html(d => nodeHtml(d));

            nodeMerge.call(d3.drag()
                .on('start', function (event) {
                    event.sourceEvent.stopPropagation();
                })
                .on('drag', function (event, d) { // <-- Usar 'function' normal
                    d.data._dx = (d.data._dx || 0) + event.dx;
                    d.data._dy = (d.data._dy || 0) + event.dy;

                    // Al usar 'this', D3 sabe exactamente cuál nodo seleccionaste originalmente, 
                    // evitando que se suelte si mueves el mouse muy rápido.
                    d3.select(this)
                        .attr('transform', `translate(${d.x + d.data._dx},${d.y + d.data._dy})`);

                    linkGroup.selectAll('path').attr('d', linkPath);
                })
            );

            nodeSel.exit().remove();
            applyFilters();
        }

        function nodeHtml(d) {
            const data = d.data;
            const initials = (data.name || '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
            const light = ' light';
            const hasToggle = d.children || d._children || data.has_more_children;
            const toggleSymbol = data.loadingChildren
                ? '...'
                : (d.children && d.children.length) ? '-' : (d._children ? '+' : (hasToggle ? '+' : ''));
            const directs = data.direct_children_count ?? 0;
            const hasPlan = !!data.plan;
            const planLabel = data.plan || labels.noPlan || 'Sin plan';
            const planColor = data.plan_color || '';
            const planIcon = data.plan_icon || '';
            const planBadgeIcon = planIcon ? `<span class="svg-icon-plan svg-xs">${planIcon}</span>` : '';

            const personalPoints = data.personal_points_formatted || '0';
            const networkPoints = data.network_points_formatted || '0';

            const avatarBg = planColor ? LifPlanBadge.hexToRgba(planColor, 0.18) : '#e5e7eb';
            const accent = hasPlan && planColor ? planColor : '#9ca3af';
            const accentText = LifPlanBadge.darkenColor(accent, 0.45);

            // --- NUEVAS LÍNEAS: Calculamos los colores de la animación ---
            const pulseBg = LifPlanBadge.hexToRgba(accent, 0.45);
            const pulseShadow = LifPlanBadge.hexToRgba(accent, 0);
            // -------------------------------------------------------------

            const toggleBtn = hasToggle ? `<button type="button" style="color:${accent}; border-color:${accent}" class="lif-node-toggle" aria-label="${labels.children || ''}">${toggleSymbol || '+'}</button>` : '';
            const loadingClass = data.loadingChildren ? ' is-loading' : '';
            return `
                <div class="lif-node${light}${loadingClass}" data-id="${data.id}" data-name="${data.name}" data-plan="${planLabel}"
                    data-discount="${data.discount ?? ''}" data-directs="${directs}" data-points="${networkPoints}"
                    data-level="${d.depth}" style="border: 1px solid ${accent}; border-top:5px solid ${accent}; --node-bg-pulse: ${pulseBg}; --node-shadow-pulse: ${pulseShadow};">
                    <div class="lif-node-head">
                        <div class="lif-avatar" style="background:${avatarBg}">${initials}</div>
                        <span class="lif-badge" data-plan="${planLabel}" style="background:${LifPlanBadge.hexToRgba(accent, 0.15)}; color:${accentText}">
                            ${planBadgeIcon}${planLabel}
                        </span>
                    </div>
                    <div style="margin-top:6px;">
                        <p class="lif-node-name">${data.name}</p>
                        ${data.document_number ? `<span class="lif-node-doc" style="color:${accentText}">${data.document_number}</span>` : ''}
                    </div>
                    <div class="lif-node-foot">
                        <div class="lif-node-metric personal">
                            <span>${labels.networkPoints || ''}</span>
                            <strong>${networkPoints}</strong>
                        </div>
                        <div class="lif-node-metric">
                            <span>${labels.personalPoints || ''}</span>
                            <strong style="color:${accentText}">${personalPoints}</strong>
                        </div>
                    </div>
                    ${toggleBtn}
                </div>
            `;
        }

        function toggle(d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
                render();
                return;
            } else if (d._children) {
                d.children = d._children;
                d._children = null;
                render();
                return;
            }

            if (d.data && d.data.has_more_children) {
                loadChildren(d);
            }
        }

        function loadChildren(d) {
            if (!config.childrenUrl || d.data.loadingChildren) return;
            d.data.loadingChildren = true;
            render();
            const params = new URLSearchParams({ node: d.data.id });
            fetch(`${config.childrenUrl}${config.childrenUrl.includes('?') ? '&' : '?'}${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !Array.isArray(res.data)) {
                        d.data.has_more_children = false;
                        return;
                    }
                    if (!res.data.length) {
                        d.children = null;
                        d._children = null;
                        d.data.has_more_children = false;
                        return;
                    }
                    const childNodes = res.data.map(child => {
                        const node = d3.hierarchy(child);
                        node.parent = d;
                        node.depth = (d.depth || 0) + 1;
                        return node;
                    });
                    d.children = childNodes;
                    d._children = null;
                    if (res.meta && typeof res.meta.has_more_children !== 'undefined') {
                        d.data.has_more_children = !!res.meta.has_more_children;
                    }
                })
                .catch(() => {
                    d.data.has_more_children = false;
                })
                .finally(() => {
                    d.data.loadingChildren = false;
                    render();
                });
        }

        function linkPath(d) {
            const sx = d.source.x + (d.source.data._dx || 0);
            const sy = d.source.y + (d.source.data._dy || 0);
            const tx = d.target.x + (d.target.data._dx || 0);
            const ty = d.target.y + (d.target.data._dy || 0);
            return d3.linkVertical().x(p => p.x).y(p => p.y)({ source: { x: sx, y: sy }, target: { x: tx, y: ty } });
        }

        let currentMaxLevel = 2;

        function applyFilters() {
            if (!searchInput) return;
            const q = searchInput.value.trim().toLowerCase();
            const level = levelSelect?.value || 'all';
            const planId = planSelect?.value || 'all';
            const levelId = planLevelSelect?.value || 'all';

            nodeGroup.selectAll('g.lif-d3-node').each(function (d) {
                const name = (d.data.name || '').toLowerCase();
                const id = String(d.data.id).toLowerCase();
                const nodeLevel = d.depth;

                const matchQuery = !q || name.includes(q) || id.includes(q);
                const matchLevel = (level === 'all') || nodeLevel <= parseInt(level, 10);

                let matchPlan = (planId === 'all');
                if (!matchPlan) {
                    const planData = allPlansData.find(p => String(p.id) === planId);
                    if (planData) {
                        const allowedIds = (levelId === 'all')
                            ? planData.levels.map(l => l.id)
                            : [parseInt(levelId)];
                        matchPlan = allowedIds.includes(d.data.plan_child_id);
                    }
                }

                d._hidden = !(matchQuery && matchLevel && matchPlan);
            });

            nodeGroup.selectAll('g.lif-d3-node')
                .classed('is-hidden', d => d._hidden);

            linkGroup.selectAll('path.lif-d3-link')
                .style('opacity', d => (d.source._hidden || d.target._hidden) ? 0.05 : 0.6);
        }

        if (levelSelect) levelSelect.addEventListener('change', applyFilters);
        if (planSelect) {
            planSelect.addEventListener('change', function () {
                updatePlanLevels();
                applyFilters();
            });
        }
        if (planLevelSelect) planLevelSelect.addEventListener('change', applyFilters);
        function showSearchError(message) {
            if (errorState) {
                const msgEl = errorState.querySelector('p');
                if (msgEl) msgEl.textContent = message || labels.searchError || '';
                state.error = true;
                state.loading = false;
                state.empty = false;
                applyState();
            }
        }

        function runSearch() {
            if (!searchInput) return;
            const q = searchInput.value.trim();
            if (!q) {
                showSearchError(labels.searchEmpty || '');
                return;
            }

            state.loading = true;
            state.error = false;
            state.empty = false;
            applyState();

            const params = new URLSearchParams({ q });
            fetch(`${config.searchUrl}${config.searchUrl.includes('?') ? '&' : '?'}${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data || !res.data.user_id) {
                        showSearchError(res.message || labels.searchNotFound || '');
                        return;
                    }
                    currentRootId = res.data.user_id;
                    loadedDepth = 2;
                    currentMaxLevel = 2;
                    if (levelSelect) levelSelect.value = 'all';
                    if (searchInput) searchInput.value = '';
                    if (resetTreeBtn) resetTreeBtn.style.display = currentRootId !== baseUserId ? 'inline-flex' : 'none';
                    loadTree(loadedDepth, null, null, currentRootId);
                    loadStats(currentRootId);
                })
                .catch(() => {
                    showSearchError(labels.searchError || '');
                });
        }

        function expandAllNodes(d) {
            if (d._children) {
                d.children = d._children;
                d._children = null;
            }
            if (d.children) d.children.forEach(expandAllNodes);
        }

        function applyLevel(d, level) {
            if (d.depth >= level && d.children) {
                d._children = d.children;
                d.children = null;
            }
            if (d.depth < level && d._children) {
                d.children = d._children;
                d._children = null;
            }
            if (d.children) d.children.forEach(child => applyLevel(child, level));
            if (d._children) d._children.forEach(child => applyLevel(child, level));
        }

    function updateLoadNextAvailability() {
        if (!loadNext) return;
        if (hasMoreLevels === false) {
            loadNext.disabled = true;
            return;
        }
        const cap = getEffectiveMaxDepth();
        if (!cap) {
            loadNext.disabled = false;
            return;
        }
        loadNext.disabled = loadedDepth >= cap;
    }
        if (expandAll) {
            expandAll.addEventListener('click', function () {
                if (!root) return;
                expandAllNodes(root);
                render();
            });
        }

        if (collapseAll) {
            collapseAll.addEventListener('click', function () {
                if (!root) return;
                currentMaxLevel = 1;
                if (levelSelect) levelSelect.value = '1';
                applyLevel(root, currentMaxLevel);
                render();
            });
        }

        if (loadNext) {
            loadNext.addEventListener('click', function () {
                if (!root) return;
                if (hasMoreLevels === false) return;
                const cap = getEffectiveMaxDepth();
                if (cap && loadedDepth >= cap) return;
                const btn = loadNext;
                btn.disabled = true;
                const prevText = btn.textContent;
                btn.textContent = labels.loading || 'Cargando...';
                loadedDepth = cap ? Math.min(loadedDepth + 1, cap) : (loadedDepth + 1);
                currentMaxLevel = loadedDepth;
                loadTree(loadedDepth, null, null, currentRootId);
                updateGenerationsSelector(getEffectiveMaxDepth() || loadedDepth);
                updateLoadNextAvailability();
                btn.disabled = false;
                btn.textContent = prevText;
            });
        }

        // Ejecutar búsqueda al oprimir Enter
        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const q = searchInput.value.trim();
                    if (q) {
                        e.preventDefault(); // Evita que el formulario se recargue si existe uno
                        runSearch();
                    }
                }
            });

            // Tu código actual de input para filtros se mantiene igual:
            searchInput.addEventListener('input', function () {
                applyFilters();
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', function () {
                runSearch();
            });
        }

        if (resetTreeBtn) {
            resetTreeBtn.addEventListener('click', function () {
                if (!baseUserId) return;
                currentRootId = baseUserId;
                loadedDepth = 2;
                currentMaxLevel = 2;
                if (levelSelect) levelSelect.value = 'all';
                if (searchInput) searchInput.value = '';
                resetTreeBtn.style.display = 'none';
                loadTree(loadedDepth, null, null, currentRootId);
                loadStats(currentRootId);
            });
        }

        // Fullscreen Logic
        const fullscreenBtn = document.getElementById('lif-fullscreen');
        const networkPage = document.querySelector('.lif-network-page');

        if (fullscreenBtn && networkPage) {
            fullscreenBtn.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    networkPage.requestFullscreen?.() || networkPage.webkitRequestFullscreen?.() || networkPage.msRequestFullscreen?.();
                } else {
                    document.exitFullscreen?.() || document.webkitExitFullscreen?.() || document.msExitFullscreen?.();
                }
            });

            const handleFsChange = () => {
                const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
                const icon = fullscreenBtn.querySelector('i');
                if (icon) icon.className = isFs ? 'ti-close' : 'ti-fullscreen';
                networkPage.classList.toggle('is-fullscreen', !!isFs);
                setTimeout(render, 100);
            };

            document.addEventListener('fullscreenchange', handleFsChange);
            document.addEventListener('webkitfullscreenchange', handleFsChange);
            document.addEventListener('msfullscreenchange', handleFsChange);
        }

        // Carga inicial
        loadTree(loadedDepth, null, null, currentRootId);
        loadStats(currentRootId);
        loadPlans();
    }
})();
