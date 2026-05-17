<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\DigitalFolder\Entities\Folder;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadir columnas de protección (default true para los que se creen en el futuro)
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('can_be_deleted')->default(true)->after('is_active');
            $table->boolean('can_be_modified')->default(true)->after('can_be_deleted');
        });

        // 2. Wipe completo de folder_files y folders (ignorando FK y soft deletes)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('folder_files')->truncate();
        DB::table('folders')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 3. Crear estructura digital: Master (raíz) → Empresarios (hija)
        $master = Folder::create([
            'name'            => 'Master',
            'type'            => 'master',
            'can_be_deleted'  => false,
            'can_be_modified' => false,
            'description'     => 'Carpeta raíz principal',
        ]);

        $empresarios = Folder::create([
            'name'            => 'Empresarios',
            'type'            => 'regular',
            'parent_id'       => $master->id,
            'can_be_deleted'  => false,
            'can_be_modified' => false,
            'description'     => 'Carpeta de empresarios',
        ]);

        // 4. Crear estructura física usando IDs (ruta inmutable, igual que getPhysicalPath())
        //    fileExplorer/{master.id}/{empresarios.id}/
        Storage::makeDirectory("fileExplorer/{$master->id}/{$empresarios->id}");
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['can_be_deleted', 'can_be_modified']);
        });
    }
};
