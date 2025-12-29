<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('help_topics')) {

            if (Schema::hasTable('help_topics')) {


                Schema::table('help_topics', function (Blueprint $table) {
            $table->string('type')->default('default')->after('id');
        });


            }

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('help_topics', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
