<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateCycleLogsPhaseEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE cycle_logs MODIFY phase VARCHAR(50) NOT NULL DEFAULT 'pipeline'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE cycle_logs MODIFY phase ENUM(
            'pre_validation',
            'consolidation',
            'pdf_generation',
            'block',
            'notification'
        ) NOT NULL");
    }
}
