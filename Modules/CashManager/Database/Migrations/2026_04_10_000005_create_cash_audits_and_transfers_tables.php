<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de Novedades (Sobrantes/Faltantes)
        Schema::create('cash_discrepancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('cash_sessions');
            
            $table->enum('type', ['SHORTAGE', 'SURPLUS']);
            $table->decimal('amount', 18, 2);
            $table->text('justification');
            $table->foreignId('authorized_by')->nullable()->constrained('users');

            $table->timestamps();
        });

        // Remesas de Dinero (Cambio de Custodia)
        Schema::create('cash_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('origin_session_id')->constrained('cash_sessions');
            $table->foreignUuid('destination_box_id')->constrained('cash_boxes');
            
            $table->decimal('amount', 18, 2);
            $table->string('transfer_hash')->unique()->comment('Código de seguridad de la bolsa física');
            $table->string('status', 20)->default('IN_TRANSIT');
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');
        Schema::dropIfExists('cash_discrepancies');
    }
};