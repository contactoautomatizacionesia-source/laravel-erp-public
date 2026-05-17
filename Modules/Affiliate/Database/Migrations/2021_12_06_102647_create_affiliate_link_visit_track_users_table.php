<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateLinkVisitTrackUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_link_visit_track_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_link_id');
            $table->string('ip');
            $table->string('agent');
            $table->date('date');
            $table->foreign('affiliate_link_id')->on('affiliate_links')->references('id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_link_visit_track_users');
    }
}
