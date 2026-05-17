<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('type_document_id')->nullable()->after('id');
            $table->string('document_number')->nullable()->after('type_document_id');
            $table->string('cost_center')->nullable()->after('document_number');
            $table->foreign('type_document_id')->references('id')->on('type_documents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['type_document_id']);
            $table->dropColumn(['type_document_id', 'document_number', 'cost_center']);
        });
    }
};
