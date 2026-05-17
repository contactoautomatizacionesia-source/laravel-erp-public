<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: ampliar el ENUM añadiendo 'closed'
        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN status ENUM('pending','correct','incorrect','closed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN audit_status ENUM('pending','rejected','approved','closed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revertir los registros cerrados a pending antes de estrechar el ENUM
        DB::statement("UPDATE inventory_counts SET status = 'pending' WHERE status = 'closed'");
        DB::statement("UPDATE inventory_counts SET audit_status = 'pending' WHERE audit_status = 'closed'");
        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN status ENUM('pending','correct','incorrect') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE inventory_counts MODIFY COLUMN audit_status ENUM('pending','rejected','approved') NOT NULL DEFAULT 'pending'");
    }
};
