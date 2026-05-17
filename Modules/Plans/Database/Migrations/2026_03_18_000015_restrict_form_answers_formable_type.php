<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE form_answers
            MODIFY COLUMN formable_type ENUM('rule','benefit') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE form_answers
            MODIFY COLUMN formable_type VARCHAR(100) NOT NULL
        ");
    }
};
