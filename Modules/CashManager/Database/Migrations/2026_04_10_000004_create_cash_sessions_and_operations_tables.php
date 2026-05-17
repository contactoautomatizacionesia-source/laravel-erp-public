<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sesiones Diarias
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assignment_id')->constrained('cash_box_assignments');
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            
            $table->decimal('opening_base', 18, 2)->comment('Base verificada al abrir');
            $table->decimal('total_system_expected', 18, 2)->default(0);
            $table->decimal('total_physical_counted', 18, 2)->default(0);
            $table->decimal('discrepancy_amount', 18, 2)->default(0);
            
            $table->string('status', 20)->default('OPEN'); 

            $table->timestamps();
        });

        // Detalle de Conteo Físico
        Schema::create('cash_session_denominations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('cash_sessions')->onDelete('cascade');
            $table->foreignUuid('denomination_id')->constrained('cat_denominations');
            
            $table->integer('quantity');
            $table->decimal('subtotal', 18, 2);
            
            $table->timestamps();
        });

        // Conciliación de Medios de Pago (Usa tabla payment_methods existente)
        Schema::create('cash_session_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('cash_sessions')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            
            $table->decimal('total_amount', 18, 2);
            $table->integer('transaction_count')->default(0);
            $table->json('reference_data')->nullable()->comment('Lotes de datáfono, vouchers, etc.');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_session_payments');
        Schema::dropIfExists('cash_session_denominations');
        Schema::dropIfExists('cash_sessions');
    }
};