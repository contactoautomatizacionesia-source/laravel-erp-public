<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `orders` MODIFY `total_points` DOUBLE(20,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE `order_product_details` MODIFY `unit_club_point` DOUBLE(20,4) UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `orders` MODIFY `total_points` INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE `order_product_details` MODIFY `unit_club_point` INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
