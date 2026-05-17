{{--
    Parámetros recibidos:
    $items: Array con ['label', 'value']
    $colClass: (Opcional) String con las clases de columna. Default: 'col-xl-4 col-lg-6 col-md-6'
--}}
<div class="row">
    @foreach($items as $item)
        <div class="{{ $colClass ?? 'col-md-6' }} mb_20">
            <div class="d-flex flex-column justify-content-center">
                <span class="card-label">
                    {{ $item['label'] }}
                </span>
                <input class="primary_input_field border-0" type="text" value="{{ $item['value'] }}" readonly style="background-color: #f8fafc!important">
            </div>
        </div>
    @endforeach
</div>
