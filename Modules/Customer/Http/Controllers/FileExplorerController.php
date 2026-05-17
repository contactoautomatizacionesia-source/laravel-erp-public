<?php

namespace Modules\Customer\Http\Controllers;

use App\Models\Folder;
use App\Models\FolderFile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;
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

                // Verify access
                if (!$currentFolder || !$currentFolder->userHasAccess($user)) {
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

        $breadcrumb = $currentFolder ? $currentFolder->getBreadcrumb() : collect();
        $canWrite = $isSuperAdmin || ($currentFolder && $currentFolder->userHasAccess($user, 'write'));

        $sidebarTree = null; // No sidebar in customer view

        return view('customer::customers.file_explorer', compact(
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

        if (!$folder->userHasAccess($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $isSuperAdmin = $user->role && $user->role->type === 'superadmin';
        $canWrite = $isSuperAdmin || $folder->userHasAccess($user, 'write');

        return response()->json([
            'folder' => $folder,
            'children' => $folder->children,
            'files' => $folder->files,
            'breadcrumb' => $folder->getBreadcrumb(),
            'can_write' => $canWrite,
            'is_super_admin' => $isSuperAdmin,
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
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'owner_id' => $user->id,
                'type' => 'regular',
                'description' => $request->description ?? null,
            ]);

            LogActivity::successLog('Folder created: ' . $folder->name);

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
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();

                // Generate unique filename
                $filename = Str::random(32) . '.' . $extension;
                $path = 'file_explorer/' . date('Y/m');

                // Store file
                $storedPath = $file->storeAs($path, $filename);

                // Create database record
                $folderFile = FolderFile::create([
                    'folder_id' => $folder->id,
                    'uploaded_by' => $user->id,
                    'name' => $filename,
                    'original_name' => $originalName,
                    'path' => $storedPath,
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'size' => $size,
                ]);

                $uploadedFiles[] = $folderFile;
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
            $file = FolderFile::with('folder')->findOrFail($id);

            if (!$file->folder || !$file->folder->userHasAccess($user)) {
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

            // Cannot delete master or user folders
            if (in_array($folder->type, ['master', 'user'])) {
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
            $file = FolderFile::with('folder')->findOrFail($id);

            if (!$file->folder || !$file->folder->userHasAccess($user)) {
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
            $file = FolderFile::with('folder')->findOrFail($id);

            if (!$file->folder || !$file->folder->userHasAccess($user)) {
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
                return view('frontend.amazy.pages.profile.file_explorer', [
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
            $currentFolder = Folder::with(['children', 'files', 'parent'])->find($folderId);

            // Verify access
            if (!$currentFolder || !$currentFolder->userHasAccess($user)) {
                Toastr::error(__('common.access_denied'));
                return redirect()->route('frontend.file-explorer.index');
            }
        } else {
            $currentFolder = Folder::with(['children', 'files'])->find($userFolder->id);
        }

        $breadcrumb = $currentFolder ? $currentFolder->getBreadcrumb() : collect();
        $canWrite = $currentFolder && $currentFolder->userHasAccess($user, 'write');

        return view('frontend.amazy.pages.profile.file_explorer', compact(
            'currentFolder',
            'breadcrumb',
            'canWrite'
        ));
    }
}
