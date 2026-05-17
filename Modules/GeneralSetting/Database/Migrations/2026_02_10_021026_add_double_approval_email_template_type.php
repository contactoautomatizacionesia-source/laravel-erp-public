<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddDoubleApprovalEmailTemplateType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        // Registramos únicamente el tipo de plantilla para el motor de correos
        DB::table('email_template_types')->insert([
            'type' => 'double_approval_template',
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminamos el tipo de plantilla si se revierte la migración
        DB::table('email_template_types')->where('type', 'double_approval_template')->delete();
    }
}
