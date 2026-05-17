<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetPreloaderStyle25OnGeneralSettings extends Migration
{
    public function up(): void
    {
        DB::table('general_settings')->limit(1)->update([
            'preloader_style' => 25
        ]);
    }

    public function down(): void
    {
        // Valor por defecto anterior
        DB::table('general_settings')->limit(1)->update([
            'preloader_style' => 1
        ]);
    }
};
