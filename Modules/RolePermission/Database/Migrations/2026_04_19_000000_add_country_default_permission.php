<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $alreadyExists = DB::table('permissions')
            ->where('route', 'setup.country.toggle-default')
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $countryParent = DB::table('permissions')
            ->where('route', 'setup.country.index')
            ->first();

        if (!$countryParent) {
            return;
        }

        $permissionId = (DB::table('permissions')->max('id') ?? 0) + 1;

        DB::table('permissions')->insert([
            'id' => $permissionId,
            'module_id' => $countryParent->module_id,
            'parent_id' => $countryParent->id,
            'name' => 'Default Country',
            'route' => 'setup.country.default',
            'type' => 3,
            'status' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('role_permission')) {
            $superadminRoleId = DB::table('roles')
                ->where('type', 'superadmin')
                ->value('id');

            if ($superadminRoleId) {
                $alreadyAssigned = DB::table('role_permission')
                    ->where('role_id', $superadminRoleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('role_permission')->insert([
                        'role_id' => $superadminRoleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('route', 'setup.country.default')
            ->value('id');

        if ($permissionId) {
            DB::table('role_permission')
                ->where('permission_id', $permissionId)
                ->delete();

            DB::table('permissions')
                ->where('id', $permissionId)
                ->delete();
        }
    }
};
