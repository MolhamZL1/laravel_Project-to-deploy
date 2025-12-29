<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExRateUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table) {
                if (Schema::hasColumn('currencies', 'exchange_rate')) {
                    $table->string('exchange_rate')->change();
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
        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table) {
                // Reverting column type changes is complex, leaving empty
            });
        }
    }
}
