<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SellerWalletWithdrawBal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('seller_wallets')) {
            Schema::table('seller_wallets', function (Blueprint $table) {
                if (Schema::hasColumn('seller_wallets', 'withdrawn')) {
                    $table->string('withdrawn')->change();
                }
                if (Schema::hasColumn('seller_wallets', 'balance')) {
                    $table->string('balance')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('seller_wallets')) {
            Schema::table('seller_wallets', function (Blueprint $table) {
                // Reverting column type changes is complex
            });
        }
    }
}
