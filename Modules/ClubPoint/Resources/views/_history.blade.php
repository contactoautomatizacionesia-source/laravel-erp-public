@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_page_css/staff_create.css'))}}" />
@endsection

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header">
                    <div class="main-title d-flex">
                        <h3 class="mb-0 mr-30 font-bold">{{ __('common.history_changes_points') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="white_box_30px box_shadow_white">
                    <h3 class="mb-3 fs-20 font-weight-bold">{{$clubpoint->product_name}}</h3>


                        <div class="row">
                            <div class="col-lg-6">
                                <h3 class="mb-3 fs-18 text-black">{{__('common.history_points')}}</h3>
                                <div class="mb-20" style="width: 100%; max-width: 900px;">
                                    <canvas id="pointsChart"></canvas>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <table class="table" id="historypointsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">{{ __('common.sl') }}</th>
                                                    <th scope="col">{{ __('common.previus_points') }}</th>
                                                    <th scope="col">{{ __('common.new_points') }}</th>
                                                    <th scope="col">{{ __('common.date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h3 class="mb-3 fs-18 text-black">{{__('common.history_price')}}</h3>
                                <div class="mb-20" style="width: 100%; max-width: 900px;">
                                    <canvas id="priceChart"></canvas>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <table class="table" id="historypricesTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">{{ __('common.sl') }}</th>
                                                    <th scope="col">{{ __('common.previus_price') }}</th>
                                                    <th scope="col">{{ __('common.new_price') }}</th>
                                                    <th scope="col">{{ __('common.date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
<script>
    moment.locale('es');

    var productId = {{ $clubpoint->id }};
    var historyUrl = "{{ route('clubpoint.get-history') }}";

    // ==========================================
    // Tabla de historico de puntos y precios
    // ==========================================

    var columnData = [
        { data: 'DT_RowIndex', name: 'id', render: function(data) {
            return numbertrans(data);
        }},
        { data: 'previous_value', name: 'previous_value' },
        { data: 'new_value', name: 'new_value' },
        { data: 'date', name: 'date' },
    ];

    historyTable('#historypointsTable', 'points');
    historyTable('#historypricesTable', 'price');

    function historyTable(id, type) {
        $(id).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: false,
            ajax: {
                url: historyUrl,
                data: { type: type, product_id: productId }
            },
            columns: columnData,
            bLengthChange: false,
            bDestroy: true,
            language: {
                paginate: {
                    next: "<i class='ti-arrow-right'></i>",
                    previous: "<i class='ti-arrow-left'></i>"
                }
            },
            dom: 'Brtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'Copy',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $("#header_title").text(),
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'PDF',
                    exportOptions: { columns: ':not(:last-child)' },
                    orientation: 'landscape',
                    pageSize: 'A4',
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $("#header_title").text(),
                    exportOptions: { columns: ':not(:last-child)' }
                },
            ],
            columnDefs: [{ targets: -1, responsivePriority: 1 }],
        });
    }

    // ==========================================
    // Gráficas dinámicas via AJAX
    // ==========================================

    var priceChartInstance = null;
    var pointsChartInstance = null;

    function buildChart(canvasId, chartInstanceVar, label, borderColor, bgColor, yLabel, isCurrency, data) {
        var ctx = document.getElementById(canvasId).getContext('2d');

        if (chartInstanceVar) {
            chartInstanceVar.destroy();
        }

        return new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: borderColor,
                    backgroundColor: bgColor,
                    fill: true,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    xAxes: [{ type: 'time' }],
                    yAxes: [{
                        scaleLabel: { display: true, labelString: yLabel },
                        ticks: {
                            callback: function(value) {
                                return isCurrency ? '$ ' + value.toLocaleString() : value;
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        title: function(tooltipItem) {
                            return moment(tooltipItem[0].xLabel).format('DD MMM YYYY');
                        },
                        label: function(tooltipItem) {
                            var val = isCurrency
                                ? '$ ' + tooltipItem.yLabel.toLocaleString()
                                : tooltipItem.yLabel;
                            return label + ': ' + val;
                        }
                    }
                }
            }
        });
    }

    function loadChartData(type, canvasId, label, borderColor, bgColor, yLabel, isCurrency, instanceRef) {
        $.get(historyUrl, { type: type, product_id: productId, draw: 1, start: 0, length: 1000 }, function(response) {
            var rows = response.data || [];
            var chartData = rows.map(function(row) {
                return {
                    x: row.created_at || row.date,
                    y: isCurrency ? parseFloat(row.new_price_raw) : parseFloat(row.new_points_raw)
                };
            }).filter(function(d) { return !isNaN(d.y); });

            if (instanceRef === 'price') {
                priceChartInstance = buildChart(canvasId, priceChartInstance, label, borderColor, bgColor, yLabel, isCurrency, chartData);
            } else {
                pointsChartInstance = buildChart(canvasId, pointsChartInstance, label, borderColor, bgColor, yLabel, isCurrency, chartData);
            }
        });
    }

    // Cargamos los datos reales para las gráficas via endpoint dedicado
    $.get("{{ route('clubpoint.get-history-chart') }}", { product_id: productId }, function(response) {
        // Gráfica de precios
        var priceData = (response.price || []).map(function(r) {
            return { x: r.date, y: parseFloat(r.new_value) };
        });
        priceChartInstance = buildChart(
            'priceChart', priceChartInstance,
            'Precio del producto', '#d54830', 'rgba(213,72,48,0.1)',
            'Precio', true, priceData
        );

        // Gráfica de puntos
        var pointsData = (response.points || []).map(function(r) {
            return { x: r.date, y: parseFloat(r.new_value) };
        });
        pointsChartInstance = buildChart(
            'pointsChart', pointsChartInstance,
            'Puntos del producto', '#e99466', 'rgba(233,148,102,0.1)',
            'Puntos', false, pointsData
        );
    });
</script>
@endpush
