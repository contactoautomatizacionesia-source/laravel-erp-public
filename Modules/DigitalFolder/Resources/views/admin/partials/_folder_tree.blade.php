{{--
    Renderiza UN nivel de hijos de $folder (sin recursión).
    Los niveles más profundos se cargan bajo demanda vía AJAX (getFolderChildren).

    Vars esperadas:
        $folder          – Folder con children cargados con withCount('children')
        $activeIds       – array de IDs en el breadcrumb actual
        $depth           – nivel de indentación (entero, empieza en 1)
        $currentFolderId – ID de la carpeta actualmente visible
--}}
@foreach($folder->children as $child)
    @php
        $isActive    = $currentFolderId == $child->id;
        $isInPath    = in_array($child->id, $activeIds);
        $hasChildren = ($child->children_count ?? 0) > 0;
        $paddingLeft = ($depth * 16) + 20;
    @endphp

    <div class="fe-tree-node"
        data-folder-id="{{ $child->id }}"
        data-children-loaded="0">

        <div class="fe-tree-item {{ $isActive ? 'active' : '' }}"
            style="padding-left: {{ $paddingLeft }}px"
            onclick="FileExplorer.navigateTo({{ $child->id }})">

            @if($hasChildren)
                <span class="fe-tree-toggle {{ $isInPath || $isActive ? 'open' : '' }}"
                    onclick="event.stopPropagation(); FileExplorer.toggleTree(this)">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @else
                <span class="fe-tree-toggle-spacer"></span>
            @endif

            @if($child->type === 'user')
                <i class="fas fa-user-circle" style="color: #17a2b8;"></i>
            @elseif($isActive || $isInPath)
                <i class="fas fa-folder-open" style="color: #ffc107;"></i>
            @else
                <i class="fas fa-folder" style="color: #ffc107;"></i>
            @endif

            <span class="fe-tree-label">{{ $child->name }}</span>
        </div>

        @if($hasChildren)
            {{-- Lista vacía: se rellena vía AJAX al expandir el nodo --}}
            <div class="fe-tree-children-list {{ $isInPath || $isActive ? 'open' : '' }}"
                data-depth="{{ $depth + 1 }}">
            </div>
        @endif
    </div>
@endforeach
