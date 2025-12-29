<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategorySubCategoryAndSubSubCategoryAddInProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'category_id')) {
                    $table->string('category_id')->after('category_ids')->nullable();
                }
                if (!Schema::hasColumn('products', 'sub_category_id')) {
                    $table->string('sub_category_id')->after('category_id')->nullable();
                }
                if (!Schema::hasColumn('products', 'sub_sub_category_id')) {
                    $table->string('sub_sub_category_id')->after('sub_category_id')->nullable();
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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'category_id')) {
                    $table->dropColumn(['category_id']);
                }
                if (Schema::hasColumn('products', 'sub_category_id')) {
                    $table->dropColumn(['sub_category_id']);
                }
                if (Schema::hasColumn('products', 'sub_sub_category_id')) {
                    $table->dropColumn(['sub_sub_category_id']);
                }
            });
        }
    }
}
