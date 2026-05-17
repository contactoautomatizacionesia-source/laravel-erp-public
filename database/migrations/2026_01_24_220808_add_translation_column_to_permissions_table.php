<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('permissions', 'translation')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('translation')->nullable()->after('name');
            });

            // Llenar la columna con claves de traducción (procesando en lotes)
            DB::table('permissions')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($permissions) {
                    foreach ($permissions as $permission) {
                        $permission_key = Str::slug($permission->name, '_');
                        DB::table('permissions')
                            ->where('id', $permission->id)
                            ->update(['translation' => 'permission.' . $permission_key]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('permissions', 'translation')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('translation');
            });
        }
    }
};
