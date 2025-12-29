<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColTypeTranslation1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('translations')) {

            if (Schema::hasTable('translations')) {


                Schema::table('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
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
        Schema::table('translations', function (Blueprint $table) {
            //
        });
    }   
}
