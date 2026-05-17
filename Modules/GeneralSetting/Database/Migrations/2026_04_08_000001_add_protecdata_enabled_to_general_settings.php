<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'protecdata_enabled')) {
                $table->tinyInteger('protecdata_enabled')->default(0)->after('user_info_update');
            }
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'protecdata_enabled')) {
                $table->dropColumn('protecdata_enabled');
            }
        });
    }
};
