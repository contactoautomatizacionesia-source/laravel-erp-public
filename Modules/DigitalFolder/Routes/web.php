<?php

use Illuminate\Support\Facades\Route;
use Modules\DigitalFolder\Http\Controllers\FileExplorerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin File Explorer Routes
Route::middleware(['auth', 'admin'])->prefix('admin/file-explorer')->as('admin.file-explorer.')->group(function () {
    Route::get('/', [FileExplorerController::class, 'index'])->name('index')->middleware('permission');
    Route::get('/contents', [FileExplorerController::class, 'getFolderContents'])->name('contents');
    Route::get('/folder/children', [FileExplorerController::class, 'getFolderChildren'])->name('folder.children');
    Route::post('/folder/create', [FileExplorerController::class, 'createFolder'])->name('folder.create');
    Route::post('/folder/user-create', [FileExplorerController::class, 'createUserFolder'])->name('folder.user-create');
    Route::post('/folder/rename', [FileExplorerController::class, 'renameFolder'])->name('folder.rename');
    Route::post('/folder/delete', [FileExplorerController::class, 'deleteFolder'])->name('folder.delete');
    Route::post('/files/upload', [FileExplorerController::class, 'uploadFiles'])->name('files.upload');
    Route::get('/files/download/{id}', [FileExplorerController::class, 'downloadFile'])->name('download');
    Route::post('/files/delete', [FileExplorerController::class, 'deleteFile'])->name('files.delete');
    Route::post('/files/rename', [FileExplorerController::class, 'renameFile'])->name('files.rename');
    Route::get('/files/preview/{id}', [FileExplorerController::class, 'previewFile'])->name('files.preview');
    Route::get('/files/serve/{id}', [FileExplorerController::class, 'serveFile'])->name('serve');
    Route::get('/search', [FileExplorerController::class, 'search'])->name('search');

    Route::get('/files/serve-path', [FileExplorerController::class, 'serveFileByPath'])->name('files.serve_path');
});

// Frontend File Explorer Routes (for customers)
Route::middleware(['auth', 'maintenance_mode'])->prefix('profile/file-explorer')->as('frontend.file-explorer.')->group(function () {
    Route::get('/', [FileExplorerController::class, 'frontendIndex'])->name('index');
    Route::get('/folder/{id}', [FileExplorerController::class, 'frontendIndex'])->name('folder');
    Route::get('/contents/{id}', [FileExplorerController::class, 'getFolderContents'])->name('contents');
    Route::get('/download/{id}', [FileExplorerController::class, 'downloadFile'])->name('download');
    Route::get('/preview/{id}', [FileExplorerController::class, 'previewFile'])->name('preview');
    Route::get('/serve/{id}', [FileExplorerController::class, 'serveFile'])->name('serve');
    Route::get('/files/serve-path', [FileExplorerController::class, 'serveFileByPath'])->name('files.serve_path');
});

// Legacy route for backward compatibility (redirects to new location)
Route::middleware(['auth', 'admin'])->get('/digitalfolder', function () {
    return redirect()->route('admin.file-explorer.index');
})->name('digital_folder.index');
