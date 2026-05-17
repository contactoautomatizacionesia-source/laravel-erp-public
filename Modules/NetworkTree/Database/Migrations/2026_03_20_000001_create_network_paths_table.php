<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkPathsTable extends Migration
{
    public function up(): void
    {
        Schema::create('network_paths', function (Blueprint $table) {
            $table->id();

            // El nodo descendiente (el empresario en cuestión)
            $table->unsignedBigInteger('entrepreneur_id')
                ->comment('ID del empresario (nodo descendiente)');

            // El nodo ancestro (puede ser él mismo cuando depth=0)
            $table->unsignedBigInteger('ancestor_id')
                ->comment('ID del ancestro en la jerarquía');

            // Distancia entre nodos: 0 = self-reference, 1 = padre directo, 2 = abuelo, etc.
            $table->unsignedSmallInteger('depth')
                ->comment('Profundidad: 0=self, 1=padre, 2=abuelo, ...');

            $table->timestamps();

            // Cada combinación entrepreneur+ancestor es única
            $table->unique(['entrepreneur_id', 'ancestor_id'], 'uq_network_paths');

            // Índice para obtener todos los descendientes de un ancestro (con filtro de profundidad)
            $table->index(['ancestor_id', 'depth'], 'idx_ancestor_depth');

            // Índice para obtener todos los ancestros de un empresario
            $table->index(['entrepreneur_id', 'depth'], 'idx_entrepreneur_depth');

            $table->foreign('entrepreneur_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('ancestor_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_paths');
    }
}
