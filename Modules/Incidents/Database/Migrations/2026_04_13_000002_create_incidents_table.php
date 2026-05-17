<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentsTable extends Migration
{
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sequential_code', 20)->unique();

            $table->enum('incident_type', ['transfer', 'inventory_count']);
            $table->enum('status', [
                'pending',
                'awaiting_statement',
                'under_investigation',
                'closed',
                'voided',
            ])->default('pending');

            // Referencia polimórfica al documento fuente — sin FK directa
            $table->string('source_type', 100)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // Producto
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->string('product_name_snapshot');
            $table->decimal('public_price_snapshot', 18, 2);
            $table->unsignedInteger('missing_units');
            // Columna generada: total_value = missing_units * public_price_snapshot
            $table->decimal('total_value', 18, 2)->storedAs('missing_units * public_price_snapshot');

            // Responsabilidad (sede destino o sede del conteo)
            $table->unsignedBigInteger('responsible_branch_id');
            $table->foreign('responsible_branch_id')->references('id')->on('cost_centers')->onDelete('restrict');
            $table->foreignId('responsible_user_id')->constrained('users')->onDelete('restrict');

            // Origen (solo en novedades de tipo transfer)
            $table->unsignedBigInteger('origin_branch_id')->nullable();
            $table->foreign('origin_branch_id')->references('id')->on('cost_centers')->nullOnDelete();
            $table->foreignId('origin_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Plazo de pronunciamiento
            $table->unsignedInteger('statement_deadline_hours')->default(48);
            $table->timestamp('statement_expires_at')->nullable();
            $table->boolean('statement_reminder_sent')->default(false);

            // Pronunciamiento del origen
            $table->timestamp('statement_submitted_at')->nullable();
            $table->enum('statement_type', ['acknowledged', 'rejected'])->nullable();

            // Resolución del administrador
            $table->enum('resolution_party', ['advisor', 'organization', 'voided'])->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            // Referencias externas
            $table->uuid('inventory_reversal_id')->nullable();   // Movimiento de reversión — sin FK
            $table->unsignedBigInteger('cash_closing_id')->nullable(); // FK diferida: cash_closings puede no existir aún

            $table->timestamps();

            // Índices para queries frecuentes
            $table->index('status', 'idx_incidents_status');
            $table->index('responsible_branch_id', 'idx_incidents_responsible_branch');
            $table->index(['source_type', 'source_id'], 'idx_incidents_source');
            $table->index('statement_expires_at', 'idx_incidents_expires_at');
            $table->index('cash_closing_id', 'idx_incidents_cash_closing');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidents');
    }
}
