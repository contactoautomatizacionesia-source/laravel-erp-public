{{-- Context Menu --}}
<div class="fe-context-menu" id="fe-context-menu">
    <button class="fe-context-item" onclick="FileExplorer.openItem()">
        <i class="fas fa-folder-open"></i>
        <span>{{ __('common.open') ?? 'Open' }}</span>
    </button>
    <button class="fe-context-item" onclick="FileExplorer.downloadItem()" id="ctx-download">
        <i class="fas fa-download"></i>
        <span>{{ __('common.download') ?? 'Download' }}</span>
    </button>
    <div class="fe-context-divider"></div>
    <button class="fe-context-item" onclick="FileExplorer.showRenameModal()" id="ctx-rename">
        <i class="fas fa-edit"></i>
        <span>{{ __('common.rename') ?? 'Rename' }}</span>
    </button>
    <div class="fe-context-divider" id="ctx-delete-divider"></div>
    <button class="fe-context-item danger" onclick="FileExplorer.deleteItem()" id="ctx-delete">
        <i class="fas fa-trash"></i>
        <span>{{ __('common.delete') ?? 'Delete' }}</span>
    </button>
</div>

{{-- New Folder Modal --}}
<div class="fe-modal-backdrop" id="modal-new-folder">
    <div class="fe-modal">
        <div class="fe-modal-header">
            <h5><i class="fas fa-folder-plus mr-2"></i>{{ __('common.new_folder') ?? 'New Folder' }}</h5>
            <button class="fe-modal-close" onclick="FileExplorer.closeModal('modal-new-folder')">&times;</button>
        </div>
        <div class="fe-modal-body">
            <div class="fe-form-group">
                <label class="fe-label" for="new-folder-name">{{ __('common.folder_name') ?? 'Folder Name' }}</label>
                <input type="text" class="fe-input" id="new-folder-name" placeholder="{{ __('common.enter_folder_name') ?? 'Enter folder name' }}">
            </div>
        </div>
        <div class="fe-modal-footer">
            <button class="btn-toolkit btn-secondary-outline" onclick="FileExplorer.closeModal('modal-new-folder')">
                {{ __('common.cancel') ?? 'Cancel' }}
            </button>
            <button class="btn-toolkit btn-primary" id="btn-create-folder" onclick="FileExplorer.createFolder()">
                {{ __('common.create') ?? 'Create' }}
            </button>
        </div>
    </div>
</div>

{{-- Upload Modal --}}
<div class="fe-modal-backdrop" id="modal-upload">
    <div class="fe-modal">
        <div class="fe-modal-header">
            <h5><i class="fas fa-upload mr-2"></i>{{ __('common.upload_files') ?? 'Upload Files' }}</h5>
            <button class="fe-modal-close" onclick="FileExplorer.closeModal('modal-upload')">&times;</button>
        </div>
        <div class="fe-modal-body">
            <div class="fe-upload-area" id="upload-dropzone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>{{ __('common.drag_files_here') ?? 'Drag and drop files here' }}</p>
                <small>{{ __('common.or_click_to_browse') ?? 'or click to browse' }}</small>
                <input type="file" id="file-input" multiple style="display: none;">
            </div>
            <div class="fe-upload-files" id="upload-files-list"></div>
        </div>
        <div class="fe-modal-footer">
            <button class="fe-toolbar-btn" onclick="FileExplorer.closeModal('modal-upload')">
                {{ __('common.cancel') ?? 'Cancel' }}
            </button>
            <button class="fe-toolbar-btn primary" onclick="FileExplorer.doUpload()" id="btn-upload">
                {{ __('common.upload') ?? 'Upload' }}
            </button>
        </div>
    </div>
</div>

{{-- Rename Modal --}}
<div class="fe-modal-backdrop" id="modal-rename">
    <div class="fe-modal">
        <div class="fe-modal-header">
            <h5><i class="fas fa-edit mr-2"></i>{{ __('common.rename') ?? 'Rename' }}</h5>
            <button class="fe-modal-close" onclick="FileExplorer.closeModal('modal-rename')">&times;</button>
        </div>
        <div class="fe-modal-body">
            <div class="fe-form-group">
                <label class="fe-label" for="rename-input">{{ __('common.new_name') ?? 'New Name' }}</label>
                <input type="text" class="fe-input" id="rename-input">
            </div>
        </div>
        <div class="fe-modal-footer">
            <button class="fe-toolbar-btn" onclick="FileExplorer.closeModal('modal-rename')">
                {{ __('common.cancel') ?? 'Cancel' }}
            </button>
            <button class="fe-toolbar-btn primary" onclick="FileExplorer.renameItem()">
                {{ __('common.save') ?? 'Save' }}
            </button>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="fe-modal-backdrop" id="modal-preview">
    <div class="fe-modal" style="max-width: 800px;">
        <div class="fe-modal-header">
            <h5><i class="fas fa-eye mr-2"></i><span id="preview-title">{{ __('common.preview') ?? 'Preview' }}</span></h5>
            <button class="fe-modal-close" onclick="FileExplorer.closeModal('modal-preview')">&times;</button>
        </div>
        <div class="fe-modal-body">
            <div class="fe-preview-container" id="preview-container">
                <div class="fe-loading">
                    <div class="fe-spinner"></div>
                </div>
            </div>
        </div>
        <div class="fe-modal-footer">
            <button class="fe-toolbar-btn" onclick="FileExplorer.closeModal('modal-preview')">
                {{ __('common.close') ?? 'Close' }}
            </button>
            <button class="fe-toolbar-btn primary" id="btn-preview-download" onclick="FileExplorer.downloadCurrentPreview()">
                <i class="fas fa-download"></i>
                {{ __('common.download') ?? 'Download' }}
            </button>
        </div>
    </div>
</div>

{{-- Create User Folder Modal (Superadmin only) --}}
@if($isSuperAdmin && isset($users) && $users->count() > 0)
<div class="fe-modal-backdrop" id="modal-user-folder">
    <div class="fe-modal">
        <div class="fe-modal-header">
            <h5><i class="fas fa-user-plus mr-2"></i>{{ __('common.create_user_folder') ?? 'Create User Folder' }}</h5>
            <button class="fe-modal-close" onclick="FileExplorer.closeModal('modal-user-folder')">&times;</button>
        </div>
        <div class="fe-modal-body">
            <div class="fe-form-group">
                <label class="fe-label" for="user-search">{{ __('common.select_user') ?? 'Select User' }}</label>
                <input type="text" class="fe-input mb-2" id="user-search" placeholder="{{ __('common.search_user') ?? 'Search user...' }}">
                <div class="fe-user-select" id="user-select-list">
                    @foreach($users as $user)
                        <button class="fe-user-item" data-user-id="{{ $user->id }}" onclick="FileExplorer.selectUser({{ $user->id }}, this)">
                            <div class="fe-user-avatar">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</div>
                            <div class="fe-user-info">
                                <div class="fe-user-name">{{ $user->name }}</div>
                                <div class="fe-user-email">{{ $user->email }}</div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="fe-modal-footer">
            <button class="btn-toolkit btn-secondary-outline" onclick="FileExplorer.closeModal('modal-user-folder')">
                {{ __('common.cancel') ?? 'Cancel' }}
            </button>
            <button class="btn-toolkit btn-primary" onclick="FileExplorer.createUserFolder()" id="btn-create-user-folder" disabled>
                {{ __('common.create') ?? 'Create' }}
            </button>
        </div>
    </div>
</div>
@endif
