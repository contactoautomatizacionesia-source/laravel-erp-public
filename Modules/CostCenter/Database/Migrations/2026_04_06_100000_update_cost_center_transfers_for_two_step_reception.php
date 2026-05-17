<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Actualizar la tabla principal de transferencias
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->string('status', 50)->default('dispatched')->after('movement_type_id')->comment('dispatched, received, received_with_discrepancies');
            $table->text('reception_notes')->nullable()->after('reason');
            $table->timestamp('dispatched_at')->nullable()->after('created_by');
            $table->timestamp('received_at')->nullable()->after('dispatched_at');
        });

        // 2. Nueva tabla para los items de la transferencia (Para saber qué se envió vs qué llegó)
        Schema::create('cost_center_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('product_sku_id');
            $table->unsignedBigInteger('lot_id')->nullable();

            $table->decimal('dispatched_qty', 16, 2)->default(0);
            $table->decimal('received_qty', 16, 2)->nullable(); // Null significa que aún no se recibe
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('cost_center_transfers')->onDelete('cascade');
            $table->foreign('product_sku_id')->references('id')->on('product_sku')->onDelete('restrict');
            $table->foreign('lot_id')->references('id')->on('product_lots')->onDelete('set null');
        });

        // 3. Nueva tabla para las Novedades / Discrepancias
        Schema::create('cost_center_transfer_discrepancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_item_id');
            $table->unsignedBigInteger('novelty_id')->comment('Referencia a system_catalogs'); // Aquí aplicamos tu sugerencia

            $table->decimal('difference_qty', 16, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('evidence_path')->nullable()->comment('Ruta del PDF u archivo de evidencia');
            $table->timestamps();

            $table->foreign('transfer_item_id')->references('id')->on('cost_center_transfer_items')->onDelete('cascade');
            $table->foreign('novelty_id')->references('id')->on('system_catalogs')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cost_center_transfer_discrepancies');
        Schema::dropIfExists('cost_center_transfer_items');

        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropColumn(['status', 'reception_notes', 'dispatched_at', 'received_at']);
        });
    }
};
