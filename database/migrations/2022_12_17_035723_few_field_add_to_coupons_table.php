<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FewFieldAddToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coupons')) {

            if (Schema::hasTable('coupons')) {


                Schema::table('coupons', function (Blueprint $table) {
            $table->string('added_by')->after('id')->default('admin');
            $table->string('coupon_bearer')->after('coupon_type')->default('inhouse');
            $table->bigInteger('seller_id')->after('coupon_bearer')->nullable()->comment('NULL=in-house, 0=all seller');
            $table->bigInteger('customer_id')->after('seller_id')->nullable()->comment('0 = all customer');
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
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['added_by']);
            $table->dropColumn(['coupon_bearer']);
            $table->dropColumn(['seller_id']);
            $table->dropColumn(['customer_id']);
        });
    }
}
