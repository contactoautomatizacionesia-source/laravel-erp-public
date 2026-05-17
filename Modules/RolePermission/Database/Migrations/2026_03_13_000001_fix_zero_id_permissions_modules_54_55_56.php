<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix: Los permisos de los módulos (CostCenter, Plans e InventoryCount)
 * quedaron con id=0 porque la columna `id` de `permissions` no tiene AUTO_INCREMENT
 * activo (fue importada desde un dump de phpMyAdmin con NO_AUTO_VALUE_ON_ZERO).
 * insertGetId() retorna 0 en ese caso, rompiendo la jerarquía parent_id.
 *
 * Solución: Obtener el module_id dinámicamente, limpiar y reinsertar con IDs explícitos.
 */
class FixZeroIdPermissionsModules545556 extends Migration
{
    /**
     * Obtiene los module_id dinámicamente basándose en el nombre de los permisos raíz.
     */
    private function getModuleIds()
    {
        $moduleNames = ['Cost Centers', 'Plans or Titles', 'Inventory Count'];
        $modulesData = DB::table('permissions')
            ->whereIn('name', $moduleNames)
            ->pluck('module_id', 'name')
            ->toArray();

        return [
            'cost_centers'    => $modulesData['Cost Centers'] ?? null,
            'plans'           => $modulesData['Plans or Titles'] ?? null,
            'inventory_count' => $modulesData['Inventory Count'] ?? null,
        ];
    }

    public function up()
    {
        // ================================================================
        // Paso 1: Consultar los IDs ANTES de borrar los registros
        // ================================================================
        $modules = $this->getModuleIds();

        // Filtramos solo los IDs que sí se encontraron
        $validModuleIds = array_filter(array_values($modules));

        if (empty($validModuleIds)) {
            // Si no se encuentra ninguno, detenemos la ejecución para evitar errores
            return;
        }

        // ================================================================
        // Paso 2: Limpiar registros corruptos con los IDs obtenidos
        // ================================================================
        DB::table('permissions')
            ->whereIn('module_id', $validModuleIds)
            ->delete();

        // ================================================================
        // Paso 3: Calcular el próximo ID disponible manualmente
        // ================================================================
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        // ================================================================
        // Paso 4: Re-insertar Cost Centers
        // ================================================================
        if (!empty($modules['cost_centers'])) {
            $ccRoot      = $nextId++;
            $ccDivisions = $nextId++;
            $ccInventory = $nextId++;

            DB::table('permissions')->insert([
                // Raíz
                ['id' => $ccRoot,      'module_id' => $modules['cost_centers'], 'parent_id' => null,        'name' => 'Cost Centers',   'translation' => 'cost_center.cost_centers', 'route' => 'cost_centers.index',   'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                // Sub-módulos
                ['id' => $ccDivisions, 'module_id' => $modules['cost_centers'], 'parent_id' => $ccRoot,     'name' => 'Divisions',      'translation' => 'cost_center.divisions',     'route' => 'cost_centers.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $ccInventory, 'module_id' => $modules['cost_centers'], 'parent_id' => $ccRoot,     'name' => 'Inventory',      'translation' => 'cost_center.inventory',     'route' => 'cost_centers.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                // Acciones CRUD de Divisiones
                ['id' => $nextId++,    'module_id' => $modules['cost_centers'], 'parent_id' => $ccDivisions, 'name' => 'Division List',  'translation' => 'cost_center.list',          'route' => 'cost_centers.index',   'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,    'module_id' => $modules['cost_centers'], 'parent_id' => $ccDivisions, 'name' => 'Division Create', 'translation' => 'cost_center.create',         'route' => 'cost_centers.store',   'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,    'module_id' => $modules['cost_centers'], 'parent_id' => $ccDivisions, 'name' => 'Division Edit',  'translation' => 'cost_center.edit',           'route' => 'cost_centers.update',  'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,    'module_id' => $modules['cost_centers'], 'parent_id' => $ccDivisions, 'name' => 'Division Delete', 'translation' => 'cost_center.delete',         'route' => 'cost_centers.destroy', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ================================================================
        // Paso 5: Re-insertar Plans
        // ================================================================
        if (!empty($modules['plans'])) {
            $plRoot = $nextId++;

            DB::table('permissions')->insert([
                // Raíz
                ['id' => $plRoot,   'module_id' => $modules['plans'], 'parent_id' => null,    'name' => 'Plans or Titles', 'translation' => 'permission.plans_or_titles', 'route' => 'plans_or_titles.index', 'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                // Sub-módulos
                ['id' => $nextId++, 'module_id' => $modules['plans'], 'parent_id' => $plRoot, 'name' => 'Plans',    'translation' => 'plans_or_titles.plans',    'route' => 'plans.index',    'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++, 'module_id' => $modules['plans'], 'parent_id' => $plRoot, 'name' => 'Rules',    'translation' => 'plans_or_titles.rules',    'route' => 'rules.index',    'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++, 'module_id' => $modules['plans'], 'parent_id' => $plRoot, 'name' => 'Benefits', 'translation' => 'plans_or_titles.benefits', 'route' => 'benefits.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ================================================================
        // Paso 6: Re-insertar Inventory Count
        // ================================================================
        if (!empty($modules['inventory_count'])) {
            $icRoot     = $nextId++;
            $icSettings = $nextId++;
            $icCounts   = $nextId++;
            $icAudits   = $nextId++;

            DB::table('permissions')->insert([
                // Raíz
                ['id' => $icRoot,     'module_id' => $modules['inventory_count'], 'parent_id' => null,        'name' => 'Inventory Count',     'translation' => 'inventorycount::menu.inventory_count', 'route' => 'inventory_count.index',          'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                // Sub-módulos
                ['id' => $icSettings, 'module_id' => $modules['inventory_count'], 'parent_id' => $icRoot,     'name' => 'Count Settings', 'translation' => 'inventorycount::menu.settings', 'route' => 'inventory_count.settings.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $icCounts,   'module_id' => $modules['inventory_count'], 'parent_id' => $icRoot,     'name' => 'Counts',         'translation' => 'inventorycount::menu.counts',   'route' => 'inventory_count.index',          'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $icAudits,   'module_id' => $modules['inventory_count'], 'parent_id' => $icRoot,     'name' => 'Count Audits',   'translation' => 'inventorycount::menu.audits',   'route' => 'inventory_count.audits.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                // Acciones
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icSettings, 'name' => 'Count Settings Save', 'translation' => 'inventorycount::menu.settings_save', 'route' => 'inventory_count.settings.store',  'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icCounts,   'name' => 'Count List',         'translation' => 'inventorycount::menu.count_list',     'route' => 'inventory_count.index',            'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icCounts,   'name' => 'Count Create',       'translation' => 'inventorycount::menu.count_create',   'route' => 'inventory_count.create',           'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icCounts,   'name' => 'Count Detail',       'translation' => 'inventorycount::menu.count_detail',   'route' => 'inventory_count.show',             'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icAudits,   'name' => 'Count Audit List',   'translation' => 'inventorycount::menu.audit_list',     'route' => 'inventory_count.audits.index',     'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $nextId++,   'module_id' => $modules['inventory_count'], 'parent_id' => $icAudits,   'name' => 'Count Audit Review', 'translation' => 'inventorycount::menu.audit_review',   'route' => 'inventory_count.audits.store',     'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        // En el rollback hacemos la misma consulta para limpiar correctamente
        $modules = $this->getModuleIds();
        $validModuleIds = array_filter(array_values($modules));

        if (!empty($validModuleIds)) {
            DB::table('permissions')
                ->whereIn('module_id', $validModuleIds)
                ->delete();
        }
    }
}
