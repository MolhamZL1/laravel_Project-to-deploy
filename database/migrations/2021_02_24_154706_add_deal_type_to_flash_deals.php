<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealTypeToFlashDeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    if (Schema::hasTable('flash_deals')) {
        Schema::table('flash_deals', function (Blueprint $table) {
            if (!Schema::hasColumn('flash_deals', 'deal_type')) {
                $table->string('deal_type')->nullable();
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
        Schema::table('flash_deals', function (Blueprint $table) {
            $table->dropColumn(['deal_type']);
        });
    }
}
