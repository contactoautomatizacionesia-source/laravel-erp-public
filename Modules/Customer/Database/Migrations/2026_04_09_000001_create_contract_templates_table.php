<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();

            // Brand que emite este contrato — FK a brands.id
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');

            // Tipo de contrato — lista cerrada definida en ContractTemplate::CONTRACT_TYPES.
            // Actualmente: 'REGISTER'
            $table->enum('contract_type', ['REGISTER']);

            // Nombre de la vista Blade que renderiza el HTML del contrato.
            // Relativo al namespace de vistas del módulo Customer.
            // Ejemplo: 'customer::contracts.sagrilaft'
            $table->string('blade_view');

            // Prefijo del nombre del archivo PDF generado.
            // El servicio añade "_{userId}.pdf" y "_{userId}_firmado.pdf".
            // Ejemplo: 'proinat_registro'  →  proinat_registro_42.pdf
            $table->string('filename_prefix');

            // Permite desactivar una plantilla sin eliminarla.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'brand_id']);
        });

        // Plantilla para Colzomac (brand_id = 1)
        DB::table('contract_templates')->insert([
            'brand_id'        => 1,
            'contract_type'   => 'REGISTER',
            'blade_view'      => 'customer::contracts.sagrilaft',
            'filename_prefix' => 'colzomac_contrato_afiliacion',
            'is_active'       => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Plantilla para Proinát (brand_id = 2)
        DB::table('contract_templates')->insert([
            'brand_id'        => 2,
            'contract_type'   => 'REGISTER',
            'blade_view'      => 'customer::contracts.sagrilaft',
            'filename_prefix' => 'proinat_contrato_afiliacion',
            'is_active'       => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
