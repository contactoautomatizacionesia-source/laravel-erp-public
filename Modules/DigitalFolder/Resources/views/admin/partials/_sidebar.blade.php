@if($sidebarTree ?? null)
<div class="fe-sidebar" id="fe-sidebar">
    <div class="fe-sidebar-header">{{ __('common.folders') ?? 'Folders' }}</div>

    @php
        $breadcrumbIds    = $breadcrumb->pluck('id')->toArray();
        $currentFolderId  = $currentFolder->id ?? null;
        $rootIsActive     = $currentFolderId === $sidebarTree->id;
        $rootHasChildren  = $sidebarTree->children->isNotEmpty();
    @endphp

    {{-- Root node --}}
    <div class="fe-tree-node"
        data-folder-id="{{ $sidebarTree->id }}"
        data-children-loaded="{{ $rootHasChildren ? '1' : '0' }}">
        <div class="fe-tree-item {{ $rootIsActive ? 'active' : '' }}">
            @if($rootHasChildren)
                <button class="fe-tree-toggle open"
                    onclick="event.stopPropagation(); FileExplorer.toggleTree(this)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            @else
                <span class="fe-tree-toggle-spacer"></span>
            @endif

            <button class="fe-tree-link" onclick="FileExplorer.navigateTo({{ $sidebarTree->id }})">
                @if($isSuperAdmin)
                    <i class="fas fa-server" style="color: var(--fe-primary);"></i>
                    Master
                @else
                    <i class="fas fa-folder" style="color: #ffc107;"></i>
                    <span class="fe-tree-label">{{ __('common.my_files') ?? 'My Files' }}</span>
                @endif
            </button>
        </div>

        @if($rootHasChildren)
            <div class="fe-tree-children-list open">
                @include('digitalfolder::admin.partials._folder_tree', [
                    'folder'          => $sidebarTree,
                    'activeIds'       => $breadcrumbIds,
                    'depth'           => 1,
                    'currentFolderId' => $currentFolderId,
                ])
            </div>
        @endif
    </div>
</div>
@endif
