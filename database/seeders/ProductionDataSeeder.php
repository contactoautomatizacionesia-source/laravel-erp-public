<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\GeneralSetting\Database\Seeders\GeneralSettingDatabaseSeeder;

class ProductionDataSeeder extends Seeder
{
    /**
     * SEEDER MAESTRO PARA PRODUCCIÓN
     * * REGLAS PARA DESARROLLADORES:
     * 1. Solo agregar seeders que inserten DATOS MAESTROS (Países, Monedas, Roles, Catálogos).
     * 2. PROHIBIDO usar Factories o generar datos de prueba ("Test", "Admin123", etc.).
     * 3. Todos los seeders llamados aquí DEBEN ser IDEMPOTENTES (usar updateOrCreate).
     * 4. Si un módulo nuevo requiere datos base para funcionar, se registra aquí.
     */
    public function run(): void
    {
        $this->command->info('Iniciando carga de datos maestros para Producción...');

        $seeders = [            
            // --- MÓDULO: GENERAL SETTINGS ---
            GeneralSettingDatabaseSeeder::class,
            
            // --- OTROS MÓDULOS ---
            // Agrega aquí los seeders de otros módulos siguiendo el estándar
            // \Modules\Facturacion\Database\Seeders\TaxSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
                $this->command->info("Ejecutado: $seeder");
            } else {
                $this->command->warn("Saltado: El seeder $seeder no existe o el módulo está deshabilitado.");
                Log::warning("Seeder de producción no encontrado: $seeder");
            }
        }

        $this->command->info('Finalizado: El CRM tiene los datos mínimos para operar.');
    }
}