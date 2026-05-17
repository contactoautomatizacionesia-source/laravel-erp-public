<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateReferralPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_referral_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_to')->comment('Who Received Money');
            $table->double('amount');
            $table->unsignedBigInteger('affiliate_link_id');
            $table->unsignedBigInteger('payment_from')->comment('Who Enrolled Course');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->date('date')->nullable();
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('affiliate_referral_payments');
    }
}
