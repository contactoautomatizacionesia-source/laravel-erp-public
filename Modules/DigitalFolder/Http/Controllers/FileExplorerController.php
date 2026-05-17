<?php

namespace Modules\DigitalFolder\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;
use Modules\DigitalFolder\Entities\Folder;
use Modules\DigitalFolder\Entities\FolderFile;
use Modules\UserActivityLog\Traits\LogActivity;

class FileExplorerController extends Controller
{
    public function __construct(private DigitalFolderRepository $repository)
    {
        $this->middleware(['auth', 'maintenance_mode']);
    }

    /**
     * Show the file explorer
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

        // Get folder ID from request or default to user's folder
        $folderId = $request->get('folder');

        if ($isSuperAdmin) {
            // Superadmin: show master folder by default
            $masterFolder = Folder::getOrCreateMasterFolder();

            if ($folderId) {
                $currentFolder = Folder::with(['children', 'files', 'parent'])->find($folderId);
                if (!$currentFolder) {
                    Toastr::error(__('common.folder_not_found'));
                    return redirect()->route('admin.file-explorer.index');
                }
            } else {
                $currentFolder = Folder::with(['children', 'files'])->find($masterFolder->id);
            }

            // Get user folders for sidebar (only for superadmin)
            $userFolders = Folder::where('type', 'user')
                ->select('id', 'name', 'owner_id')
                ->orderBy('name')
                ->get();
            $masterFolderId = $masterFolder->id;

            // Get IDs of users who already have a folder (to avoid N+1 in view)
            $usersWithFolderIds = $userFolders->pluck('owner_id')->toArray();

            // Get users without folders for creating user folders
            $users = User::whereHas('role', function ($q) {
                $q->where('type', '!=', 'superadmin');
            })->whereNotIn('id', $usersWithFolderIds)->get();
        } else {
            // Regular user: only show their folder
            $userFolder = Folder::where('owner_id', $user->id)
                ->where('type', 'user')
                ->first();

            if (!$userFolder) {
                $userFolder = $this->repository->ensureUserFolder($user);
            }

            if ($folderId) {
                $currentFolder = Folder::with(['children', 'files', 'parent'])->find($folderId);

                // Verify access: direct or inherited from ancestor
                if (!$currentFolder || !$this->folderIsAccessibleByUser($currentFolder, $user)) {
                    Toastr::error(__('common.access_denied'));
                    return redirect()->route('admin.file-explorer.index');
                }
            } else {
                $currentFolder = Folder::with(['children', 'files'])->find($userFolder->id);
            }

            $users = collect();
            $userFolders = collect();
            $masterFolderId = null;
        }

        // Load sidebar: solo un nivel con conteo de hijos (sin recursión).
        // Los niveles más profundos se cargan bajo demanda vía AJAX (getFolderChildren).
        $withChildren = ['children' => fn($q) => $q->withCount('children')];
        if ($isSuperAdmin) {
            $sidebarTree = Folder::with($withChildren)->find($masterFolder->id);
        } else {
            $sidebarTree = Folder::with($withChildren)->find($userFolder->id);
        }

        $fullBreadcrumb = $currentFolder ? $currentFolder->getBreadcrumb() : collect();

        // Non-superadmin: trim breadcrumb so it starts from their own root folder
        if ($isSuperAdmin) {
            $breadcrumb = $fullBreadcrumb;
        } else {
            $rootId = $userFolder->id;
            $rootIndex = $fullBreadcrumb->search(fn($crumb) => $crumb->id === $rootId);
            $breadcrumb = $rootIndex !== false
                ? $fullBreadcrumb->slice($rootIndex)->values()
                : $fullBreadcrumb;
        }

        $canWrite = $isSuperAdmin || ($currentFolder && $currentFolder->userHasAccess($user, 'write'));

        return view('digitalfolder::admin.file_explorer', compact(
            'currentFolder',
            'breadcrumb',
            'isSuperAdmin',
            'canWrite',
            'users',
            'userFolders',
            'masterFolderId',
            'sidebarTree'
        ));
    }

    /**
     * Get folder contents (AJAX)
     */
    public function getFolderContents(Request $request)
    {
        $user = Auth::user();
        $folderId = $request->get('folder_id');

        $folder = Folder::with(['children.owner', 'files.uploader', 'parent'])->find($folderId);

        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }

        $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

        if (!$isSuperAdmin && !$this->folderIsAccessibleByUser($folder, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $canWrite = $isSuperAdmin || $folder->userHasAccess($user, 'write');

        $fullBreadcrumb = $folder->getBreadcrumb();
        if (!$isSuperAdmin) {
            $userFolder = Folder::where('owner_id', $user->id)->where('type', 'user')->first();
            if ($userFolder) {
                $rootIndex = $fullBreadcrumb->search(fn($crumb) => $crumb->id === $userFolder->id);
                $fullBreadcrumb = $rootIndex !== false
                    ? $fullBreadcrumb->slice($rootIndex)->values()
                    : $fullBreadcrumb;
            }
        }

        return response()->json([
            'folder' => $folder,
            'children' => $folder->children,
            'files' => $folder->files,
            'breadcrumb' => $fullBreadcrumb,
            'can_write' => $canWrite,
            'is_super_admin' => $isSuperAdmin,
        ]);
    }

    /**
     * Get direct children of a folder for the sidebar lazy-load (AJAX).
     * Returns each child with a has_children flag — never recurses.
     */
    public function getFolderChildren(Request $request)
    {
        $user   = Auth::user();
        $folder = Folder::with(['children' => fn($q) => $q->withCount('children')])
                        ->find($request->get('folder_id'));

        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }

        $isSuperAdmin = $user->role && $user->role->type === 'superadmin';
        if (!$isSuperAdmin && !$this->folderIsAccessibleByUser($folder, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json([
            'children' => $folder->children->map(fn($child) => [
                'id'           => $child->id,
                'name'         => $child->name,
                'type'         => $child->type,
                'has_children' => $child->children_count > 0,
            ]),
        ]);
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|exists:folders,id',
        ]);

        try {
            $user = Auth::user();
            $parentFolder = Folder::find($request->parent_id);

            $parentFolder = Folder::find($request->parent_id);
            if (!$parentFolder) {
                return response()->json(['error' => 'Parent folder not found'], 404);
            }

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            if (!$isSuperAdmin && !$parentFolder->userHasAccess($user, 'write')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $folder = Folder::create([
                'name'        => $request->name,
                'parent_id'   => $request->parent_id,
                'type'        => 'regular',
                'description' => $request->description ?? null,
            ]);

            LogActivity::successLog('Folder created: ' . $folder->name);

            return response()->json([
                'success' => true,
                'folder'  => $folder,
                'message' => __('common.created_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => __('common.error_message') ?? 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Create user folder (superadmin only)
     */
    public function createUserFolder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = Auth::user();

            if (!$user->role || $user->role->type !== 'superadmin') {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $targetUser = User::find($request->user_id);
            $folder = $this->repository->ensureUserFolder($targetUser);

            LogActivity::successLog('User folder created for: ' . $targetUser->name);

            return response()->json([
                'success' => true,
                'folder' => $folder,
                'message' => __('common.created_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload files
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'files' => 'required|array|max:10', // max 10 files per upload
            'files.*' => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,mp4,mp3', // 50MB max per file
        ]);

        try {
            $user = Auth::user();
            $folder = Folder::find($request->folder_id);

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            if (!$isSuperAdmin && !$folder->userHasAccess($user, 'write')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $uploadedFiles = [];

            foreach ($request->file('files') as $file) {
                $uploadedFiles[] = $this->repository->uploadFile($file, $folder->id, $user->id);
            }

            LogActivity::successLog('Files uploaded to folder: ' . $folder->name);

            return response()->json([
                'success' => true,
                'files' => $uploadedFiles,
                'message' => __('common.uploaded_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download file
     */
    public function downloadFile($id)
    {
        try {
            $user = Auth::user();
            $file = FolderFile::with('folder.parent.parent.parent')->findOrFail($id);

            if (!$file->folder || !$this->folderIsAccessibleByUser($file->folder, $user)) {
                Toastr::error(__('common.access_denied'));
                return back();
            }

            if (!Storage::exists($file->path)) {
                Toastr::error(__('common.file_not_found'));
                return back();
            }

            LogActivity::successLog('File downloaded: ' . $file->original_name);

            return Storage::download($file->path, $file->original_name);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    /**
     * Delete file
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:folder_files,id',
        ]);

        try {
            $user = Auth::user();
            $file = FolderFile::with('folder')->findOrFail($request->file_id);

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';
            if (!$file->folder || (!$isSuperAdmin && !$file->folder->userHasAccess($user, 'write'))) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Delete from storage
            if (Storage::exists($file->path)) {
                Storage::delete($file->path);
            }

            $fileName = $file->original_name;
            $file->delete();

            LogActivity::successLog('File deleted: ' . $fileName);

            return response()->json([
                'success' => true,
                'message' => __('common.deleted_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete folder
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
        ]);

        try {
            $user = Auth::user();
            $folder = Folder::findOrFail($request->folder_id);

            // Cannot delete protected folders (master, user, or flagged can_be_deleted=false)
            if (!$folder->can_be_deleted || \in_array($folder->type, ['master', 'user'])) {
                return response()->json(['error' => 'Cannot delete this folder type'], 403);
            }

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            if (!$isSuperAdmin && !$folder->userHasAccess($user, 'admin')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Delete all files in folder
            foreach ($folder->files as $file) {
                if (Storage::exists($file->path)) {
                    Storage::delete($file->path);
                }
            }

            $folderName = $folder->name;
            $folder->delete();

            LogActivity::successLog('Folder deleted: ' . $folderName);

            return response()->json([
                'success' => true,
                'message' => __('common.deleted_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rename folder
     */
    public function renameFolder(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $folder = Folder::findOrFail($request->folder_id);

            if (!$folder->can_be_modified) {
                return response()->json(['error' => 'Cannot rename this folder'], 403);
            }

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            if (!$isSuperAdmin && !$folder->userHasAccess($user, 'write')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $folder->update(['name' => $request->name]);

            LogActivity::successLog('Folder renamed to: ' . $request->name);

            return response()->json([
                'success' => true,
                'folder' => $folder,
                'message' => __('common.updated_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rename file
     */
    public function renameFile(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:folder_files,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $file = FolderFile::with('folder')->findOrFail($request->file_id);

            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            if (!$file->folder || (!$isSuperAdmin && !$file->folder->userHasAccess($user, 'write'))) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $file->update(['original_name' => $request->name]);

            LogActivity::successLog('File renamed to: ' . $request->name);

            return response()->json([
                'success' => true,
                'file' => $file,
                'message' => __('common.updated_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get file preview
     */
    public function previewFile($id)
    {
        try {
            $user = Auth::user();
            $file = FolderFile::with('folder.parent.parent.parent')->findOrFail($id);

            if (!$file->folder || !$this->folderIsAccessibleByUser($file->folder, $user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            if (!Storage::exists($file->path)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $previewData = [
                'file' => $file,
                'is_image' => $file->isImage(),
                'is_previewable' => $file->isPreviewable(),
            ];

            if ($file->isImage()) {
                $previewData['preview_url'] = route('admin.file-explorer.serve', $id);
            } elseif (in_array(strtolower($file->extension), ['txt', 'csv', 'json', 'html', 'css', 'js'])) {
                $content = Storage::get($file->path);
                $previewData['content'] = mb_substr($content, 0, 50000); // Limit preview size
            }

            return response()->json($previewData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Serve file (for preview/display)
     */
    public function serveFile($id)
    {
        try {
            $user = Auth::user();
            $file = FolderFile::with('folder.parent.parent.parent')->findOrFail($id);

            if (!$file->folder || !$this->folderIsAccessibleByUser($file->folder, $user)) {
                abort(403);
            }

            if (!Storage::exists($file->path)) {
                abort(404);
            }

            return response()->file(Storage::path($file->path), [
                'Content-Type' => $file->mime_type,
            ]);
        } catch (Exception $e) {
            abort(500);
        }
    }

    public function serveFileByPath(Request $request)
    {
        $path = $request->input('path'); 

        if (!$path || !Storage::exists($path)) {
            abort(404);
        }

        return Storage::response($path);
    }

    /**
     * Search files and folders
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        try {
            $user = Auth::user();
            $query = $request->get('query');
            $isSuperAdmin = $user->role && $user->role->type === 'superadmin';

            $foldersQuery = Folder::where('name', 'like', "%{$query}%")->active();
            $filesQuery = FolderFile::where('original_name', 'like', "%{$query}%")->active();

            if (!$isSuperAdmin) {
                // Get folders the user has access to
                $accessibleFolderIds = $user->folders()->pluck('folders.id')->toArray();
                $ownedFolderIds = Folder::where('owner_id', $user->id)->pluck('id')->toArray();
                $allowedFolderIds = array_unique(array_merge($accessibleFolderIds, $ownedFolderIds));

                $foldersQuery->whereIn('id', $allowedFolderIds);
                $filesQuery->whereIn('folder_id', $allowedFolderIds);
            }

            $folders = $foldersQuery->limit(20)->get();
            $files = $filesQuery->with('folder')->limit(20)->get();

            return response()->json([
                'folders' => $folders,
                'files' => $files,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the file explorer for frontend customers
     */
    public function frontendIndex(Request $request, $folderId = null)
    {
        $user = Auth::user();

        // Get or create user's folder
        $userFolder = Folder::where('owner_id', $user->id)
            ->where('type', 'user')
            ->first();

        // If user doesn't have a folder yet, check if they have access to any folder
        if (!$userFolder) {
            // Check if user has access to any folder via folder_user pivot
            $accessibleFolder = Folder::whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

            if (!$accessibleFolder) {
                // User has no accessible folders - show empty state
                return view('digitalfolder::customer.file_explorer', [
                    'currentFolder' => null,
                    'breadcrumb' => collect(),
                    'canWrite' => false,
                    'noAccess' => true,
                ]);
            }

            $userFolder = $accessibleFolder;
        }

        // Determine current folder
        if ($folderId) {
            $currentFolder = Folder::with(['children', 'files', 'parent.parent.parent'])->find($folderId);

            // Verify access: direct permission OR the folder is a descendant of an accessible folder
            if (!$currentFolder || !$this->folderIsAccessibleByUser($currentFolder, $user)) {
                Toastr::error(__('common.access_denied'));
                return redirect()->route('frontend.file-explorer.index');
            }
        } else {
            $currentFolder = Folder::with(['children', 'files'])->find($userFolder->id);
        }

        $breadcrumb = $currentFolder ? $currentFolder->getBreadcrumb() : collect();
        $canWrite = $currentFolder && $currentFolder->userHasAccess($user, 'write');

        return view('digitalfolder::customer.file_explorer', compact(
            'currentFolder',
            'breadcrumb',
            'canWrite'
        ));
    }

    /**
     * Check if a folder is accessible by a user, considering ancestor inheritance.
     * A subfolder created inside the user's folder inherits access even without
     * an explicit permission record.
     */
    private function folderIsAccessibleByUser(Folder $folder, $user): bool
    {
        // Direct access: user owns it or has explicit permission
        if ($folder->userHasAccess($user)) {
            return true;
        }

        // Inherited access: traverse up the parent chain
        $ancestor = $folder->parent;
        while ($ancestor) {
            if ($ancestor->userHasAccess($user)) {
                return true;
            }
            $ancestor = $ancestor->parent;
        }

        return false;
    }
}
