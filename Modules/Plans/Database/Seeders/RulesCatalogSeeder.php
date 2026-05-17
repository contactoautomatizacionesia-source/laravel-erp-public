<?php

namespace Modules\Plans\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SkipsExistingCatalogRows;

class RulesCatalogSeeder extends Seeder
{
    use SkipsExistingCatalogRows;

    public function run(): void // NOSONAR
    {
        $this->insertMissingRows('rule_category_type', [
            ['id' => 1, 'label' => json_encode(['es' => 'Puntos', 'en' => 'Points']), 'key' => 'POINTS', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'label' => json_encode(['es' => 'Cumplimiento', 'en' => 'Compliance']), 'key' => 'COMPLIANCE', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'label' => json_encode(['es' => 'Red', 'en' => 'Network']), 'key' => 'NETWORK', 'created_at' => now(), 'updated_at' => now()],
        ], ['id', 'key']);

        $this->insertMissingRows('rule_category', [
            [
                'id' => 1,
                'name' => json_encode(['es' => 'Umbral de Puntos', 'en' => 'Points Threshold']),
                'key' => 'POINTS_THRESHOLD',
                'description' => json_encode(['es' => 'Valida si los puntos acumulados superan un minimo para estar activo', 'en' => 'Validates if accumulated points exceed a minimum to remain active']),
                'rule_category_type_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => json_encode(['es' => 'Rango de Puntos', 'en' => 'Points Range']),
                'key' => 'POINTS_RANGE',
                'description' => json_encode(['es' => 'Valida si los puntos acumulados estan dentro de un rango para pertenecer a un nivel', 'en' => 'Validates if accumulated points are within a range to belong to a level']),
                'rule_category_type_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => json_encode(['es' => 'Cierre de Ciclo Previo', 'en' => 'Previous Cycle Completion']),
                'key' => 'CYCLE_COMPLETION',
                'description' => json_encode(['es' => 'Valida haber cerrado al menos N ciclos completos del plan anterior', 'en' => 'Validates that at least N complete cycles of the previous plan have been closed']),
                'rule_category_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => json_encode(['es' => 'Puntos por Ciclo', 'en' => 'Cycle Points']),
                'key' => 'POINTS_PER_CYCLE',
                'description' => json_encode(['es' => 'Define la cantidad de puntos requeridos por ciclo y la fuente de red desde la que deben provenir', 'en' => 'Defines the required points per cycle and the network source they must come from']),
                'rule_category_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => json_encode(['es' => 'Agrupacion de Reglas', 'en' => 'Rule Grouping']),
                'key' => 'RULE_GROUPING',
                'description' => json_encode(['es' => 'Agrupa multiples reglas bajo una condicion logica AND u OR, permitiendo construir requisitos compuestos de forma jerarquica', 'en' => 'Groups multiple rules under an AND or OR logical condition, allowing hierarchical composite requirements to be built']),
                'rule_category_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => json_encode(['es' => 'Conteo de Titulos en Primera Generacion', 'en' => 'First Generation Title Count']),
                'key' => 'DOWNLINE_TITLE_COUNT',
                'description' => json_encode(['es' => 'Valida que existan N empresarios de un titulo especifico en la primera generacion', 'en' => 'Validates that N entrepreneurs of a specific title exist in the first generation']),
                'rule_category_type_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => json_encode(['es' => 'Conteo de Titulos Life en Red', 'en' => 'Life Title Count in Network']),
                'key' => 'LIFE_TITLE_COUNT',
                'description' => json_encode(['es' => 'Valida que existan N empresarios con titulo Life debajo de los Platinos de la red', 'en' => 'Validates that N Life-titled entrepreneurs exist beneath the Platinos in the network']),
                'rule_category_type_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id', 'key']);

        $this->insertMissingRows('form_sections', [
            ['id' => 1, 'owner_key' => 'POINTS_THRESHOLD', 'section_label' => json_encode(['es' => 'Configuracion del umbral', 'en' => 'Threshold Configuration']), 'section_key' => 'THRESHOLD_CONFIG', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'owner_key' => 'POINTS_RANGE', 'section_label' => json_encode(['es' => 'Configuracion del rango', 'en' => 'Range Configuration']), 'section_key' => 'RANGE_CONFIG', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'owner_key' => 'CYCLE_COMPLETION', 'section_label' => json_encode(['es' => 'Configuracion del ciclo', 'en' => 'Cycle Configuration']), 'section_key' => 'CYCLE_CONFIG', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'owner_key' => 'POINTS_PER_CYCLE', 'section_label' => json_encode(['es' => 'Combinaciones de ciclos validas', 'en' => 'Valid Cycle Combinations']), 'section_key' => 'CYCLE_COMBINATIONS', 'section_order' => 1, 'is_repeatable' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'owner_key' => 'DOWNLINE_TITLE_COUNT', 'section_label' => json_encode(['es' => 'Configuracion de generacion', 'en' => 'Generation Configuration']), 'section_key' => 'DOWNLINE_CONFIG', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'owner_key' => 'LIFE_TITLE_COUNT', 'section_label' => json_encode(['es' => 'Configuracion de conteo Life', 'en' => 'Life Count Configuration']), 'section_key' => 'LIFE_COUNT_CONFIG', 'section_order' => 1, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'owner_key' => 'POINTS_THRESHOLD', 'section_label' => json_encode(['es' => 'Fuente de puntos', 'en' => 'Points source']), 'section_key' => 'POINTS_SOURCE_CONFIG', 'section_order' => 2, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'owner_key' => 'POINTS_RANGE', 'section_label' => json_encode(['es' => 'Fuente de puntos', 'en' => 'Points source']), 'section_key' => 'POINTS_SOURCE_CONFIG', 'section_order' => 2, 'is_repeatable' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['id']);

        $this->insertMissingRows('form_fields', [
            ['id' => 1, 'form_section_id' => 1, 'field_label' => json_encode(['es' => 'Puntos minimos', 'en' => 'Minimum points']), 'field_key' => 'MIN_POINTS', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Cantidad minima de puntos para que la regla se cumpla.', 'en' => 'Minimum number of points required for the rule to be met.']), 'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'form_section_id' => 2, 'field_label' => json_encode(['es' => 'Puntos minimos del rango', 'en' => 'Range minimum points']), 'field_key' => 'MIN_POINTS', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Limite inferior del rango de puntos (inclusivo).', 'en' => 'Lower bound of the points range (inclusive).']), 'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'form_section_id' => 2, 'field_label' => json_encode(['es' => 'Puntos maximos del rango', 'en' => 'Range maximum points']), 'field_key' => 'MAX_POINTS', 'field_type' => 'number', 'is_required' => false, 'help_text' => json_encode(['es' => 'Limite superior del rango de puntos. Dejar vacio si no tiene tope.', 'en' => 'Upper bound of the points range. Leave empty if there is no cap.']), 'validation_rules' => '{"min":0,"decimals":2,"nullable":true}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'form_section_id' => 4, 'field_label' => json_encode(['es' => 'Plan anterior requerido', 'en' => 'Required previous plan']), 'field_key' => 'REQUIRED_PLAN', 'field_type' => 'select', 'is_required' => true, 'help_text' => json_encode(['es' => 'Plan que debe haberse cerrado antes de alcanzar este nivel.', 'en' => 'Plan that must have been completed before reaching this level.']), 'validation_rules' => '{"required":true,"options":"METHOD[fetchPlanChildren]"}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'form_section_id' => 4, 'field_label' => json_encode(['es' => 'Ciclos minimos a cerrar', 'en' => 'Minimum cycles to complete']), 'field_key' => 'MIN_CYCLES', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Cantidad minima de ciclos completos que deben haberse cerrado en el plan anterior.', 'en' => 'Minimum number of complete cycles that must have been closed in the previous plan.']), 'validation_rules' => '{"min":1}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'form_section_id' => 6, 'field_label' => json_encode(['es' => 'Ciclo a seleccionar', 'en' => 'Cycle to select']), 'field_key' => 'CYCLE_SELECTED', 'field_type' => 'select', 'is_required' => true, 'help_text' => json_encode(['es' => 'Elige a que ciclo aplica esta combinacion.', 'en' => 'Choose which cycle this combination applies to.']), 'validation_rules' => '{"required":true,"options":[12,13]}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'form_section_id' => 6, 'field_label' => json_encode(['es' => 'Puntos', 'en' => 'Points']), 'field_key' => 'CYCLE_POINTS', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Cantidad de puntos requeridos en este ciclo.', 'en' => 'Number of points required in this cycle.']), 'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'form_section_id' => 6, 'field_label' => json_encode(['es' => 'Fuente de puntos', 'en' => 'Points source']), 'field_key' => 'POINTS_SOURCES', 'field_type' => 'multiselect', 'is_required' => true, 'help_text' => json_encode(['es' => 'Selecciona que redes aportan puntos para esta combinacion de ciclo.', 'en' => 'Select which networks contribute points for this cycle combination.']), 'validation_rules' => '{"required":true,"options":[3,4,5]}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'form_section_id' => 8, 'field_label' => json_encode(['es' => 'Titulo requerido en la generacion', 'en' => 'Required title in generation']), 'field_key' => 'REQUIRED_PLAN', 'field_type' => 'select', 'is_required' => true, 'help_text' => json_encode(['es' => 'Titulo que deben tener los empresarios directos para contar.', 'en' => 'Title that direct entrepreneurs must hold to be counted.']), 'validation_rules' => '{"required":true,"options":"METHOD[fetchPlanChildren]"}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'form_section_id' => 8, 'field_label' => json_encode(['es' => 'Numero de generacion', 'en' => 'Generation number']), 'field_key' => 'GENERATION', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Generacion desde la cual se cuentan los empresarios (1 = hijos directos).', 'en' => 'Generation from which entrepreneurs are counted (1 = direct children).']), 'validation_rules' => '{"min":1}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'form_section_id' => 8, 'field_label' => json_encode(['es' => 'Cantidad minima de empresarios', 'en' => 'Minimum number of entrepreneurs']), 'field_key' => 'MIN_COUNT', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Cuantos empresarios con ese titulo deben existir en esa generacion.', 'en' => 'How many entrepreneurs with that title must exist in that generation.']), 'validation_rules' => '{"min":1}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 19, 'form_section_id' => 8, 'field_label' => json_encode(['es' => 'Multiplicador minimo de beneficio', 'en' => 'Minimum benefit multiplier']), 'field_key' => 'MIN_BENEFIT_MULTIPLIER', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Cada empresario debe haber alcanzado beneficios equivalentes al valor del punto multiplicado por este numero.', 'en' => 'Each entrepreneur must have reached benefits equal to the point value multiplied by this number.']), 'validation_rules' => '{"min":1}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'form_section_id' => 9, 'field_label' => json_encode(['es' => 'Debajo de que titulo', 'en' => 'Beneath which title']), 'field_key' => 'BENEATH_PLAN', 'field_type' => 'select', 'is_required' => true, 'help_text' => json_encode(['es' => 'Los empresarios Life se cuentan debajo de los empresarios de este titulo.', 'en' => 'Life entrepreneurs are counted beneath entrepreneurs of this title.']), 'validation_rules' => '{"required":true,"options":"METHOD[fetchPlanChildren]"}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 21, 'form_section_id' => 9, 'field_label' => json_encode(['es' => 'Cantidad minima de empresarios Life', 'en' => 'Minimum Life entrepreneurs']), 'field_key' => 'MIN_COUNT', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Total de empresarios con titulo Life requeridos en toda la red bajo los Platinos.', 'en' => 'Total Life-titled entrepreneurs required across the entire network beneath Platinos.']), 'validation_rules' => '{"min":1}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 22, 'form_section_id' => 9, 'field_label' => json_encode(['es' => 'Puntos minimos por empresario', 'en' => 'Minimum points per entrepreneur']), 'field_key' => 'MIN_POINTS_PER_MEMBER', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Puntos minimos que debe realizar cada empresario Life (personales o de su red No Life).', 'en' => 'Minimum points each Life entrepreneur must generate (personal or from their Non-Life network).']), 'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 23, 'form_section_id' => 10, 'field_label' => json_encode(['es' => 'Incluir compras personales', 'en' => 'Include personal purchases']), 'field_key' => 'INCLUDE_PERSONAL', 'field_type' => 'boolean', 'is_required' => false, 'help_text' => json_encode(['es' => 'Indica si las compras propias del empresario cuentan en el calculo.', 'en' => "Indicates whether the entrepreneur's own purchases count in the calculation."]), 'validation_rules' => '{"required":false}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 24, 'form_section_id' => 10, 'field_label' => json_encode(['es' => 'Incluir compras de hijos', 'en' => "Include children's purchases"]), 'field_key' => 'INCLUDE_CHILDREN', 'field_type' => 'boolean', 'is_required' => false, 'help_text' => json_encode(['es' => 'Indica si las compras de los referidos directos e indirectos cuentan.', 'en' => 'Indicates whether purchases from direct and indirect referrals count.']), 'validation_rules' => '{"required":false}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 25, 'form_section_id' => 11, 'field_label' => json_encode(['es' => 'Incluir compras personales', 'en' => 'Include personal purchases']), 'field_key' => 'INCLUDE_PERSONAL', 'field_type' => 'boolean', 'is_required' => false, 'help_text' => json_encode(['es' => 'Indica si las compras propias del empresario cuentan en el calculo.', 'en' => "Indicates whether the entrepreneur's own purchases count in the calculation."]), 'validation_rules' => '{"required":false}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 26, 'form_section_id' => 11, 'field_label' => json_encode(['es' => 'Incluir compras de hijos', 'en' => "Include children's purchases"]), 'field_key' => 'INCLUDE_CHILDREN', 'field_type' => 'boolean', 'is_required' => false, 'help_text' => json_encode(['es' => 'Indica si las compras de los referidos directos e indirectos cuentan.', 'en' => 'Indicates whether purchases from direct and indirect referrals count.']), 'validation_rules' => '{"required":false}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['id']);
    }
}
