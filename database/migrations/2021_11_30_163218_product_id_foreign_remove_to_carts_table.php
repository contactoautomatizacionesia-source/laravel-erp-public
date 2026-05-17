<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductIdForeignRemoveToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try{
            if($this->foreignKeyExists('carts','product_id'))
            {
                DB::statement("ALTER TABLE `carts` DROP FOREIGN KEY `carts_product_id_foreign`;");
                DB::statement("ALTER TABLE `carts` DROP INDEX `carts_product_id_foreign`;");
            }

        }catch(Exception $e){
            Log::info($e->getMessage());
        }

    }

    protected function foreignKeyExists(string $tableName, string $columnName)
    {
    $foreignKeysDefinitions = Schema::getForeignKeys($tableName);
    foreach($foreignKeysDefinitions as $foreignKeyDefinition) {
        if($foreignKeyDefinition['name'] === $tableName . '_' . $columnName . '_foreign') {
            return true;
        }
    }
    return false;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {

        });
    }
}
