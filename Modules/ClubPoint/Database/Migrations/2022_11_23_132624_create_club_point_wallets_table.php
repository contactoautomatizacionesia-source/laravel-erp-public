<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateClubPointWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_point_wallets', function (Blueprint $table) {
            $table->id();
            $table->float('wallet_point',20,4)->nullable();
            $table->timestamps();
        });
        DB::statement("INSERT INTO `club_point_wallets` (`id`, `wallet_point`, `created_at`, `updated_at`) VALUES
        (1, 5,'2021-05-05 05:42:40', '2021-05-05 05:46:00')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('club_point_wallets');
    }
}
