<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('backendmenus')
            ->where('name', 'incidents::menu.incidents')
            ->update(['icon' => 'ti-flag-alt']);
    }

    public function down(): void
    {
        DB::table('backendmenus')
            ->where('name', 'incidents::menu.incidents')
            ->update(['icon' => 'ti-alert-circle']);
    }
};
