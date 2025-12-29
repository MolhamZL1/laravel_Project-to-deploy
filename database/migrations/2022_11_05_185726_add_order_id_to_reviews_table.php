<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdToReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('reviews')) {

            if (Schema::hasTable('reviews')) {


                Schema::table('reviews', function (Blueprint $table) {
            $table->bigInteger('order_id')->after('delivery_man_id')->nullable();
            $table->boolean('is_saved')->after('status')->default(0);
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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['order_id']);
            $table->dropColumn(['is_saved']);
        });
    }
}
