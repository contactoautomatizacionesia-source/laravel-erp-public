<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('backendmenus')
            ->where('name', 'common.double_approval')
            ->update([
                'parent_id'  => 132,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('backendmenus')
            ->where('name', 'common.double_approval')
            ->update([
                'parent_id'  => 53,
                'updated_at' => now(),
            ]);
    }
};
