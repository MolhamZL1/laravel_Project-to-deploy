<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponDiscountBearerAndAdminCommissionToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('orders')) {

            if (Schema::hasTable('orders')) {


                Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_discount_bearer')->after('coupon_code')->default('inhouse');
            $table->decimal('admin_commission')->after('order_amount')->default(0);
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_discount_bearer']);
            $table->dropColumn(['admin_commission']);
        });
    }
}
