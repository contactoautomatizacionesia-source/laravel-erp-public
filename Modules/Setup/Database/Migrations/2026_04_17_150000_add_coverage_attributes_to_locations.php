<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('status');
            }
            if (!collect(DB::select("SHOW INDEX FROM countries WHERE Key_name = 'countries_status_index'"))->count()) {
                $table->index('status');
            }
            if (!collect(DB::select("SHOW INDEX FROM countries WHERE Key_name = 'countries_is_default_index'"))->count()) {
                $table->index('is_default');
            }
        });

        Schema::table('states', function (Blueprint $table) {
            if (!collect(DB::select("SHOW INDEX FROM states WHERE Key_name = 'states_country_id_status_index'"))->count()) {
                $table->index(['country_id', 'status']);
            }
        });

        Schema::table('cities', function (Blueprint $table) {
            if (!collect(DB::select("SHOW INDEX FROM cities WHERE Key_name = 'cities_state_id_status_index'"))->count()) {
                $table->index(['state_id', 'status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['state_id', 'status']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropIndex(['country_id', 'status']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['status']);
            if (Schema::hasColumn('countries', 'is_default')) {
                $table->dropIndex(['is_default']);
                $table->dropColumn('is_default');
            }
        });
    }
};
