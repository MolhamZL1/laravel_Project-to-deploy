<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxTypeToCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('carts')) {

            if (Schema::hasTable('carts')) {


                Schema::table('carts', function (Blueprint $table) {
            $table->string('tax_model', 20)->after('discount')->default('exclude');
        });


            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['tax_model']);
        });
    }
}
