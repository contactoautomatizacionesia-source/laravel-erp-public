<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_documents', function (Blueprint $table) {
            if (Schema::hasColumn('signature_documents', 'sign_url')) {
                $table->dropColumn('sign_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signature_documents', function (Blueprint $table) {
            $table->string('sign_url')->nullable()->after('protecdata_process_id');
        });
    }
};
