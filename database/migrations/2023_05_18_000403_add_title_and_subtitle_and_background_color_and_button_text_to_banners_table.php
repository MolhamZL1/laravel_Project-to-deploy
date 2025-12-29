<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleAndSubtitleAndBackgroundColorAndButtonTextToBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('banners')) {

            if (Schema::hasTable('banners')) {


                Schema::table('banners', function (Blueprint $table) {
            $table->string('title')->after('resource_id')->nullable();
            $table->string('sub_title')->after('title')->nullable();
            $table->string('button_text')->after('sub_title')->nullable();
            $table->string('background_color')->after('button_text')->nullable();
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
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['title']);
            $table->dropColumn(['sub_title']);
            $table->dropColumn(['button_text']);
            $table->dropColumn(['background_color']);
        });
    }
}
