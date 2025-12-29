<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthTokenSeller extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (!Schema::hasColumn('sellers', 'auth_token')) {
                    $table->text('auth_token')->nullable();
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
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (Schema::hasColumn('sellers', 'auth_token')) {
                    $table->dropColumn(['auth_token']);
                }
            });
        }
    }
}
