<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDianSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificamos si la tabla no existe para evitar errores
        if (!Schema::hasTable('dian_settings')) {
            
            Schema::create('dian_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('brand_id')->unique(); // "solo puede existir una por marca"
                $table->string('api_url')->nullable();      // Url Api
                $table->string('api_user')->nullable();     // Usuario (correo en tu ejemplo)
                $table->text('api_password')->nullable(); // Contraseña
                $table->text('api_token')->nullable();      // Token (Opcional, text por si es largo)
                $table->boolean('is_active')->default(0);    // default(0) = Inactivo, 1 = Activo                              
                $table->boolean('connection_status')->default(0); // Indicador de "Conección con API" ... default(0) = Con falla, 1 = Funcional                                
                $table->text('last_response')->nullable(); // Para guardar el mensaje de respuesta (ej: "Error 401" o "Conectado")

                // 4. Auditoría (Quién creó o editó)
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                
                $table->timestamps();

                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dian_settings');
    }
}