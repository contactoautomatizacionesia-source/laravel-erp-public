@php
    $documents = [
        [
            'label' => __('amazy.front_document_image'),
            'file' => $customerProfile?->front_id_image ?? null,
        ],
        [
            'label' => __('amazy.back_document_image'),
            'file' => $customerProfile?->back_id_image ?? null,
        ]
    ];
@endphp

<div class="row">
    @foreach($documents as $doc)
        <div class="col-md-6 col-document-custom mb_20">
            <div class="bg-white p_20 br_10 h-100 text-center" style="border-radius: 15px;">
                <p class="mb_15 text-muted font-weight-bold">
                    {{ $doc['label'] }}
                </p>
                
                @if($doc['file'])
                    @php
                        $serveUrl = route('admin.file-explorer.files.serve_path', ['path' => $doc['file']]);
                    @endphp
                    <div class="img_container p-2" style="border: 1px dashed #ccc; border-radius: 10px;">
                        <a href="{{ $serveUrl }}" target="_blank">
                            <img src="{{ $serveUrl }}"
                                alt="{{ $doc['label'] }}"
                                class="img-fluid"
                                style="max-height: 200px; object-fit: contain;">
                        </a>
                        <div class="mt-2">
                            <a href="{{ $serveUrl }}" download class="btn btn-sm btn-secondary">                                <i class="ti-download"></i> {{ __('common.download') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-center" 
                        style="height: 150px; background: #f8f9fa; border-radius: 10px; border: 1px dashed #ddd;">
                        <span class="text-muted">{{ __('common.no_document_uploaded') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>