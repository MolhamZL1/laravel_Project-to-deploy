<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmountWithdrawReq extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (Schema::hasColumn('withdraw_requests', 'amount')) {
                    $table->string('amount')->change();
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
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                // Reverting column type changes is complex
            });
        }
    }
}
