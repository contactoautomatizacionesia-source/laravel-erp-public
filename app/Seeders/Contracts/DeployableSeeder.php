<?php

namespace App\Seeders\Contracts;

/**
 * Marker interface para seeders idempotentes aptos para producción y develop.
 *
 * Reglas obligatorias para implementar esta interface:
 *  - El seeder NUNCA debe usar truncate(), delete() ni DB::statement('DELETE ...')
 *  - Cada fila debe insertarse con updateOrInsert(), firstOrCreate(), o el trait SkipsExistingCatalogRows
 *  - El seeder debe poder ejecutarse N veces sin generar duplicados ni errores
 *
 * Comandos que ejecutan estos seeders:
 *  - php artisan seed:deploy   → producción y develop (Jenkins)
 *  - php artisan seed:staging  → develop + local (incluye también StagingSeeder)
 */
interface DeployableSeeder
{
}
