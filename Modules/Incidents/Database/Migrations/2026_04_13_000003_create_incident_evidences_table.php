<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentEvidencesTable extends Migration
{
    public function up()
    {
        Schema::create('incident_evidences', function (Blueprint $table) {
            $table->id();
            $table->uuid('incident_id');
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('restrict');

            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->enum('actor_role', ['destination', 'origin', 'admin']);

            $table->string('file_url');
            $table->string('file_name');
            $table->string('file_mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->text('notes')->nullable();

            // Solo created_at — los registros son inmutables
            $table->timestamp('created_at')->useCurrent();

            $table->index('incident_id', 'idx_incident_evidences_incident');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_evidences');
    }
}
