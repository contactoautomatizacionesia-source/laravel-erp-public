
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parameter_settings', function (Blueprint $table) {
            $table->id();
            
            // --- Campos de Configuración ---
            $table->string('parameter_name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->integer('value_limit')->nullable();
            $table->decimal('monetary_value', 15, 2)->nullable();
            
            // Relación con Staff (Doble Aprobación)
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');

            // --- Campos de Auditoría Requeridos ---
            // ID del usuario Root/Admin que creó o editó el registro
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Marcas de tiempo estándar (created_at, updated_at)
            $table->timestamps();
        });

        // Insertar registros iniciales basados en la interfaz sugerida
        $now = date('Y-m-d H:i:s');
        $parameters = [
            [
                'parameter_name' => 'Stock de Productos',
                'slug'           => 'product-stock',
                'is_active'      => 1,
                'min_value'      => 5,
                'max_value'      => 100,
                'value_limit'    => null,
                'monetary_value' => null,
                'staff_id'       => null,
                'created_by'     => 1, // Asignado al Super Admin por defecto
                'created_at'     => $now,
                'updated_at'     => $now
            ],
            [
                'parameter_name' => 'Fallos Conteo Diario',
                'slug'           => 'daily-count-failures',
                'is_active'      => 1,
                'min_value'      => null,
                'max_value'      => null,
                'value_limit'    => 3,
                'monetary_value' => null,
                'staff_id'       => null,
                'created_by'     => 1,
                'created_at'     => $now,
                'updated_at'     => $now
            ],
            [
                'parameter_name' => 'Doble Aprobación',
                'slug'           => 'double-approval',
                'is_active'      => 0, // Toggle OFF según imagen
                'min_value'      => null,
                'max_value'      => null,
                'value_limit'    => null,
                'monetary_value' => null,
                'staff_id'       => null, // Se selecciona vía UI
                'created_by'     => 1,
                'created_at'     => $now,
                'updated_at'     => $now
            ],
            [
                'parameter_name' => 'Apertura de Caja',
                'slug'           => 'cash-opening',
                'is_active'      => 1,
                'min_value'      => null,
                'max_value'      => null,
                'value_limit'    => null,
                'monetary_value' => 500000.00,
                'staff_id'       => null,
                'created_by'     => 1,
                'created_at'     => $now,
                'updated_at'     => $now
            ],
        ];

        DB::table('parameter_settings')->insert($parameters);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_settings');
    }
};