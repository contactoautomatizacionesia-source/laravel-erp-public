@extends('frontend.amazy.pages.profile.layouts._profile_layout')

@section('title')
    {{ __('common.file_explorer') }}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset(asset_path('modules/digitalfolder/css/file-explorer-shared.css')) }}">
<link rel="stylesheet" href="{{ asset(asset_path('modules/digitalfolder/css/file-explorer-frontend.css')) }}">
@endpush

@section('profile_content')
    <div class="dashboard_white_box_header d-flex align-items-center gap_20 mb_20">
        <h3 class="font_20 f_w_700 mb-0">{{ __('common.file_explorer') }}</h3>
    </div>

    @if(isset($noAccess) && $noAccess)
        <div class="fe-container">
            <div class="fe-no-access">
                <i class="fas fa-folder-open"></i>
                <h4>{{ __('common.no_files_available') ?? 'No files available' }}</h4>
                <p>{{ __('common.contact_admin_for_access') ?? 'Contact your administrator to get access to files.' }}</p>
            </div>
        </div>
    @else
        <div class="fe-container">
            <div class="fe-header">
                <div class="fe-breadcrumb">
                    @foreach($breadcrumb as $index => $crumb)
                        <a href="{{ route('frontend.file-explorer.folder', $crumb->id) }}"
                        class="fe-breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if($loop->first)
                                <i class="fas fa-home"></i>
                            @endif
                            {{ $crumb->name }}
                        </a>
                        @if(!$loop->last)
                            <span class="fe-breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    @endforeach
                </div>

                <div class="fe-actions">
                    <a href="{{ route('frontend.file-explorer.index') }}" class="fe-btn">
                        <i class="fas fa-sync-alt"></i>
                        {{ __('common.refresh') }}
                    </a>
                </div>
            </div>

            <div class="fe-content">
                @if($currentFolder)
                    @php
                        $hasContent = $currentFolder->children->count() > 0 || $currentFolder->files->count() > 0;
                    @endphp

                    @if($hasContent)
                        <div class="fe-grid">
                            {{-- Folders --}}
                            @foreach($currentFolder->children as $folder)
                                <a href="{{ route('frontend.file-explorer.folder', $folder->id) }}" class="fe-item">
                                    <i class="fe-item-icon fas fa-folder folder"></i>
                                    <span class="fe-item-name">{{ $folder->name }}</span>
                                </a>
                            @endforeach

                            {{-- Files --}}
                            @foreach($currentFolder->files as $file)
                                @php
                                    $iconClass = 'fa-file';
                                    $colorClass = '';
                                    $ext = strtolower($file->extension);

                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                        $iconClass = 'fa-file-image';
                                        $colorClass = 'fe-icon-img';
                                    } elseif ($ext === 'pdf') {
                                        $iconClass = 'fa-file-pdf';
                                        $colorClass = 'fe-icon-pdf';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        $iconClass = 'fa-file-word';
                                        $colorClass = 'fe-icon-doc';
                                    } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                        $iconClass = 'fa-file-excel';
                                        $colorClass = 'fe-icon-xls';
                                    } elseif (in_array($ext, ['mp4', 'avi', 'mov', 'wmv'])) {
                                        $iconClass = 'fa-file-video';
                                        $colorClass = 'fe-icon-video';
                                    } elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) {
                                        $iconClass = 'fa-file-audio';
                                        $colorClass = 'fe-icon-audio';
                                    } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                                        $iconClass = 'fa-file-archive';
                                        $colorClass = 'fe-icon-zip';
                                    } elseif (in_array($ext, ['html', 'css', 'js', 'php', 'json', 'xml'])) {
                                        $iconClass = 'fa-file-code';
                                        $colorClass = 'fe-icon-code';
                                    }
                                @endphp
                                <div class="fe-item" onclick="fePreviewFile({{ $file->id }})" title="{{ $file->original_name }}" style="cursor:pointer;">
                                    <i class="fe-item-icon fas {{ $iconClass }} {{ $colorClass }} file"></i>
                                    <span class="fe-item-name">{{ $file->original_name }}</span>
                                    <span class="fe-item-info">{{ $file->formatted_size }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="fe-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>{{ __('common.folder_is_empty') }}</p>
                        </div>
                    @endif
                @else
                    <div class="fe-empty">
                        <i class="fas fa-folder-open"></i>
                        <p>{{ __('common.folder_not_found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Preview Modal -->
    <div class="fe-modal-backdrop" id="fe-preview-modal" onclick="feClosePreviewOnBackdrop(event)">
        <div class="fe-modal">
            <div class="fe-modal-header">
                <h5 id="fe-preview-title"><i class="fas fa-eye mr-2"></i>{{ __('common.preview') ?? 'Preview' }}</h5>
                <button class="fe-modal-close" onclick="feClosePreview()">&times;</button>
            </div>
            <div class="fe-modal-body">
                <div id="fe-preview-container">
                    <div class="fe-spinner"></div>
                </div>
            </div>
            <div class="fe-modal-footer">
                <button class="fe-btn" onclick="feClosePreview()">{{ __('common.close') ?? 'Close' }}</button>
                <a id="fe-preview-download-btn" href="#" class="fe-btn primary">
                    <i class="fas fa-download"></i>
                    {{ __('common.download') ?? 'Download' }}
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var feCurrentPreviewId = null;
var fePreviewUrl = '{{ route('frontend.file-explorer.preview', '') }}';
var feDownloadUrl = '{{ route('frontend.file-explorer.download', '') }}';
var feServeUrl = '{{ route('frontend.file-explorer.serve', '') }}';

function fePreviewFile(fileId) {
    feCurrentPreviewId = fileId;
    document.getElementById('fe-preview-title').innerHTML = '<i class="fas fa-eye mr-2"></i>{{ __("common.preview") ?? "Preview" }}';
    document.getElementById('fe-preview-container').innerHTML = '<div class="fe-spinner"></div>';
    document.getElementById('fe-preview-download-btn').href = feDownloadUrl + '/' + fileId;
    document.getElementById('fe-preview-modal').classList.add('open');

    fetch(fePreviewUrl + '/' + fileId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                document.getElementById('fe-preview-container').innerHTML =
                    '<div class="fe-preview-empty"><i class="fas fa-exclamation-circle"></i><p>' + data.error + '</p></div>';
                return;
            }

            document.getElementById('fe-preview-title').textContent = data.file.original_name;

            var content = '';
            if (data.is_image) {
                content = '<img src="' + feServeUrl + '/' + fileId + '" class="fe-preview-image" alt="' + feEscapeHtml(data.file.original_name) + '">';
            } else if (data.content !== undefined) {
                content = '<pre class="fe-preview-text">' + feEscapeHtml(data.content) + '</pre>';
            } else {
                content = '<div class="fe-preview-empty"><i class="fas fa-file" style="font-size:64px;opacity:.3;display:block;margin-bottom:16px;"></i><p>{{ __("common.preview_not_available") ?? "Preview not available for this file type" }}</p></div>';
            }

            document.getElementById('fe-preview-container').innerHTML = content;
        })
        .catch(function() {
            document.getElementById('fe-preview-container').innerHTML =
                '<div class="fe-preview-empty"><i class="fas fa-exclamation-triangle"></i><p>{{ __("common.error_loading_preview") ?? "Error loading preview" }}</p></div>';
        });
}

function feClosePreview() {
    document.getElementById('fe-preview-modal').classList.remove('open');
    feCurrentPreviewId = null;
}

function feClosePreviewOnBackdrop(event) {
    if (event.target === document.getElementById('fe-preview-modal')) {
        feClosePreview();
    }
}

function feEscapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') feClosePreview();
});
</script>
@endpush