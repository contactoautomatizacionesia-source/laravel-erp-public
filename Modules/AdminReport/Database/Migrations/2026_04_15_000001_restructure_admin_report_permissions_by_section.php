<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reestructura los permisos del módulo AdminReport para agruparlos por
 * sección (vista) en la UI de asignación de permisos.
 *
 * Antes: todos los reportes eran tipo 2 (sub-módulo simple), apareciendo
 *        planos en "Permisos Generales".
 *
 * Después: se crean 6 secciones tipo 2 (Users, Sales, Products, Finance,
 *          Rankings, Reviews) y cada reporte pasa a ser tipo 3 (acción
 *          hija de su sección). Esto hace que aparezcan en "Permisos
 *          Detallados" con pestañas por sección.
 *
 * Además:
 *  - Se agregan los permisos faltantes: report.top_seller y
 *    report.seller_wise_sales (sólo activos con módulo MultiVendor).
 *  - Se corrige la translation del permiso Company Reviews, que quedó como
 *    'permission.company_reviews' (clave inexistente) tras la migración
 *    de traducciones automáticas. Se actualiza a 'review.company_review'.
 *
 * PORTABILIDAD: todos los lookups se hacen por `route`, nunca por `id`,
 * para que la migración funcione en cualquier entorno independientemente
 * de los IDs asignados.
 */
class RestructureAdminReportPermissionsBySection extends Migration
{
    /**
     * Routes de los reportes existentes que pasarán de tipo 2 a tipo 3.
     * Mapeados por route → sección a la que pertenecen (para el up/down).
     */
    private array $existingReportRoutes = [
        'report.user_searches',
        'report.visitor_report',
        'report.inhouse_product_sale',
        'report.product_stock',
        'report.wishlist',
        'report.wallet_recharge_history',
        'report.top_customer',
        'report.top_selling_item',
        'report.order',
        'report.payment',
        'report.product_review',
        'report.seller_review',
    ];

    /**
     * Routes de los permisos nuevos insertados por esta migración.
     * Necesarios para limpiarlos en down().
     */
    private array $newPermissionRoutes = [
        'report.section_users',
        'report.section_sales',
        'report.section_products',
        'report.section_finance',
        'report.section_rankings',
        'report.section_reviews',
        'report.top_seller',
        'report.seller_wise_sales',
    ];

    public function up(): void
    {
        // ----------------------------------------------------------------
        // 1. Resolver el ID del permiso raíz (Admin Report, tipo 1)
        //    por su route, sin asumir ningún ID.
        // ----------------------------------------------------------------
        $rootId = DB::table('permissions')
            ->where('route', 'admin_report')
            ->where('type', 1)
            ->value('id');

        if (! $rootId) {
            // El módulo Admin Report no está instalado en este entorno; no hay nada que hacer.
            return;
        }

        // ----------------------------------------------------------------
        // 2. Resolver module_id leyéndolo del propio permiso raíz.
        // ----------------------------------------------------------------
        $moduleId = DB::table('permissions')
            ->where('id', $rootId)
            ->value('module_id');

        // ----------------------------------------------------------------
        // 3. Idempotencia: si las secciones ya existen (migración ya corrida),
        //    no volver a insertarlas.
        // ----------------------------------------------------------------
        $alreadyMigrated = DB::table('permissions')
            ->where('route', 'report.section_users')
            ->exists();

        if ($alreadyMigrated) {
            return;
        }

        // ----------------------------------------------------------------
        // 4. Corregir la translation del permiso Company Reviews.
        //    La migración de traducciones automáticas la dejó como
        //    'permission.company_reviews', clave que no existe en los lang.
        // ----------------------------------------------------------------
        DB::table('permissions')
            ->where('route', 'report.seller_review')
            ->update([
                'name'        => 'Company Reviews',
                'translation' => 'review.company_review',
                'updated_at'  => now(),
            ]);

        // ----------------------------------------------------------------
        // 5. Calcular el próximo ID disponible.
        // ----------------------------------------------------------------
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        // ----------------------------------------------------------------
        // 6. Definición de secciones y sus hijos.
        //
        //    Cada sección lleva:
        //      - 'section'  : datos del nuevo permiso tipo 2 (agrupador)
        //      - 'children' : lista de routes de hijos existentes que
        //                     se reasignan a tipo 3, más los nuevos a insertar.
        // ----------------------------------------------------------------
        $sections = [
            // ── Usuarios ──────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Users',
                    'translation' => 'report.section_users',
                    'route'       => 'report.section_users',
                ],
                'existing' => ['report.user_searches', 'report.visitor_report'],
                'new'      => [],
            ],
            // ── Ventas ────────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Sales',
                    'translation' => 'report.section_sales',
                    'route'       => 'report.section_sales',
                ],
                'existing' => ['report.inhouse_product_sale'],
                'new'      => [
                    [
                        'name'        => 'Seller Wise Sale',
                        'translation' => 'report.seller_wise_sales',
                        'route'       => 'report.seller_wise_sales',
                        'module'      => 'MultiVendor',
                    ],
                ],
            ],
            // ── Productos ─────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Products',
                    'translation' => 'report.section_products',
                    'route'       => 'report.section_products',
                ],
                'existing' => ['report.product_stock', 'report.wishlist', 'report.top_selling_item'],
                'new'      => [],
            ],
            // ── Finanzas ──────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Finance',
                    'translation' => 'report.section_finance',
                    'route'       => 'report.section_finance',
                ],
                'existing' => ['report.wallet_recharge_history', 'report.order', 'report.payment'],
                'new'      => [],
            ],
            // ── Rankings ──────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Rankings',
                    'translation' => 'report.section_rankings',
                    'route'       => 'report.section_rankings',
                ],
                'existing' => ['report.top_customer'],
                'new'      => [
                    [
                        'name'        => 'Top Sellers',
                        'translation' => 'report.top_sellers',
                        'route'       => 'report.top_seller',
                        'module'      => 'MultiVendor',
                    ],
                ],
            ],
            // ── Reseñas ───────────────────────────────────────────────
            [
                'section'  => [
                    'name'        => 'Reviews',
                    'translation' => 'report.section_reviews',
                    'route'       => 'report.section_reviews',
                ],
                'existing' => ['report.product_review', 'report.seller_review'],
                'new'      => [],
            ],
        ];

        // ----------------------------------------------------------------
        // 7. Procesar cada sección.
        // ----------------------------------------------------------------
        foreach ($sections as $sectionDef) {
            $sectionId = $nextId++;

            // 7a. Insertar la sección como permiso tipo 2.
            DB::table('permissions')->insert([
                'id'          => $sectionId,
                'module_id'   => $moduleId,
                'parent_id'   => $rootId,
                'name'        => $sectionDef['section']['name'],
                'translation' => $sectionDef['section']['translation'],
                'route'       => $sectionDef['section']['route'],
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 7b. Reasignar hijos existentes: parent_id → sección, tipo → 3.
            if (! empty($sectionDef['existing'])) {
                DB::table('permissions')
                    ->whereIn('route', $sectionDef['existing'])
                    ->update([
                        'parent_id'  => $sectionId,
                        'type'       => 3,
                        'updated_at' => now(),
                    ]);
            }

            // 7c. Insertar hijos nuevos como tipo 3.
            foreach ($sectionDef['new'] as $newChild) {
                DB::table('permissions')->insert([
                    'id'          => $nextId++,
                    'module_id'   => $moduleId,
                    'parent_id'   => $sectionId,
                    'name'        => $newChild['name'],
                    'translation' => $newChild['translation'],
                    'route'       => $newChild['route'],
                    'module'      => $newChild['module'] ?? null,
                    'type'        => 3,
                    'status'      => 1,
                    'created_by'  => 1,
                    'updated_by'  => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // ----------------------------------------------------------------
        // 1. Resolver el ID raíz por route para restaurar parent_id.
        // ----------------------------------------------------------------
        $rootId = DB::table('permissions')
            ->where('route', 'admin_report')
            ->where('type', 1)
            ->value('id');

        if (! $rootId) {
            return;
        }

        // ----------------------------------------------------------------
        // 2. Restaurar los permisos existentes: tipo 2, parent = raíz.
        // ----------------------------------------------------------------
        DB::table('permissions')
            ->whereIn('route', $this->existingReportRoutes)
            ->update([
                'parent_id'  => $rootId,
                'type'       => 2,
                'updated_at' => now(),
            ]);

        // ----------------------------------------------------------------
        // 3. Restaurar translation original de Company Reviews.
        // ----------------------------------------------------------------
        DB::table('permissions')
            ->where('route', 'report.seller_review')
            ->update([
                'translation' => 'permission.company_reviews',
                'updated_at'  => now(),
            ]);

        // ----------------------------------------------------------------
        // 4. Eliminar secciones nuevas y permisos nuevos creados por esta
        //    migración (identificados por sus routes únicos).
        // ----------------------------------------------------------------
        DB::table('permissions')
            ->whereIn('route', $this->newPermissionRoutes)
            ->delete();
    }
}
