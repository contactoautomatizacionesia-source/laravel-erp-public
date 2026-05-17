<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertDocumentationFormalizationRuleCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('rule_category')->insert([
            'key' => 'DOCUMENTATION_FORMALIZATION',
            'name' => '{"es":"Documentaci\u00f3n y formalizaci\u00f3n","en":"Documentation and formalization"}',
            'description' => '{"es":"Valida si el empresario realiz\u00f3 la firma o entrega de documentaci\u00f3n","en":"Confirm whether the employer signed or submitted the documentation"}',
            'rule_category_type_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('rule_category')
            ->where('key', 'DOCUMENTATION_FORMALIZATION')
            ->delete();
    }
}
