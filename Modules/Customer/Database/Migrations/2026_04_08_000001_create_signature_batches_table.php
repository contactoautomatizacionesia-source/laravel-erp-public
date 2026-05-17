<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Qué evento originó este lote: registration | data_update | manual
            $table->string('trigger', 50)->default('registration');

            // Estado global del lote calculado a partir de sus documentos hijos
            $table->enum('status', ['pending', 'partial', 'completed'])->default('pending');

            // Contadores denormalizados para consultas rápidas sin JOIN
            $table->unsignedTinyInteger('total_docs')->default(0);
            $table->unsignedTinyInteger('signed_docs')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_batches');
    }
};
