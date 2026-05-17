<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiryDateToProductsTable extends Migration
{
    /**
     * Agrega la columna expiry_date a la tabla products.
     * Es nullable porque no todos los productos tienen fecha de vencimiento.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('model_number');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });
    }
}
