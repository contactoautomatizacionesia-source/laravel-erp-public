<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\GeneralSetting\Entities\ParameterSetting;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadimos la columna para guardar el array JSON de campos bloqueados
        if (!Schema::hasColumn('parameter_settings', 'json_value')) {
            Schema::table('parameter_settings', function (Blueprint $table) {
                $table->json('json_value')->nullable()->after('monetary_value');
            });
        }

        // 2. Insertamos los parámetros
        ParameterSetting::insert([
            [
                'parameter_name' => 'entrepreneur_data_ttl',
                'slug' => 'entrepreneur-data-ttl',
                'is_active' => 1,
                'value_limit' => 6, // 6 meses por defecto
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parameter_name' => 'kyc_readonly_fields',
                'slug' => 'kyc-readonly-fields',
                'is_active' => 1,
                'value_limit' => null,
                'json_value' => json_encode(["referral_code"]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        ParameterSetting::whereIn('slug', ['entrepreneur-data-ttl', 'kyc-readonly-fields'])->delete();

        if (Schema::hasColumn('parameter_settings', 'json_value')) {
            Schema::table('parameter_settings', function (Blueprint $table) {
                $table->dropColumn('json_value');
            });
        }
    }
};
