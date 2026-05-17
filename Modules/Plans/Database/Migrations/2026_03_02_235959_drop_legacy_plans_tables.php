<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina las tablas de la abstracción anterior antes de crear la nueva.
 *
 * Las tablas a eliminar son:
 *   - benefit_plan  (pivot viejo)
 *   - plan_rule     (pivot viejo)
 *   - benefits      (tabla vieja)
 *   - rules         (tabla vieja)
 *   - plans         (tabla vieja)
 *
 * Se usa dropIfExists para que sea idempotente:
 * funciona igual en entornos donde las tablas ya existen
 * o en instalaciones frescas donde nunca existieron.
 */
class DropLegacyPlansTables extends Migration
{
    public function up()
    {
        // Primero los pivots (foreign keys apuntan a las tablas principales)
        Schema::dropIfExists('benefit_plan');
        Schema::dropIfExists('plan_rule');

        // Luego las tablas principales
        Schema::dropIfExists('benefits');
        Schema::dropIfExists('rules');
        Schema::dropIfExists('plans');
    }

    /**
     * No se puede revertir una eliminación sin tener el esquema original.
     * Si necesitas hacer rollback completo, revierte desde las migraciones 2026_03_03_*.
     */
    public function down()
    {
        // Intencional: no se recrean las tablas legacy.
    }
}
