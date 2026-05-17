<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueCodeToRuleTable extends Migration
{
    public function up(): void
    {
            // rule: unique en code
            if (!$this->indexExists('rule', 'rule_code_unique')) {
                Schema::table('rule', function (Blueprint $table) {
                    $table->unique('code', 'rule_code_unique');
                });
            }

            // benefit: columna code + unique
            if (!Schema::hasColumn('benefit', 'code')) {
                Schema::table('benefit', function (Blueprint $table) {
                    $table->string('code', 20)->nullable()->unique('benefit_code_unique')
                        ->comment('Identificador de negocio: B1, B17...')
                        ->after('id');
                });
            } elseif (!$this->indexExists('benefit', 'benefit_code_unique')) {
                Schema::table('benefit', function (Blueprint $table) {
                    $table->unique('code', 'benefit_code_unique');
                });
            }

            // benefit_form_sections: is_repeatable
            if (!Schema::hasColumn('benefit_form_sections', 'is_repeatable')) {
                Schema::table('benefit_form_sections', function (Blueprint $table) {
                    $table->boolean('is_repeatable')->default(false)->after('section_order');
                });
            }

            // rule_form_fields / benefit_form_fields: agregar money al enum
            DB::statement("ALTER TABLE rule_form_fields MODIFY COLUMN field_type ENUM('number','select','boolean','text','money') NOT NULL DEFAULT 'text'");
            DB::statement("ALTER TABLE benefit_form_fields MODIFY COLUMN field_type ENUM('number','select','boolean','text','money') NOT NULL DEFAULT 'text'");

            // plan: reemplazar plan_scale_id por scale_type
            if (Schema::hasColumn('plan', 'plan_scale_id')) {
                Schema::table('plan', function (Blueprint $table) {
                    if ($this->indexExists('plan', 'plan_plan_scale_id_foreign')) {
                        $table->dropForeign(['plan_scale_id']);
                    }
                    $table->dropColumn('plan_scale_id');
                });
            }
            
            if (!Schema::hasColumn('plan', 'scale_type')) {
                Schema::table('plan', function (Blueprint $table) {
                    $table->enum('scale_type', ['CYCLE', 'CUMULATIVE'])->nullable()
                        ->comment('Tipo de escala del plan: CYCLE (por ciclos) o CUMULATIVE (acumulativa)')
                        ->after('id');
                });
            }
    }

    public function down(): void
    {
            // rule: quitar unique
            if ($this->indexExists('rule', 'rule_code_unique')) {
                Schema::table('rule', function (Blueprint $table) {
                    $table->dropUnique('rule_code_unique');
                });
            }

            // benefit: quitar unique y columna code
            if ($this->indexExists('benefit', 'benefit_code_unique')) {
                Schema::table('benefit', function (Blueprint $table) {
                    $table->dropUnique('benefit_code_unique');
                });
            }
            if (Schema::hasColumn('benefit', 'code')) {
                Schema::table('benefit', function (Blueprint $table) {
                    $table->dropColumn('code');
                });
            }

            // benefit_form_sections: quitar is_repeatable
            if (Schema::hasColumn('benefit_form_sections', 'is_repeatable')) {
                Schema::table('benefit_form_sections', function (Blueprint $table) {
                    $table->dropColumn('is_repeatable');
                });
            }

            // rule_form_fields / benefit_form_fields: quitar money del enum
            DB::statement("ALTER TABLE rule_form_fields MODIFY COLUMN field_type ENUM('number','select','boolean','text') NOT NULL DEFAULT 'text'");
            DB::statement("ALTER TABLE benefit_form_fields MODIFY COLUMN field_type ENUM('number','select','boolean','text') NOT NULL DEFAULT 'text'");

            // plan: restaurar plan_scale_id
            if (Schema::hasColumn('plan', 'scale_type')) {
                Schema::table('plan', function (Blueprint $table) {
                    $table->dropColumn('scale_type');
                });
            }
            if (!Schema::hasColumn('plan', 'plan_scale_id')) {
                Schema::table('plan', function (Blueprint $table) {
                    $table->unsignedBigInteger('plan_scale_id')->nullable()->after('id');
                });
            }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
}
