<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('withdraw_amount');
            $table->integer('payment_type')->comment('1=>Offline, 2 => Paypal, 3=>Add User Wallet');
            $table->boolean('status')->default(0)->comment('0 => Pending, 1 => Done , 2=>Cancel');
            $table->date('request_date');
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->date('confirm_date')->nullable();
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
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
        Schema::dropIfExists('affiliate_withdraws');
    }
}
