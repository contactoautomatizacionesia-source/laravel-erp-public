<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTimesTriggeredFromRuleAndBenefit extends Migration
{
    public function up(): void
    {
        Schema::table('rule', function (Blueprint $table) {
            $table->dropColumn('times_triggered');
        });

        Schema::table('benefit', function (Blueprint $table) {
            $table->dropColumn('times_triggered');
        });
    }

    public function down(): void
    {
        Schema::table('rule', function (Blueprint $table) {
            $table->integer('times_triggered')->nullable()
                ->comment('Cuántas veces puede dispararse esta regla en la vida del empresario. Null = sin límite');
        });

        Schema::table('benefit', function (Blueprint $table) {
            $table->integer('times_triggered')->nullable()
                ->comment('Cuántas veces puede dispararse este beneficio. Null = sin límite');
        });
    }
}
