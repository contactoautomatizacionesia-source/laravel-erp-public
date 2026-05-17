<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('blocked_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cycle_id');
            $table->date('blocked_until')->comment('Dates before this value are locked for modification');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_periods');
    }
}
