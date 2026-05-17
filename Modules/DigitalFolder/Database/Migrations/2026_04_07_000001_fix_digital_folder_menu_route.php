<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La migración add_digital_folder_menu insertó route='digital_folder.index'
        // pero el permiso real tiene route='admin.file-explorer.index'.
        // permissionCheck() compara la ruta del backendmenu contra la tabla permissions,
        // por lo que deben coincidir para que el ítem aparezca en el sidebar.
        DB::table('backendmenus')
            ->where('module', 'DigitalFolder')
            ->where('route', 'digital_folder.index')
            ->update(['route' => 'admin.file-explorer.index']);
    }

    public function down(): void
    {
        DB::table('backendmenus')
            ->where('module', 'DigitalFolder')
            ->where('route', 'admin.file-explorer.index')
            ->update(['route' => 'digital_folder.index']);
    }
};
