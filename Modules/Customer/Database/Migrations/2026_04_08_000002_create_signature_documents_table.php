<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('signature_batch_id')
                ->constrained('signature_batches')
                ->cascadeOnDelete();

            // Identificación del contrato
            $table->string('company_name');           // Nombre de la empresa del contrato
            $table->string('contract_type', 100);     // ej: 'sagrilaft', 'vinculacion'

            // Nombres de archivo en la carpeta Contratos/ del File Explorer
            $table->string('original_filename');      // PDF sin firmar  ej: empresa_a_contrato_42.pdf
            $table->string('signed_filename');        // PDF firmado     ej: empresa_a_contrato_42_firmado.pdf

            // Integración con ProtecData
            // NULL cuando PROTECDATA_ENABLED=false o si el envío falló (pendiente de reintento)
            $table->string('protecdata_process_id')->nullable()->unique();

            // Estado del documento individual
            $table->enum('status', ['pending', 'signed', 'rejected'])->default('pending');

            // Resultado de la firma
            $table->timestamp('signed_at')->nullable();
            $table->string('pdf_local_path')->nullable(); // Ruta en storage del PDF firmado

            $table->timestamps();

            $table->index(['signature_batch_id', 'status']);
            $table->index('protecdata_process_id'); // Búsqueda rápida en el callback
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_documents');
    }
};
