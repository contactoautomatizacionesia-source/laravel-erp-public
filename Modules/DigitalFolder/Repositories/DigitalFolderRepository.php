<?php

namespace Modules\DigitalFolder\Repositories;

use App\Enums\FolderType;
use Illuminate\Support\Str;
use Modules\DigitalFolder\Entities\Folder;
use Modules\DigitalFolder\Entities\FolderFile;

class DigitalFolderRepository
{
    /**
     * Asegura que el usuario tenga su carpeta raíz creada dentro de la jerarquía estándar:
     * Master → Empresarios → {nombre del usuario}
     */
    public function ensureUserFolder($user): Folder
    {
        $master      = Folder::getOrCreateMasterFolder();
        $empresarios = $this->ensureStandardSubfolder($master, FolderType::Customers);

        $existing = Folder::where('owner_id', $user->id)
            ->where('type', 'user')
            ->first();

        if ($existing) {
            return $existing;
        }

        $folder = Folder::create([
            'name'        => $user->name,
            'slug'        => 'user-' . $user->id . '-' . Str::random(6),
            'parent_id'   => $empresarios->id,
            'owner_id'    => $user->id,
            'type'        => 'user',
            'description' => 'Personal folder for ' . $user->name,
        ]);

        $folder->users()->attach($user->id, ['permission' => 'read']);

        return $folder;
    }

    /**
     * Asegura que el usuario de personal tenga su carpeta raíz dentro de la jerarquía:
     * Master → Personal → {nombre del usuario}
     */
    public function ensureStaffUserFolder($user): Folder
    {
        $master   = Folder::getOrCreateMasterFolder();
        $personal = $this->ensureStandardSubfolder($master, FolderType::Staff);

        $existing = Folder::where('owner_id', $user->id)
            ->where('type', 'user')
            ->first();

        if ($existing) {
            return $existing;
        }

        $folder = Folder::create([
            'name'        => $user->name,
            'slug'        => 'user-' . $user->id . '-' . \Illuminate\Support\Str::random(6),
            'parent_id'   => $personal->id,
            'owner_id'    => $user->id,
            'type'        => 'user',
            'description' => 'Personal folder for staff ' . $user->name,
        ]);

        $folder->users()->attach($user->id, ['permission' => 'read']);

        return $folder;
    }

    /**
     * Finds or creates a year subfolder (e.g. "2026") under the given parent.
     */
    public function ensureYearFolder(Folder $parent, string $year = null): Folder
    {
        $year = $year ?? (string) now()->year;

        return Folder::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $year],
            ['type' => 'regular']
        );
    }

    /**
     * Finds or creates a standard subfolder identified by a FolderType case under the given parent.
     * These folders are protected: cannot be deleted or renamed.
     *
     * Example: ensureStandardSubfolder($yearFolder, FolderType::Register) → "Registro"
     *          ensureStandardSubfolder($yearFolder, FolderType::Contracts)  → "Contratos"
     */
    public function ensureStandardSubfolder(Folder $parent, FolderType $type): Folder
    {
        return Folder::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $type->value],
            [
                'type'            => 'regular',
                'can_be_deleted'  => false,
                'can_be_modified' => false,
            ]
        );
    }

    /**
     * Sube un archivo físico y crea el registro en la BD (folder_files).
     * La ruta física espeja la jerarquía digital (fileExplorer/master/empresarios/...).
     * Retorna el objeto FolderFile creado.
     */
    public function uploadFile($file, $folderId, $userId): FolderFile
    {
        $originalName = $file->getClientOriginalName();
        $extension    = $file->getClientOriginalExtension();
        $mimeType     = $file->getMimeType();
        $size         = $file->getSize();

        $filename = Str::random(32) . '.' . $extension;

        $folder     = Folder::find($folderId);
        $path       = $folder ? $folder->getPhysicalPath() : 'fileExplorer';
        $storedPath = $file->storeAs($path, $filename);

        return FolderFile::create([
            'folder_id'     => $folderId,
            'uploaded_by'   => $userId,
            'name'          => $filename,
            'original_name' => $originalName,
            'path'          => $storedPath,
            'mime_type'     => $mimeType,
            'extension'     => $extension,
            'size'          => $size,
        ]);
    }
}
