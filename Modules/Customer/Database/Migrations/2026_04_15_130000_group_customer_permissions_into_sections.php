<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GroupCustomerPermissionsIntoSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $rootPermission = DB::table('permissions')
            ->where('route', 'cusotmer.list_active')
            ->orderBy('id')
            ->first();

        if (!$rootPermission) {
            return;
        }

        $customerModuleId = $rootPermission->module_id;
        $customerRootId = $rootPermission->id;

        DB::transaction(function () use ($customerModuleId, $customerRootId) {
            $allCustomersSectionId = $this->upsertSection(
                $customerModuleId,
                $customerRootId,
                'customer.section.all_customers',
                'All Customer',
                'common.all_customer'
            );

            $bulkUploadSectionId = $this->upsertSection(
                $customerModuleId,
                $customerRootId,
                'customer.section.bulk_upload',
                'Bulk Customer Upload',
                'common.bulk_customer_upload'
            );

            $this->movePermissionsToSection(
                $customerModuleId,
                $allCustomersSectionId,
                [
                    'customer.update_active_status',
                    'customer.show_details',
                    'customer.list_inactive',
                    'admin.customer.create',
                    'admin.customer.edit',
                    'admin.customer.destroy',
                    'customer.restore',
                ]
            );

            $this->movePermissionsToSection(
                $customerModuleId,
                $bulkUploadSectionId,
                [
                    'admin.customer.bulk_upload',
                ]
            );

            $networkPermission = DB::table('permissions')
                ->where('module_id', $customerModuleId)
                ->where('route', 'network.admin.global_tree')
                ->orderBy('id')
                ->first();

            if ($networkPermission) {
                $networkSectionId = $this->upsertSection(
                    $customerModuleId,
                    $customerRootId,
                    'customer.section.network',
                    'Network',
                    'tree.network'
                );

                $this->movePermissionsToSection(
                    $customerModuleId,
                    $networkSectionId,
                    ['network.admin.global_tree']
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $rootPermission = DB::table('permissions')
            ->where('route', 'cusotmer.list_active')
            ->orderBy('id')
            ->first();

        if (!$rootPermission) {
            return;
        }

        $customerModuleId = $rootPermission->module_id;
        $customerRootId = $rootPermission->id;

        DB::transaction(function () use ($customerModuleId, $customerRootId) {
            $sectionRoutes = [
                'customer.section.all_customers' => [
                    'customer.update_active_status',
                    'customer.show_details',
                    'customer.list_inactive',
                    'admin.customer.create',
                    'admin.customer.edit',
                    'admin.customer.destroy',
                    'customer.restore',
                ],
                'customer.section.bulk_upload' => [
                    'admin.customer.bulk_upload',
                ],
                'customer.section.network' => [
                    'network.admin.global_tree',
                ],
            ];

            foreach ($sectionRoutes as $sectionRoute => $childRoutes) {
                $section = DB::table('permissions')
                    ->where('module_id', $customerModuleId)
                    ->where('route', $sectionRoute)
                    ->orderBy('id')
                    ->first();

                foreach ($childRoutes as $childRoute) {
                    DB::table('permissions')
                        ->where('module_id', $customerModuleId)
                        ->where('route', $childRoute)
                        ->update([
                            'parent_id' => $customerRootId,
                            'type' => 2,
                            'updated_at' => now(),
                        ]);
                }

                if ($section && Schema::hasTable('role_permission')) {
                    DB::table('role_permission')
                        ->where('permission_id', $section->id)
                        ->delete();
                }

                if ($section) {
                    DB::table('permissions')
                        ->where('id', $section->id)
                        ->delete();
                }
            }
        });
    }

    /**
     * Create or update a section permission under the Customer module.
     */
    private function upsertSection($moduleId, $rootId, $route, $name, $translation)
    {
        $section = DB::table('permissions')
            ->where('module_id', $moduleId)
            ->where('route', $route)
            ->orderBy('id')
            ->first();

        $payload = [
            'module_id' => $moduleId,
            'parent_id' => $rootId,
            'name' => $name,
            'route' => $route,
            'type' => 2,
            'status' => 1,
            'updated_by' => 1,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('permissions', 'translation')) {
            $payload['translation'] = $translation;
        }

        if ($section) {
            DB::table('permissions')
                ->where('id', $section->id)
                ->update($payload);

            return $section->id;
        }

        $maxId = DB::table('permissions')->max('id');
        $nextId = ($maxId ? $maxId : 0) + 1;

        DB::table('permissions')->insert($payload + [
            'id' => $nextId,
            'created_by' => 1,
            'created_at' => now(),
        ]);

        return $nextId;
    }

    /**
     * Move existing Customer permissions under a section and mirror the parent in role_permission.
     */
    private function movePermissionsToSection($moduleId, $sectionId, array $routes)
    {
        $childPermissions = DB::table('permissions')
            ->where('module_id', $moduleId)
            ->whereIn('route', $routes)
            ->get();

        if ($childPermissions->isEmpty()) {
            return;
        }

        foreach ($childPermissions as $permission) {
            DB::table('permissions')
                ->where('id', $permission->id)
                ->where('route', $permission->route)
                ->update([
                    'parent_id' => $sectionId,
                    'type' => 3,
                    'updated_at' => now(),
                ]);
        }

        if (!Schema::hasTable('role_permission')) {
            return;
        }

        $childIds = $childPermissions->pluck('id')->unique()->values();

        $roleIds = DB::table('role_permission')
            ->whereIn('permission_id', $childIds)
            ->pluck('role_id')
            ->unique()
            ->values();

        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_permission')
                ->where('role_id', $roleId)
                ->where('permission_id', $sectionId)
                ->exists();

            if (!$exists) {
                DB::table('role_permission')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $sectionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
