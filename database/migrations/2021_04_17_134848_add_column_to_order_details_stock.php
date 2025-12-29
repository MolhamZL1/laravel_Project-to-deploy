<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToOrderDetailsStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('order_details', 'is_stock_decreased')) {
                    $table->boolean('is_stock_decreased')->default(1);
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
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (Schema::hasColumn('order_details', 'is_stock_decreased')) {
                    $table->dropColumn('is_stock_decreased');
                }
            });
        }
    }
}
