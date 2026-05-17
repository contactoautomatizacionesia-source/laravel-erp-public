<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Registrar el módulo DigitalFolder si no existe
        if (Schema::hasTable('modules')) {
            $exists = DB::table('modules')->where('name', 'DigitalFolder')->exists();
            if (!$exists) {
                $maxOrder = DB::table('modules')->max('order') ?? 0;
                DB::table('modules')->insert([
                    'name'       => 'DigitalFolder',
                    'status'     => 1,
                    'order'      => $maxOrder + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Limpiar el permiso mal creado por la migración 100003 (si aún existe)
        DB::table('permissions')->where('route', 'admin.file-explorer.index')->delete();

        // 3. Crear el permiso principal para la vista File Explorer
        $alreadyExists = DB::table('permissions')
            ->where('route', 'admin.file-explorer.index')
            ->exists();

        if (!$alreadyExists) {
            $moduleId = DB::table('modules')->where('name', 'DigitalFolder')->value('id');
            $maxId    = DB::table('permissions')->max('id') ?? 0;

            DB::table('permissions')->insert([
                'id'          => $maxId + 1,
                'module_id'   => $moduleId,
                'parent_id'   => null,
                'name'        => 'File Explorer',
                'translation' => 'general_settings.digital_folder',
                'route'       => 'admin.file-explorer.index',
                'type'        => 1,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 4. Asignar el permiso a los roles admin/staff por defecto
        if (Schema::hasTable('role_permission')) {
            $permissionId = DB::table('permissions')
                ->where('route', 'admin.file-explorer.index')
                ->value('id');

            if ($permissionId) {
                // id=2 es el rol Admin del sistema (predeterminado); los demás se buscan por nombre
                $roleIds = DB::table('roles')
                    ->where('id', 2)
                    ->orWhere('name', 'Staff')
                    ->orWhere('name', 'Administrador')
                    ->pluck('id')
                    ->toArray();

                foreach ($roleIds as $roleId) {
                    $alreadyAssigned = DB::table('role_permission')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$alreadyAssigned) {
                        DB::table('role_permission')->insert([
                            'role_id'       => $roleId,
                            'permission_id' => $permissionId,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('route', 'admin.file-explorer.index')
            ->value('id');

        if ($permissionId) {
            DB::table('role_permission')
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table('permissions')->where('route', 'admin.file-explorer.index')->delete();

        DB::table('modules')->where('name', 'DigitalFolder')->delete();
    }
};
