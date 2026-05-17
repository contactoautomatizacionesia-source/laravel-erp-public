<?php

namespace Modules\Plans\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SkipsExistingCatalogRows;

class BenefitsCatalogSeeder extends Seeder
{
    use SkipsExistingCatalogRows;

    public function run(): void
    {
        // =========================================================
        // SEED: Tipos de categoría de beneficio
        // =========================================================
        $this->insertMissingRows('benefit_category_type', [
            ['id' => 1, 'label' => json_encode(['es' => 'Económico', 'en' => 'Economic']),   'key' => 'ECONOMIC',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'label' => json_encode(['es' => 'Descuento', 'en' => 'Discount']),   'key' => 'DISCOUNT',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'label' => json_encode(['es' => 'Permiso',   'en' => 'Permission']), 'key' => 'PERMISSION', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'label' => json_encode(['es' => 'Referidos', 'en' => 'Referrals']),  'key' => 'REFERRALS',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'label' => json_encode(['es' => 'Premio',    'en' => 'Prize']),      'key' => 'PRIZE',      'created_at' => now(), 'updated_at' => now()],
        ], ['id', 'key']);

        // =========================================================
        // SEED: Categorías de beneficio
        // =========================================================
        $this->insertMissingRows('benefit_category', [
            [
                'id'                       => 1,
                'name'                     => json_encode(['es' => 'Descuento en Compras Posteriores',                     'en' => 'Discount on Subsequent Purchases']),
                'key'                      => 'DISCOUNT_ON_NEXT_PURCHASE',
                'description'              => json_encode(['es' => 'Obtiene un monto fijo o porcentaje de descuento fijo en las compras posteriores a la vinculación',                          'en' => 'Gets a fixed amount or fixed discount percentage on purchases after enrollment']),
                'benefit_category_type_id' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 2,
                'name'                     => json_encode(['es' => 'Diferencial Compras Referidos',                        'en' => 'Referral Purchase Differential']),
                'key'                      => 'REFERRED_PURCHASE_DIFFERENTIAL',
                'description'              => json_encode(['es' => 'Es el diferencial que se adquiere por todos los hijos directos',                                                             'en' => 'The differential earned from all direct referrals']),
                'benefit_category_type_id' => 4,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 3,
                'name'                     => json_encode(['es' => 'Acumula puntos para subir de nivel',                   'en' => 'Accumulate points to level up']),
                'key'                      => 'ACCUMULATE_POINTS_LEVEL_UP',
                'description'              => json_encode(['es' => 'Acumula puntos por las compras realizadas por los referidos',                                                                'en' => 'Accumulates points from purchases made by referrals']),
                'benefit_category_type_id' => 4,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 4,
                'name'                     => json_encode(['es' => 'Beneficio por Primera Compra de Referido',             'en' => 'First Referral Purchase Benefit']),
                'key'                      => 'FIRST_REFERRED_PURCHASE_BENEFIT',
                'description'              => json_encode(['es' => 'Beneficio fijo sobre el valor pagado por la primera compra de cada nuevo empresario que vincule directamente',               'en' => 'Fixed benefit on the amount paid for the first purchase of each directly enrolled entrepreneur']),
                'benefit_category_type_id' => 4,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 5,
                'name'                     => json_encode(['es' => 'Adquirir nuevo permiso',                               'en' => 'Acquire new permission']),
                'key'                      => 'NEW_PLATFORM_PERMISSION',
                'description'              => json_encode(['es' => 'Adquiere un nuevo permiso o acceso dentro de la plataforma',                                                                 'en' => 'Acquires a new permission or access within the platform']),
                'benefit_category_type_id' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 6,
                'name'                     => json_encode(['es' => 'Obtener una recompensa material, premiación o anuncio', 'en' => 'Obtain a material reward, recognition or announcement']),
                'key'                      => 'MATERIAL_REWARD_OR_RECOGNITION',
                'description'              => json_encode(['es' => 'Adquiere una recompensa material, premiación o anuncio otorgado por la organización.',                                       'en' => 'Receives a material reward, recognition or announcement granted by the organization.']),
                'benefit_category_type_id' => 5,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 7,
                'name'                     => json_encode(['es' => 'Bono monetario',                                       'en' => 'Monetary bonus']),
                'key'                      => 'MONETARY_BONUS',
                'description'              => json_encode(['es' => 'Adquiere un bono monetario como recompensa',                                                                                'en' => 'Receives a monetary bonus as a reward']),
                'benefit_category_type_id' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id'                       => 8,
                'name'                     => json_encode(['es' => 'Bono de ingresos residuales',                          'en' => 'Residual income bonus']),
                'key'                      => 'RESIDUAL_INCOME_BONUS',
                'description'              => json_encode(['es' => 'Adquiere una bonificación por ingresos residuales de sus referidos',                                                          'en' => 'Receives a bonus from residual income generated by referrals']),
                'benefit_category_type_id' => 4,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ], ['id', 'key']);

        // =========================================================
        // SEED: Secciones de formulario de beneficios
        // Las categorías 2 (REFERRED_PURCHASE_DIFFERENTIAL), 3 (ACCUMULATE_POINTS_LEVEL_UP),
        // 7 (MONETARY_BONUS) no tienen secciones (sin configuración adicional)
        // IDs 10-14 en form_sections (continuando después de las 9 secciones de rules)
        // =========================================================
        $this->insertMissingRows('form_sections', [
            ['id' => 10, 'owner_key' => 'DISCOUNT_ON_NEXT_PURCHASE',      'section_label' => json_encode(['es' => 'Registro de información', 'en' => 'Information Record']), 'section_key' => 'REGISTER_DATA', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // NOSONAR
            ['id' => 11, 'owner_key' => 'FIRST_REFERRED_PURCHASE_BENEFIT', 'section_label' => json_encode(['es' => 'Registro de información', 'en' => 'Information Record']), 'section_key' => 'REGISTER_DATA', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'owner_key' => 'NEW_PLATFORM_PERMISSION',         'section_label' => json_encode(['es' => 'Registro de información', 'en' => 'Information Record']), 'section_key' => 'REGISTER_DATA', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'owner_key' => 'MATERIAL_REWARD_OR_RECOGNITION',  'section_label' => json_encode(['es' => 'Registro de información', 'en' => 'Information Record']), 'section_key' => 'REGISTER_DATA', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'owner_key' => 'RESIDUAL_INCOME_BONUS',           'section_label' => json_encode(['es' => 'Registro de información', 'en' => 'Information Record']), 'section_key' => 'REGISTER_DATA', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['id']);

        // =========================================================
        // SEED: Campos de formulario de beneficios
        // IDs 23-30 en form_fields (continuando después de los 22 campos de rules)
        // Sección IDs remapeados: old 1→10, 2→11, 3→12, 4→13, 5→14
        // =========================================================
        $this->insertMissingRows('form_fields', [
            // Sección 10 (old 1) — DISCOUNT_ON_NEXT_PURCHASE
            ['id' => 23, 'form_section_id' => 10,
                'field_label'      => json_encode(['es' => 'Cantidad de descuento',        'en' => 'Discount amount']),
                'field_key'        => 'DISCOUNT_QUANTITY', 'field_type' => 'number', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Cantidad de descuento que obtendrá por las compras posteriores a adquirir este beneficio.', 'en' => 'Discount amount to be applied on purchases made after acquiring this benefit.']),
                'validation_rules' => '{"enabled":true,"maxLength":100}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // NOSONAR

            ['id' => 24, 'form_section_id' => 10,
                'field_label'      => json_encode(['es' => 'Seleccione Fijo o Porcentaje', 'en' => 'Select Fixed or Percentage']),
                'field_key'        => 'DISCOUNT_TYPE',     'field_type' => 'select', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Tipo de descuento, si es fijo será un descuento fijo por producto, si es porcentaje, será un porcentaje determinado del valor del producto.', 'en' => 'Discount type: Fixed applies a set amount per product; Percentage applies a set percentage of the product value.']),
                'validation_rules' => '{"required":true,"options":[1,2]}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Sección 11 (old 2) — FIRST_REFERRED_PURCHASE_BENEFIT
            ['id' => 25, 'form_section_id' => 11,
                'field_label'      => json_encode(['es' => 'Valor de beneficio',           'en' => 'Benefit value']),
                'field_key'        => 'BENEFIT_QUANTITY',  'field_type' => 'number', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Valor de beneficio pago sobre la primer compra del referido.',                          'en' => "Benefit value paid on the referral's first purchase."]),
                'validation_rules' => '{"enabled":true,"maxLength":100}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            ['id' => 26, 'form_section_id' => 11,
                'field_label'      => json_encode(['es' => 'Seleccione Fijo o Porcentaje', 'en' => 'Select Fixed or Percentage']),
                'field_key'        => 'BENEFIT_TYPE',      'field_type' => 'select', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Tipo de beneficio, si es fijo será un beneficio fijo por la primer compra, si es porcentaje será sobre el valor de la primer compra.', 'en' => 'Benefit type: Fixed applies a set amount on the first purchase; Percentage applies a set percentage of the first purchase value.']),
                'validation_rules' => '{"required":true,"options":[1,2]}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Sección 12 (old 3) — NEW_PLATFORM_PERMISSION
            ['id' => 27, 'form_section_id' => 12,
                'field_label'      => json_encode(['es' => 'Seleccione el permiso',        'en' => 'Select the permission']),
                'field_key'        => 'SELECT_PERMISSION', 'field_type' => 'select', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Seleccione el permiso que se adquiere por este beneficio.',                            'en' => 'Select the permission granted by this benefit.']),
                'validation_rules' => '{"required":true,"options":"METHOD[fetchPermissions]"}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Sección 13 (old 4) — MATERIAL_REWARD_OR_RECOGNITION
            ['id' => 28, 'form_section_id' => 13,
                'field_label'      => json_encode(['es' => 'Nombre del premio',            'en' => 'Prize name']),
                'field_key'        => 'PRIZE_NAME',        'field_type' => 'text', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Nombre del premio material, premiación o anuncio a obtener por este beneficio.',        'en' => 'Name of the material prize, recognition or announcement to be obtained through this benefit.']),
                'validation_rules' => '{"enabled":true,"maxLength":100}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            ['id' => 29, 'form_section_id' => 13,
                'field_label'      => json_encode(['es' => 'Descripción del premio',       'en' => 'Prize description']),
                'field_key'        => 'PRIZE_DESCRIPTION', 'field_type' => 'text', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Descripción precisa sobre qué se trata el beneficio en concreto.',                     'en' => 'Precise description of what the benefit specifically entails.']),
                'validation_rules' => '{"enabled":true,"maxLength":500}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Sección 14 (old 5) — RESIDUAL_INCOME_BONUS
            ['id' => 30, 'form_section_id' => 14,
                'field_label'      => json_encode(['es' => 'Cantidad del bono',            'en' => 'Bonus amount']),
                'field_key'        => 'MONETARY_BONUS_QUANTITY', 'field_type' => 'number', 'is_required' => true,
                'help_text'        => json_encode(['es' => 'Diligencie la cantidad del bono monetario que obtendrá como bonificación por este beneficio.', 'en' => 'Enter the monetary bonus amount to be received as a reward for this benefit.']),
                'validation_rules' => '{"enabled":true,"maxLength":100}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['id']);
    }
}
