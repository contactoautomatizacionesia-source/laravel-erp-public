<?php

namespace App\Seeders\Contracts;

/**
 * Marker interface para seeders de datos de prueba / fixtures de desarrollo.
 *
 * Reglas obligatorias para implementar esta interface:
 *  - El seeder NUNCA debe contener datos reales de negocio (eso va en DeployableSeeder)
 *  - Debe ser idempotente: usar updateOrInsert(), firstOrCreate(), o SkipsExistingCatalogRows
 *  - Puede contener datos ficticios, usuarios de prueba, configuraciones de demo, etc.
 *
 * Comandos que ejecutan estos seeders:
 *  - php artisan seed:staging  → develop + local (también ejecuta DeployableSeeder primero)
 *  - NUNCA se ejecuta con seed:deploy (no llega a producción)
 */
interface StagingSeeder
{
}
