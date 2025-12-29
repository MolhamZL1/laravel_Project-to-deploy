<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('oauth_clients', 'provider')) {
            if (Schema::hasTable('oauth_clients')) {

                if (Schema::hasTable('oauth_clients')) {


                    Schema::table('oauth_clients', function (Blueprint $table) {
                $table->string('provider')->nullable();
            });


                }

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
        Schema::table('oauth_clients', function (Blueprint $table) {
            //
        });
    }
}
