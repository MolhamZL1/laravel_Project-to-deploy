<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdColOrderTran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('order_transactions')) {

            if (Schema::hasTable('order_transactions')) {


                Schema::table('order_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
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
        Schema::table('order_transaction', function (Blueprint $table) {
            //
        });
    }
}
