<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVacationStartAndVacationEndAndVacationNotColumnToShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shops')) {

            if (Schema::hasTable('shops')) {


                Schema::table('shops', function (Blueprint $table) {
            $table->date('vacation_start_date')->after('image')->nullable();
            $table->date('vacation_end_date')->after('vacation_start_date')->nullable();
            $table->string('vacation_note', 255)->after('vacation_end_date')->nullable();
            $table->tinyInteger('vacation_status')->after('vacation_note')->default(0);
            $table->tinyInteger('temporary_close')->after('vacation_status')->default(0);
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
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['vacation_start_date']);
            $table->dropColumn(['vacation_end_date']);
            $table->dropColumn(['vacation_note']);
            $table->dropColumn(['vacation_status']);
            $table->dropColumn(['temporary_close']);
        });
    }
}
