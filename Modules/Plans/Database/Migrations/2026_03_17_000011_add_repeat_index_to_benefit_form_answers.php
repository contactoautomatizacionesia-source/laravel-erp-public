<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepeatIndexToBenefitFormAnswers extends Migration
{
    public function up(): void
    {
        Schema::table('benefit_form_answers', function (Blueprint $table) {
            $table->unsignedInteger('repeat_index')->nullable()->after('answer')
                ->comment('Índice de repetición para secciones repetibles; null si no aplica');
        });
    }

    public function down(): void
    {
        Schema::table('benefit_form_answers', function (Blueprint $table) {
            $table->dropColumn('repeat_index');
        });
    }
}
