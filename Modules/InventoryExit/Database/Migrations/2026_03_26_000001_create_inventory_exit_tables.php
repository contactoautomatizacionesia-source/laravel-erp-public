<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryExitTables extends Migration
{
    public function up()
    {
        // =========================================================
        // inventory_exit_requests — cabecera de la solicitud
        // =========================================================
        Schema::create('inventory_exit_requests', function (Blueprint $table) {
            $table->id();

            // Motivo (FK a system_catalogs — inventory_out_reason)
            $table->unsignedBigInteger('exit_reason_id');
            $table->foreign('exit_reason_id')->references('id')->on('system_catalogs');

            // Bodega / Centro de costo de origen
            $table->unsignedBigInteger('cost_center_id');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');

            // Estado del flujo
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Fecha solicitada de salida
            $table->date('exit_date');

            // Justificación obligatoria
            $table->text('observation');

            // Usuarios
            $table->unsignedBigInteger('requested_by');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users');
            $table->timestamp('approved_at')->nullable();

            // Nota del admin al aprobar/rechazar
            $table->text('approval_note')->nullable();

            // Audit trail — solicitud
            $table->string('requested_ip', 45)->nullable();
            $table->text('requested_user_agent')->nullable();

            // Audit trail — aprobación
            $table->string('approved_ip', 45)->nullable();
            $table->text('approved_user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('cost_center_id');
            $table->index('requested_by');
            $table->index('exit_date');
        });

        // =========================================================
        // inventory_exit_items — detalle de productos por solicitud
        // =========================================================
        Schema::create('inventory_exit_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inventory_exit_request_id');
            $table->foreign('inventory_exit_request_id')
                ->references('id')->on('inventory_exit_requests')
                ->onDelete('cascade');

            $table->unsignedBigInteger('product_sku_id');
            $table->foreign('product_sku_id')->references('id')->on('product_sku');

            // Trazabilidad de lote
            $table->unsignedBigInteger('lot_id')->nullable();
            $table->foreign('lot_id')->references('id')->on('product_lots');

            $table->decimal('qty_requested', 16, 2);
            // El admin puede ajustar la cantidad al aprobar
            $table->decimal('qty_approved', 16, 2)->nullable();

            $table->timestamps();

            $table->index('inventory_exit_request_id');
            $table->index('product_sku_id');
            $table->index('lot_id');
        });

        // =========================================================
        // inventory_exit_documents — archivos soporte adjuntos
        // =========================================================
        Schema::create('inventory_exit_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inventory_exit_request_id');
            $table->foreign('inventory_exit_request_id')
                ->references('id')->on('inventory_exit_requests')
                ->onDelete('cascade');

            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100);

            $table->unsignedBigInteger('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users');

            $table->timestamps();

            $table->index('inventory_exit_request_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_exit_documents');
        Schema::dropIfExists('inventory_exit_items');
        Schema::dropIfExists('inventory_exit_requests');
    }
}
