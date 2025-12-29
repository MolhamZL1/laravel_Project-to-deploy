<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColTypeAdminWallet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('admin_wallets')) {

            if (Schema::hasTable('admin_wallets')) {


                Schema::table('admin_wallets', function (Blueprint $table) {
            $table->float('balance')->change();
            $table->float('withdrawn')->change();
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
        Schema::table('admin_wallets', function (Blueprint $table) {
            //
        });
    }
}
