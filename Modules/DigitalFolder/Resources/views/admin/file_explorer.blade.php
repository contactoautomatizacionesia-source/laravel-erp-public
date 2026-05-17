@extends('backEnd.master')

@section('styles')
@parent
<link rel="stylesheet" href="{{ asset(asset_path('modules/digitalfolder/css/file-explorer-shared.css')) }}">
<link rel="stylesheet" href="{{ asset(asset_path('modules/digitalfolder/css/file-explorer-backend.css')) }}">
@endsection

@section('mainContent')
<x-admin.section>
<div id="file-explorer-app" class="px-1">
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="box_header mb-3">
                        <div class="main-title d-flex align-items-center">
                            <h3 class="mb-0 mr-30">
                                <i class="fas fa-folder-open mr-2"></i>
                                {{ __('common.file_explorer') ?? 'File Explorer' }}
                            </h3>
                        </div>
                    </div>

                    <div class="file-explorer-container">
                        {{-- Toolbar --}}
                        <div class="fe-toolbar">
                            @if($isSuperAdmin)
                                @if($canWrite)
                                    <button class="btn-toolkit btn-primary btn-icon" onclick="FileExplorer.showUploadModal()">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>{{ __('common.upload') ?? 'Upload' }}</span>
                                    </button>
                                    <button class="btn-toolkit btn-outline btn-icon" onclick="FileExplorer.showNewFolderModal()">
                                        <i class="fas fa-folder-plus"></i>
                                        <span>{{ __('common.new_folder') ?? 'New Folder' }}</span>
                                    </button>
                                @endif

                                @if(isset($users) && $users->count() > 0)
                                    <button class="btn-toolkit btn-outline btn-icon" onclick="FileExplorer.showCreateUserFolderModal()">
                                        <i class="fas fa-user-plus"></i>
                                        <span>{{ __('common.create_user_folder') ?? 'Create User Folder' }}</span>
                                    </button>
                                @endif

                                <div class="fe-toolbar-divider"></div>
                            @endif

                            <button class="btn-toolkit btn-outline btn-icon" onclick="FileExplorer.refresh()" data-tooltip="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>

                            <div class="fe-view-toggle">
                                <button class="active fe-tooltip" onclick="FileExplorer.setView('grid')" data-view="grid" data-tooltip="Grid View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button class="fe-tooltip" onclick="FileExplorer.setView('list')" data-view="list" data-tooltip="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>

                            <div class="fe-search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="{{ __('common.search') ?? 'Search files and folders' }}..." id="fe-search-input">
                            </div>
                        </div>

                        {{-- Breadcrumb --}}
                        <div class="fe-breadcrumb" id="fe-breadcrumb">
                            @foreach($breadcrumb as $index => $crumb)
                                <button class="fe-breadcrumb-item {{ $loop->last ? 'active' : '' }}"
                                    onclick="FileExplorer.navigateTo({{ $crumb->id }})">
                                    @if($loop->first)
                                        <i class="fas fa-home"></i>
                                    @endif
                                    {{ $crumb->name }}
                                </button>
                                @if(!$loop->last)
                                    <span class="fe-breadcrumb-separator">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        {{-- Main Content --}}
                        <div class="fe-main">
                            @if($isSuperAdmin)
                                @include('digitalfolder::admin.partials._sidebar')
                            @endif

                            {{-- Content Area --}}
                            <div class="fe-content" id="fe-content">
                                {{-- Grid View --}}
                                <div class="fe-grid active" id="fe-grid">
                                    @if($currentFolder)
                                        @forelse($currentFolder->children as $folder)
                                            <button class="fe-item rounded-md border" data-type="folder" data-id="{{ $folder->id }}"
                                                data-can-be-deleted="{{ $folder->can_be_deleted ? '1' : '0' }}"
                                                data-can-be-modified="{{ $folder->can_be_modified ? '1' : '0' }}"
                                                ondblclick="FileExplorer.navigateTo({{ $folder->id }})"
                                                oncontextmenu="FileExplorer.showContextMenu(event, 'folder', {{ $folder->id }})">
                                                @if($folder->type === 'user')
                                                    <i class="fe-item-icon fas fa-folder-open user-folder"></i>
                                                @else
                                                    <i class="fe-item-icon fas fa-folder folder"></i>
                                                @endif
                                                <span class="fe-item-name">{{ $folder->name }}</span>
                                                @if($folder->type === 'user' && $folder->owner)
                                                    <span class="fe-item-info">{{ $folder->owner->email }}</span>
                                                @endif
                                            </button>
                                        @empty
                                        @endforelse

                                        @forelse($currentFolder->files as $file)
                                            <button class="fe-item" data-type="file" data-id="{{ $file->id }}"
                                                ondblclick="FileExplorer.previewFile({{ $file->id }})"
                                                oncontextmenu="FileExplorer.showContextMenu(event, 'file', {{ $file->id }})">
                                                <i class="fe-item-icon {{ $file->icon_class }}"></i>
                                                <span class="fe-item-name">{{ $file->original_name }}</span>
                                                <span class="fe-item-info">{{ $file->formatted_size }}</span>
                                            </button>
                                        @empty
                                        @endforelse

                                        @if($currentFolder->children->isEmpty() && $currentFolder->files->isEmpty())
                                            <div class="fe-empty" style="grid-column: 1 / -1;">
                                                <i class="fas fa-folder-open"></i>
                                                <p>{{ __('common.folder_is_empty') ?? 'This folder is empty' }}</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                {{-- List View --}}
                                <div class="fe-list" id="fe-list">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;"></th>
                                                <th>{{ __('common.name') ?? 'Name' }}</th>
                                                <th>{{ __('common.type') ?? 'Type' }}</th>
                                                <th>{{ __('common.size') ?? 'Size' }}</th>
                                                <th>{{ __('common.date') ?? 'Date' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fe-list-body">
                                            @if($currentFolder)
                                                @foreach($currentFolder->children as $folder)
                                                    <tr data-type="folder" data-id="{{ $folder->id }}"
                                                        data-can-be-deleted="{{ $folder->can_be_deleted ? '1' : '0' }}"
                                                        data-can-be-modified="{{ $folder->can_be_modified ? '1' : '0' }}"
                                                        ondblclick="FileExplorer.navigateTo({{ $folder->id }})"
                                                        oncontextmenu="FileExplorer.showContextMenu(event, 'folder', {{ $folder->id }})">
                                                        <td class="fe-list-icon">
                                                            <i class="fas fa-folder {{ $folder->type === 'user' ? 'text-info' : 'text-warning' }}"></i>
                                                        </td>
                                                        <td>{{ $folder->name }}</td>
                                                        <td>{{ __('common.folder') ?? 'Folder' }}</td>
                                                        <td>-</td>
                                                        <td>{{ $folder->created_at->format('Y-m-d H:i') }}</td>
                                                    </tr>
                                                @endforeach

                                                @foreach($currentFolder->files as $file)
                                                    <tr data-type="file" data-id="{{ $file->id }}"
                                                        ondblclick="FileExplorer.previewFile({{ $file->id }})"
                                                        oncontextmenu="FileExplorer.showContextMenu(event, 'file', {{ $file->id }})">
                                                        <td class="fe-list-icon">
                                                            <i class="{{ $file->icon_class }}"></i>
                                                        </td>
                                                        <td>{{ $file->original_name }}</td>
                                                        <td>{{ strtoupper($file->extension) }}</td>
                                                        <td>{{ $file->formatted_size }}</td>
                                                        <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('digitalfolder::admin.partials._modals')
</div>
</x-admin.section>
@endsection

@push('scripts')
<script>
// Globals consumed by file-explorer-backend.js
const BREADCRUMB_IDS = @json($breadcrumb->pluck('id')->toArray());
const FE_CSRF = '{{ csrf_token() }}';

const FE_CONFIG = {
    currentFolderId: {{ $currentFolder->id ?? 'null' }},
    canWrite: {{ $canWrite ? 'true' : 'false' }},
    isSuperAdmin: {{ $isSuperAdmin ? 'true' : 'false' }},
};

const FE_ROUTES = {
    index:           '{{ route('admin.file-explorer.index') }}',
    contents:        '{{ route('admin.file-explorer.contents') }}',
    download:        '{{ route('admin.file-explorer.download', '') }}',
    folderCreate:    '{{ route('admin.file-explorer.folder.create') }}',
    folderUserCreate:'{{ route('admin.file-explorer.folder.user-create') }}',
    folderRename:    '{{ route('admin.file-explorer.folder.rename') }}',
    folderDelete:    '{{ route('admin.file-explorer.folder.delete') }}',
    folderChildren:  '{{ route('admin.file-explorer.folder.children') }}',
    filesUpload:     '{{ route('admin.file-explorer.files.upload') }}',
    filesRename:     '{{ route('admin.file-explorer.files.rename') }}',
    filesDelete:     '{{ route('admin.file-explorer.files.delete') }}',
    filesPreview:    '{{ route('admin.file-explorer.files.preview', '') }}',
};

const FE_TRANS = {
    error:                    '{{ __("common.error_message") ?? "Error" }}',
    are_you_sure:             '{{ __("common.are_you_sure") ?? "Are you sure?" }}',
    name:                     '{{ __("common.name") ?? "Name" }}',
    type:                     '{{ __("common.type") ?? "Type" }}',
    size:                     '{{ __("common.size") ?? "Size" }}',
    date:                     '{{ __("common.date") ?? "Date" }}',
    folder:                   '{{ __("common.folder") ?? "Folder" }}',
    folder_is_empty:          '{{ __("common.folder_is_empty") ?? "This folder is empty" }}',
    please_enter_folder_name: '{{ __("common.please_enter_folder_name") ?? "Please enter folder name" }}',
    please_enter_name:        '{{ __("common.please_enter_name") ?? "Please enter name" }}',
    please_select_files:      '{{ __("common.please_select_files") ?? "Please select files" }}',
    please_select_user:       '{{ __("common.please_select_user") ?? "Please select a user" }}',
    uploading:                '{{ __("common.uploading") ?? "Uploading" }}',
    upload:                   '{{ __("common.upload") ?? "Upload" }}',
    create:                   '{{ __("common.create") ?? "Create" }}',
    preview_not_available:    '{{ __("common.preview_not_available") ?? "Preview not available for this file type" }}',
    error_loading_preview:    '{{ __("common.error_loading_preview") ?? "Error loading preview" }}',
    no_results_title:         '{{ __("common.no_results_for") ?? "No results for %s" }}',
    no_results_hint:          '{{ __("common.no_results_hint") ?? "Try a different search term" }}',
    download:                 '{{ __("common.download") ?? "Download" }}',
    loading:                  '{{ __("common.loading") ?? "Cargando..." }}',
};
</script>
<script src="{{ asset(asset_path('modules/digitalfolder/js/file-explorer-backend.js')) }}"></script>
@endpush
