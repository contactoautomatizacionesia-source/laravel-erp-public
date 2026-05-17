<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new `styles` column
        Schema::table('plan', function (Blueprint $table) {
            $table->json('styles')->nullable()->after('image');
        });

        // 2. Migrate existing color data into styles.primaryColor
        DB::table('plan')->whereNotNull('color')->orderBy('id')->each(function ($row) {
            $color = is_string($row->color) ? json_decode($row->color, true) : null;
            if ($color) {
                DB::table('plan')->where('id', $row->id)->update([
                    'styles' => json_encode($color),
                ]);
            }
        });

        // 3. Drop old color column
        Schema::table('plan', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }

    public function down(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            $table->json('color')->nullable()->after('image');
        });

        DB::table('plan')->whereNotNull('styles')->orderBy('id')->each(function ($row) {
            $styles = is_string($row->styles) ? json_decode($row->styles, true) : null;
            if ($styles) {
                DB::table('plan')->where('id', $row->id)->update([
                    'color' => json_encode(array_intersect_key($styles, ['primaryColor' => true])),
                ]);
            }
        });

        Schema::table('plan', function (Blueprint $table) {
            $table->dropColumn('styles');
        });
    }
};
