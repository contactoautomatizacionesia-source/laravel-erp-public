/**
 * FileExplorer - Backend file explorer logic.
 *
 * Depends on globals injected by the blade template:
 *   - BREADCRUMB_IDS
 *   - FE_ROUTES
 *   - FE_CONFIG
 *   - FE_TRANS
 */

const FileExplorer = {
    currentFolderId: FE_CONFIG.currentFolderId,
    canWrite: FE_CONFIG.canWrite,
    isSuperAdmin: FE_CONFIG.isSuperAdmin,
    selectedItem: null,
    selectedType: null,
    filesToUpload: [],
    selectedUserId: null,
    currentPreviewFileId: null,
    currentView: 'grid',
    currentFolderData: null,
    currentSearchQuery: '',
    isLoadingContent: false,
    sidebarSyncDepth: 0,

    init() {
        this.bindEvents();
        this.hydrateInitialState();
        this.bootstrapSidebar();
    },

    async bootstrapSidebar() {
        await this.runSidebarSync(async () => {
            await this.autoExpandBreadcrumbPath();
            await this.syncSidebarFromIds(BREADCRUMB_IDS);
        });
    },

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.fe-context-menu')) {
                this.hideContextMenu();
            }
        });

        const searchInput = document.getElementById('fe-search-input');
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.search(e.target.value), 300);
            });
        }

        const dropzone = document.getElementById('upload-dropzone');
        if (dropzone) {
            dropzone.addEventListener('click', () => document.getElementById('file-input').click());
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                this.handleFileSelect(e.dataTransfer.files);
            });
        }

        const fileInput = document.getElementById('file-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e.target.files));
        }

        const userSearch = document.getElementById('user-search');
        if (userSearch) {
            userSearch.addEventListener('input', (e) => this.filterUsers(e.target.value));
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
                this.hideContextMenu();
            }
        });

        window.addEventListener('popstate', (event) => {
            const folderId = event.state?.folderId;
            if (folderId) {
                this.loadFolderContents(folderId, { pushState: false, preserveSearch: false });
            }
        });
    },

    hydrateInitialState() {
        const breadcrumbItems = Array.from(document.querySelectorAll('#fe-breadcrumb .fe-breadcrumb-item'));
        const activeBreadcrumb = breadcrumbItems.map((item, index) => ({
            id: this.extractFolderId(item.getAttribute('onclick')),
            name: item.textContent.trim(),
            is_active: index === breadcrumbItems.length - 1,
        })).filter(item => item.id);

        this.currentFolderData = {
            folder: {
                id: this.currentFolderId,
                name: activeBreadcrumb[activeBreadcrumb.length - 1]?.name || '',
            },
            breadcrumb: activeBreadcrumb,
            children: null,
            files: null,
        };

        this.updateBrowserState(false);
    },

    navigateTo(folderId) {
        return this.loadFolderContents(folderId);
    },

    refresh() {
        if (!this.currentFolderId) {
            return;
        }
        return this.loadFolderContents(this.currentFolderId, { pushState: false, preserveSearch: true });
    },

    async loadFolderContents(folderId, options = {}) {
        const { pushState = true, preserveSearch = false } = options;
        if (!folderId || this.isLoadingContent) {
            return;
        }

        this.isLoadingContent = true;
        this.hideContextMenu();
        this.setContentLoading(true);
        this.setSidebarSelectionLoading(folderId, true);

        try {
            const response = await fetch(`${FE_ROUTES.contents}?folder_id=${folderId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();
            if (!response.ok || data.error) {
                throw new Error(data.error || FE_TRANS.error);
            }

            this.currentFolderId = data.folder?.id || folderId;
            this.canWrite = data.can_write;
            this.isSuperAdmin = data.is_super_admin;
            this.currentFolderData = data;
            FE_CONFIG.currentFolderId = this.currentFolderId;
            FE_CONFIG.canWrite = this.canWrite;
            FE_CONFIG.isSuperAdmin = this.isSuperAdmin;

            this.renderCurrentFolder();
            await this.syncSidebarWithBreadcrumb(data.breadcrumb || []);
            this.updateBrowserState(pushState);

            if (!preserveSearch) {
                this.clearSearchInput();
            } else if (this.currentSearchQuery.length >= 2) {
                this.search(this.currentSearchQuery);
            }
        } catch (error) {
            console.error(error);
            toastr.error(error.message || FE_TRANS.error);
            this.renderCurrentFolder();
        } finally {
            this.setContentLoading(false);
            this.setSidebarSelectionLoading(folderId, false);
            this.isLoadingContent = false;
        }
    },

    updateBrowserState(pushState) {
        const state = { folderId: this.currentFolderId };
        const url = this.currentFolderId
            ? `${FE_ROUTES.index}?folder=${this.currentFolderId}`
            : FE_ROUTES.index;
        if (pushState) {
            window.history.pushState(state, '', url);
        } else {
            window.history.replaceState(state, '', url);
        }
    },

    renderLoadingState() {
        this.setContentLoading(true);
    },

    setContentLoading(isLoading) {
        const content = document.getElementById('fe-content');
        if (!content) {
            return;
        }

        content.classList.toggle('loading', isLoading);

        let overlay = content.querySelector(':scope > .fe-content-loader');
        if (isLoading) {
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'fe-content-loader';
                overlay.innerHTML = `
                    <div class="fe-loading">
                        <div class="fe-spinner"></div>
                        <span class="fe-loading-text">Cargando contenido...</span>
                    </div>
                `;
                content.appendChild(overlay);
            }
        } else if (overlay) {
            overlay.remove();
        }
    },

    async runSidebarSync(task) {
        this.sidebarSyncDepth += 1;
        this.setSidebarBusy(true);

        try {
            return await task();
        } finally {
            this.sidebarSyncDepth = Math.max(0, this.sidebarSyncDepth - 1);
            if (this.sidebarSyncDepth === 0) {
                this.setSidebarBusy(false);
            }
        }
    },

    setSidebarBusy(isBusy) {
        const sidebar = document.getElementById('fe-sidebar');
        if (!sidebar) {
            return;
        }

        sidebar.classList.toggle('syncing', isBusy);

        let overlay = sidebar.querySelector(':scope > .fe-sidebar-loader');
        if (isBusy) {
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'fe-sidebar-loader';
                overlay.innerHTML = `
                    <div class="fe-sidebar-loader-inner">
                        <span class="fe-loading-text">Cargando carpetas...</span>
                    </div>
                `;
                sidebar.appendChild(overlay);
            }
        } else if (overlay) {
            overlay.remove();
        }
    },

    renderCurrentFolder() {
        const content = document.getElementById('fe-content');
        if (!content) {
            return;
        }

        const data = this.currentFolderData || {};
        const children = Array.isArray(data.children) ? data.children : [];
        const files = Array.isArray(data.files) ? data.files : [];

        content.innerHTML = `
            <div class="fe-grid ${this.currentView === 'grid' ? 'active' : ''}" id="fe-grid">
                ${this.renderGridItems(children, files)}
            </div>
            <div class="fe-list ${this.currentView === 'list' ? 'active' : ''}" id="fe-list">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>${this.escapeHtml(FE_TRANS.name || 'Name')}</th>
                            <th>${this.escapeHtml(FE_TRANS.type || 'Type')}</th>
                            <th>${this.escapeHtml(FE_TRANS.size || 'Size')}</th>
                            <th>${this.escapeHtml(FE_TRANS.date || 'Date')}</th>
                        </tr>
                    </thead>
                    <tbody id="fe-list-body">
                        ${this.renderListItems(children, files)}
                    </tbody>
                </table>
            </div>
        `;

        this.renderBreadcrumb(data.breadcrumb || []);
        this.setView(this.currentView);
    },

    renderBreadcrumb(breadcrumb) {
        const container = document.getElementById('fe-breadcrumb');
        if (!container) {
            return;
        }

        container.innerHTML = breadcrumb.map((crumb, index) => `
            <button class="fe-breadcrumb-item ${index === breadcrumb.length - 1 ? 'active' : ''}"
                onclick="FileExplorer.navigateTo(${crumb.id})">
                ${index === 0 ? '<i class="fas fa-home"></i>' : ''}
                ${this.escapeHtml(crumb.name)}
            </button>
            ${index === breadcrumb.length - 1 ? '' : `
                <span class="fe-breadcrumb-separator">
                    <i class="fas fa-chevron-right"></i>
                </span>
            `}
        `).join('');
    },

    renderGridItems(children, files) {
        const folderItems = children.map((folder) => `
            <button class="fe-item rounded-md border" data-type="folder" data-id="${folder.id}"
                data-can-be-deleted="${folder.can_be_deleted ? '1' : '0'}"
                data-can-be-modified="${folder.can_be_modified ? '1' : '0'}"
                ondblclick="FileExplorer.navigateTo(${folder.id})"
                oncontextmenu="FileExplorer.showContextMenu(event, 'folder', ${folder.id})">
                <i class="fe-item-icon fas ${folder.type === 'user' ? 'fa-folder-open user-folder' : 'fa-folder folder'}"></i>
                <span class="fe-item-name">${this.escapeHtml(folder.name)}</span>
                ${folder.type === 'user' && folder.owner ? `<span class="fe-item-info">${this.escapeHtml(folder.owner.email || '')}</span>` : ''}
            </button>
        `).join('');

        const fileItems = files.map((file) => `
            <button class="fe-item" data-type="file" data-id="${file.id}"
                ondblclick="FileExplorer.previewFile(${file.id})"
                oncontextmenu="FileExplorer.showContextMenu(event, 'file', ${file.id})">
                <i class="fe-item-icon ${this.escapeHtml(file.icon_class || 'fas fa-file text-secondary')}"></i>
                <span class="fe-item-name">${this.escapeHtml(file.original_name)}</span>
                <span class="fe-item-info">${this.escapeHtml(file.formatted_size || '')}</span>
            </button>
        `).join('');

        if (!folderItems && !fileItems) {
            return `
                <div class="fe-empty" style="grid-column: 1 / -1;">
                    <i class="fas fa-folder-open"></i>
                    <p>${this.escapeHtml(FE_TRANS.folder_is_empty || 'This folder is empty')}</p>
                </div>
            `;
        }

        return folderItems + fileItems;
    },

    renderListItems(children, files) {
        const folderRows = children.map((folder) => `
            <tr data-type="folder" data-id="${folder.id}"
                data-can-be-deleted="${folder.can_be_deleted ? '1' : '0'}"
                data-can-be-modified="${folder.can_be_modified ? '1' : '0'}"
                ondblclick="FileExplorer.navigateTo(${folder.id})"
                oncontextmenu="FileExplorer.showContextMenu(event, 'folder', ${folder.id})">
                <td class="fe-list-icon">
                    <i class="fas fa-folder ${folder.type === 'user' ? 'text-info' : 'text-warning'}"></i>
                </td>
                <td>${this.escapeHtml(folder.name)}</td>
                <td>${this.escapeHtml(FE_TRANS.folder || 'Folder')}</td>
                <td>-</td>
                <td>${this.escapeHtml(this.formatDate(folder.created_at))}</td>
            </tr>
        `).join('');

        const fileRows = files.map((file) => `
            <tr data-type="file" data-id="${file.id}"
                ondblclick="FileExplorer.previewFile(${file.id})"
                oncontextmenu="FileExplorer.showContextMenu(event, 'file', ${file.id})">
                <td class="fe-list-icon">
                    <i class="${this.escapeHtml(file.icon_class || 'fas fa-file text-secondary')}"></i>
                </td>
                <td>${this.escapeHtml(file.original_name)}</td>
                <td>${this.escapeHtml((file.extension || '').toUpperCase())}</td>
                <td>${this.escapeHtml(file.formatted_size || '')}</td>
                <td>${this.escapeHtml(this.formatDate(file.created_at))}</td>
            </tr>
        `).join('');

        if (!folderRows && !fileRows) {
            return `
                <tr>
                    <td colspan="5">
                        <div class="fe-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>${this.escapeHtml(FE_TRANS.folder_is_empty || 'This folder is empty')}</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        return folderRows + fileRows;
    },

    setView(view) {
        this.currentView = view;
        const grid = document.getElementById('fe-grid');
        const list = document.getElementById('fe-list');

        document.querySelectorAll('.fe-view-toggle button').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === view) {
                btn.classList.add('active');
            }
        });

        if (!grid || !list) {
            return;
        }

        if (view === 'grid') {
            grid.classList.add('active');
            list.classList.remove('active');
        } else {
            grid.classList.remove('active');
            list.classList.add('active');
        }
    },

    showContextMenu(event, type, id) {
        event.preventDefault();
        this.selectedType = type;
        this.selectedItem = id;

        const menu = document.getElementById('fe-context-menu');
        const downloadBtn = document.getElementById('ctx-download');
        const renameBtn = document.getElementById('ctx-rename');
        const deleteBtn = document.getElementById('ctx-delete');
        const deleteDivider = document.getElementById('ctx-delete-divider');

        downloadBtn.style.display = type === 'file' ? 'flex' : 'none';

        const itemEl = document.querySelector(`[data-type="${type}"][data-id="${id}"]`);
        const canBeDeleted = type === 'file' || !itemEl || itemEl.dataset.canBeDeleted !== '0';
        const canBeModified = type === 'file' || !itemEl || itemEl.dataset.canBeModified !== '0';

        if (this.canWrite || this.isSuperAdmin) {
            renameBtn.style.display = canBeModified ? 'flex' : 'none';
            deleteBtn.style.display = canBeDeleted ? 'flex' : 'none';
            deleteDivider.style.display = canBeDeleted ? 'block' : 'none';
        } else {
            renameBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
            deleteDivider.style.display = 'none';
        }

        menu.style.left = `${event.pageX}px`;
        menu.style.top = `${event.pageY}px`;
        menu.classList.add('show');
    },

    hideContextMenu() {
        const menu = document.getElementById('fe-context-menu');
        if (menu) {
            menu.classList.remove('show');
        }
    },

    openItem() {
        this.hideContextMenu();
        if (this.selectedType === 'folder') {
            this.navigateTo(this.selectedItem);
        } else {
            this.previewFile(this.selectedItem);
        }
    },

    downloadItem() {
        this.hideContextMenu();
        if (this.selectedType === 'file') {
            window.location.href = `${FE_ROUTES.download}/${this.selectedItem}`;
        }
    },

    showModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    },

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    },

    closeAllModals() {
        document.querySelectorAll('.fe-modal-backdrop').forEach(modal => {
            modal.classList.remove('show');
        });
    },

    showNewFolderModal() {
        document.getElementById('new-folder-name').value = '';
        const btn = document.getElementById('btn-create-folder');
        btn.disabled = false;
        btn.innerHTML = FE_TRANS.create;
        this.showModal('modal-new-folder');
        setTimeout(() => document.getElementById('new-folder-name').focus(), 100);
    },

    createFolder() {
        const name = document.getElementById('new-folder-name').value.trim();
        if (!name) {
            toastr.error(FE_TRANS.please_enter_folder_name);
            return;
        }

        const btn = document.getElementById('btn-create-folder');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(FE_ROUTES.folderCreate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': FE_CSRF
            },
            body: JSON.stringify({
                name,
                parent_id: this.currentFolderId
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    this.closeModal('modal-new-folder');
                    this.refresh();
                } else {
                    toastr.error(data.error || FE_TRANS.error);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(FE_TRANS.error);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = FE_TRANS.create;
            });
    },

    showUploadModal() {
        this.filesToUpload = [];
        document.getElementById('upload-files-list').innerHTML = '';
        document.getElementById('file-input').value = '';
        this.showModal('modal-upload');
    },

    handleFileSelect(files) {
        this.filesToUpload = Array.from(files);
        this.renderUploadFilesList();
    },

    renderUploadFilesList() {
        const container = document.getElementById('upload-files-list');
        container.innerHTML = this.filesToUpload.map((file, index) => `
            <div class="fe-upload-file-item">
                <i class="fas fa-file"></i>
                <div class="fe-upload-file-info">
                    <div class="fe-upload-file-name">${this.escapeHtml(file.name)}</div>
                    <div class="fe-upload-file-size">${this.formatFileSize(file.size)}</div>
                </div>
                <button class="fe-upload-file-remove" onclick="FileExplorer.removeUploadFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    },

    removeUploadFile(index) {
        this.filesToUpload.splice(index, 1);
        this.renderUploadFilesList();
    },

    formatFileSize(bytes) {
        if (bytes >= 1073741824) return `${(bytes / 1073741824).toFixed(2)} GB`;
        if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(2)} MB`;
        if (bytes >= 1024) return `${(bytes / 1024).toFixed(2)} KB`;
        return `${bytes} bytes`;
    },

    doUpload() {
        if (this.filesToUpload.length === 0) {
            toastr.error(FE_TRANS.please_select_files);
            return;
        }

        const formData = new FormData();
        formData.append('folder_id', this.currentFolderId);
        this.filesToUpload.forEach(file => formData.append('files[]', file));

        const uploadBtn = document.getElementById('btn-upload');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${FE_TRANS.uploading}...`;

        fetch(FE_ROUTES.filesUpload, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': FE_CSRF
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    this.closeModal('modal-upload');
                    this.refresh();
                } else {
                    toastr.error(data.error || FE_TRANS.error);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(FE_TRANS.error);
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = FE_TRANS.upload;
            });
    },

    showRenameModal() {
        this.hideContextMenu();
        const input = document.getElementById('rename-input');

        const item = document.querySelector(`[data-type="${this.selectedType}"][data-id="${this.selectedItem}"]`);
        const name = item ? item.querySelector('.fe-item-name, td:nth-child(2)').textContent.trim() : '';

        input.value = name;
        this.showModal('modal-rename');
        setTimeout(() => input.focus(), 100);
    },

    renameItem() {
        const newName = document.getElementById('rename-input').value.trim();
        if (!newName) {
            toastr.error(FE_TRANS.please_enter_name);
            return;
        }

        const url = this.selectedType === 'folder'
            ? FE_ROUTES.folderRename
            : FE_ROUTES.filesRename;

        const data = this.selectedType === 'folder'
            ? { folder_id: this.selectedItem, name: newName }
            : { file_id: this.selectedItem, name: newName };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': FE_CSRF
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    this.closeModal('modal-rename');
                    this.refresh();
                } else {
                    toastr.error(data.error || FE_TRANS.error);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(FE_TRANS.error);
            });
    },

    deleteItem() {
        this.hideContextMenu();

        if (!confirm(FE_TRANS.are_you_sure)) {
            return;
        }

        const url = this.selectedType === 'folder'
            ? FE_ROUTES.folderDelete
            : FE_ROUTES.filesDelete;

        const data = this.selectedType === 'folder'
            ? { folder_id: this.selectedItem }
            : { file_id: this.selectedItem };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': FE_CSRF
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    this.refresh();
                } else {
                    toastr.error(data.error || FE_TRANS.error);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(FE_TRANS.error);
            });
    },

    previewFile(fileId) {
        this.currentPreviewFileId = fileId;
        document.getElementById('preview-container').innerHTML = `<p class="fe-loading-text">${FE_TRANS.loading}</p>`;

        const downloadBtn = document.getElementById('btn-preview-download');
        if (downloadBtn) downloadBtn.disabled = true;

        this.showModal('modal-preview');

        fetch(`${FE_ROUTES.filesPreview}/${fileId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('preview-container').innerHTML = `<p class="text-danger">${data.error}</p>`;
                    return;
                }

                document.getElementById('preview-title').textContent = data.file.original_name;

                let content = '';
                if (data.is_image) {
                    content = `<img src="${data.preview_url}" class="fe-preview-image" alt="${this.escapeHtml(data.file.original_name)}">`;
                } else if (data.content !== undefined) {
                    content = `<pre class="fe-preview-text">${this.escapeHtml(data.content)}</pre>`;
                } else {
                    content = `
                        <div class="fe-empty">
                            <i class="${this.escapeHtml(data.file.icon_class)}" style="font-size: 64px;"></i>
                            <p class="mt-3">${FE_TRANS.preview_not_available}</p>
                        </div>
                    `;
                }

                document.getElementById('preview-container').innerHTML = content;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('preview-container').innerHTML = `<p class="text-danger">${FE_TRANS.error_loading_preview}</p>`;
            })
            .finally(() => {
                if (downloadBtn) downloadBtn.disabled = false;
            });
    },

    downloadCurrentPreview() {
        if (!this.currentPreviewFileId) return;

        const btn = document.getElementById('btn-preview-download');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<div class="fe-btn-spinner"></div> ${FE_TRANS.download}`;
        }

        window.location.href = `${FE_ROUTES.download}/${this.currentPreviewFileId}`;

        setTimeout(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-download"></i> ${FE_TRANS.download}`;
            }
        }, 3000);
    },

    showCreateUserFolderModal() {
        this.selectedUserId = null;
        document.querySelectorAll('.fe-user-item').forEach(item => item.classList.remove('selected'));
        document.getElementById('btn-create-user-folder').disabled = true;
        document.getElementById('user-search').value = '';
        this.filterUsers('');
        this.showModal('modal-user-folder');
    },

    selectUser(userId, element) {
        this.selectedUserId = userId;
        document.querySelectorAll('.fe-user-item').forEach(item => item.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('btn-create-user-folder').disabled = false;
    },

    filterUsers(query) {
        const items = document.querySelectorAll('.fe-user-item');
        query = query.toLowerCase();

        items.forEach(item => {
            const name = item.querySelector('.fe-user-name').textContent.toLowerCase();
            const email = item.querySelector('.fe-user-email').textContent.toLowerCase();
            item.style.display = (name.includes(query) || email.includes(query)) ? 'flex' : 'none';
        });
    },

    createUserFolder() {
        if (!this.selectedUserId) {
            toastr.error(FE_TRANS.please_select_user);
            return;
        }

        const btn = document.getElementById('btn-create-user-folder');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(FE_ROUTES.folderUserCreate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': FE_CSRF
            },
            body: JSON.stringify({ user_id: this.selectedUserId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    this.closeModal('modal-user-folder');
                    this.refresh();
                } else {
                    toastr.error(data.error || FE_TRANS.error);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(FE_TRANS.error);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = FE_TRANS.create;
            });
    },

    async toggleTree(toggleEl) {
        if (this.sidebarSyncDepth > 0) {
            return;
        }

        const node = toggleEl.closest('.fe-tree-node');
        const childrenList = node?.querySelector(':scope > .fe-tree-children-list');
        if (!childrenList) {
            return;
        }

        if (node.dataset.childrenLoaded !== '1') {
            this.setTreeLoading(node, toggleEl, true);
            try {
                await this._loadTreeChildren(node, childrenList);
            } finally {
                this.setTreeLoading(node, toggleEl, false);
            }
        }

        toggleEl.classList.toggle('open');
        childrenList.classList.toggle('open');
        this.updateTreeNodeIcon(node, childrenList.classList.contains('open'));
    },

    async _loadTreeChildren(node, childrenList) {
        const folderId = node.dataset.folderId;
        const depth = parseInt(childrenList.dataset.depth, 10) || 1;

        try {
            const res = await fetch(`${FE_ROUTES.folderChildren}?folder_id=${folderId}`);
            const data = await res.json();
            if (data.error) {
                return;
            }

            childrenList.innerHTML = this._renderTreeNodes(data.children, depth);
            node.dataset.childrenLoaded = '1';
        } catch (e) {
            console.error('Error loading folder children', e);
        }
    },

    _renderTreeNodes(children, depth) {
        const paddingLeft = (depth * 16) + 20;

        return children.map(child => {
            const isActive = child.id === this.currentFolderId;

            const toggle = child.has_children
                ? `<span class="fe-tree-toggle"
                        onclick="event.stopPropagation(); FileExplorer.toggleTree(this)">
                    <i class="fas fa-chevron-right"></i>
                </span>`
                : '<span class="fe-tree-toggle-spacer"></span>';

            const icon = child.type === 'user'
                ? '<i class="fas fa-user-circle" style="color: #17a2b8;"></i>'
                : `<i class="fas ${isActive ? 'fa-folder-open' : 'fa-folder'}" style="color: #ffc107;"></i>`;

            const childrenSlot = child.has_children
                ? `<div class="fe-tree-children-list" data-depth="${depth + 1}"></div>`
                : '';

            return `<div class="fe-tree-node" data-folder-id="${child.id}" data-children-loaded="0">
                        <div class="fe-tree-item${isActive ? ' active' : ''}"
                            style="padding-left: ${paddingLeft}px"
                            onclick="FileExplorer.navigateTo(${child.id})">
                            ${toggle}${icon}
                            <span class="fe-tree-label">${this.escapeHtml(child.name)}</span>
                        </div>
                        ${childrenSlot}
                    </div>`;
        }).join('');
    },

    async autoExpandBreadcrumbPath() {
        let pending = document.querySelector(
            '.fe-tree-node[data-children-loaded="0"] > .fe-tree-children-list.open'
        );

        while (pending) {
            const node = pending.closest('.fe-tree-node');
            if (!node) {
                break;
            }

            await this._loadTreeChildren(node, pending);
            pending = document.querySelector(
                '.fe-tree-node[data-children-loaded="0"] > .fe-tree-children-list.open'
            );
        }
    },

    async syncSidebarWithBreadcrumb(breadcrumb) {
        const ids = breadcrumb.map(item => item.id);
        await this.runSidebarSync(async () => {
            await this.syncSidebarFromIds(ids);
        });
    },

    async syncSidebarFromIds(ids) {
        if (!ids.length) {
            return;
        }

        document.querySelectorAll('.fe-tree-item').forEach(item => item.classList.remove('active'));

        let parentNode = document.querySelector('.fe-sidebar .fe-tree-node[data-folder-id]');
        const rootChildren = parentNode?.querySelector(':scope > .fe-tree-children-list');
        if (parentNode && rootChildren && ids.includes(parseInt(parentNode.dataset.folderId, 10))) {
            rootChildren.classList.add('open');
        }

        for (let index = 0; index < ids.length; index++) {
            const folderId = ids[index];
            let node = document.querySelector(`.fe-tree-node[data-folder-id="${folderId}"]`);

            if (!node && parentNode) {
                const childrenList = parentNode.querySelector(':scope > .fe-tree-children-list');
                if (childrenList) {
                    if (parentNode.dataset.childrenLoaded !== '1') {
                        await this._loadTreeChildren(parentNode, childrenList);
                    }
                    node = childrenList.querySelector(`:scope > .fe-tree-node[data-folder-id="${folderId}"]`);
                }
            }

            if (!node) {
                continue;
            }

            const item = node.querySelector(':scope > .fe-tree-item');
            const childrenList = node.querySelector(':scope > .fe-tree-children-list');
            const toggle = node.querySelector(':scope > .fe-tree-item .fe-tree-toggle');
            const isActive = index === ids.length - 1;
            const shouldBeOpen = !isActive || ids.length === 1;

            if (item) {
                item.classList.toggle('active', isActive);
            }

            if (toggle && childrenList) {
                toggle.classList.toggle('open', shouldBeOpen);
                childrenList.classList.toggle('open', shouldBeOpen);
            }

            this.updateTreeNodeIcon(node, shouldBeOpen || isActive);

            if (childrenList && index < ids.length - 1 && node.dataset.childrenLoaded !== '1') {
                await this._loadTreeChildren(node, childrenList);
            }

            parentNode = node;
        }
    },

    updateTreeNodeIcon(node, isOpen) {
        if (!node) {
            return;
        }

        const icon = node.querySelector(':scope > .fe-tree-item > i.fas');
        if (!icon || icon.classList.contains('fa-user-circle') || icon.classList.contains('fa-server')) {
            return;
        }

        icon.classList.remove('fa-folder', 'fa-folder-open');
        icon.classList.add(isOpen ? 'fa-folder-open' : 'fa-folder');
    },

    setTreeLoading(node, toggleEl, isLoading) {
        if (node) {
            node.classList.toggle('loading', isLoading);
        }

        if (toggleEl) {
            toggleEl.classList.toggle('loading', isLoading);
            toggleEl.innerHTML = isLoading
                ? '<i class="fas fa-spinner fa-spin"></i>'
                : '<i class="fas fa-chevron-right"></i>';
        }
    },

    setSidebarSelectionLoading(folderId, isLoading) {
        document.querySelectorAll('.fe-tree-node.selection-loading').forEach(node => {
            if (parseInt(node.dataset.folderId, 10) !== folderId) {
                node.classList.remove('selection-loading');
            }
        });

        const node = document.querySelector(`.fe-tree-node[data-folder-id="${folderId}"]`);
        if (!node) {
            return;
        }

        node.classList.toggle('selection-loading', isLoading);
    },

    search(query) {
        const grid = document.getElementById('fe-grid');
        const list = document.getElementById('fe-list');
        if (!grid || !list) {
            return;
        }

        this.currentSearchQuery = query.trim();

        grid.querySelectorAll('.fe-no-results').forEach(el => el.remove());
        list.querySelectorAll('.fe-no-results').forEach(el => el.remove());

        if (this.currentSearchQuery.length < 2) {
            if (Array.isArray(this.currentFolderData?.children) && Array.isArray(this.currentFolderData?.files)) {
                this.renderCurrentFolder();
            }
            return;
        }

        const gridItems = grid.querySelectorAll('.fe-item');
        const listRows = list.querySelectorAll('tbody tr');
        const normalizedQuery = this.currentSearchQuery.toLowerCase();

        let visibleGrid = 0;
        gridItems.forEach(item => {
            const name = item.querySelector('.fe-item-name')?.textContent.toLowerCase() || '';
            const show = name.includes(normalizedQuery);
            item.style.display = show ? '' : 'none';
            if (show) {
                visibleGrid++;
            }
        });

        let visibleList = 0;
        listRows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const show = name.includes(normalizedQuery);
            row.style.display = show ? '' : 'none';
            if (show) {
                visibleList++;
            }
        });

        if (visibleGrid === 0 && this.currentView === 'grid') {
            grid.insertAdjacentHTML('beforeend', `
                <div class="fe-no-results" style="grid-column: 1 / -1;">
                    <div class="fe-no-results-inner">
                        <i class="fas fa-search"></i>
                        <p>${FE_TRANS.no_results_title.replace('%s', `<strong>${this.escapeHtml(normalizedQuery)}</strong>`)}</p>
                        <small>${FE_TRANS.no_results_hint}</small>
                    </div>
                </div>
            `);
        }

        if (visibleList === 0 && this.currentView === 'list') {
            const tbody = list.querySelector('tbody');
            const cols = list.querySelectorAll('thead th').length;
            tbody.insertAdjacentHTML('beforeend', `
                <tr class="fe-no-results">
                    <td colspan="${cols}">
                        <div class="fe-no-results-inner">
                            <i class="fas fa-search"></i>
                            <p>${FE_TRANS.no_results_title.replace('%s', `<strong>${this.escapeHtml(normalizedQuery)}</strong>`)}</p>
                            <small>${FE_TRANS.no_results_hint}</small>
                        </div>
                    </td>
                </tr>
            `);
        }
    },

    clearSearchInput() {
        this.currentSearchQuery = '';
        const input = document.getElementById('fe-search-input');
        if (input) {
            input.value = '';
        }
    },

    formatDate(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        const pad = (number) => String(number).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    },

    extractFolderId(onclickValue) {
        if (!onclickValue) {
            return null;
        }

        const match = onclickValue.match(/(\d+)/);
        return match ? parseInt(match[1], 10) : null;
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', () => FileExplorer.init());
