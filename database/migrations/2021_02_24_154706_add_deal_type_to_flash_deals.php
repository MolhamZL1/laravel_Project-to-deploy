<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealTypeToFlashDeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('flash_deals')) {
            // Create the table if it doesn't exist
            Schema::create('flash_deals', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('status')->default(0);
                $table->boolean('featured')->default(0);
                $table->string('background_color', 255)->nullable();
                $table->string('text_color', 255)->nullable();
                $table->string('banner', 255)->nullable();
                $table->string('slug', 255)->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('deal_type')->nullable();
                $table->timestamps();
            });
        } else {
            // Table exists, just add the column if it doesn't exist
            Schema::table('flash_deals', function (Blueprint $table) {
                if (!Schema::hasColumn('flash_deals', 'deal_type')) {
                    $table->string('deal_type')->nullable();
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
        if (Schema::hasTable('flash_deals')) {
            Schema::table('flash_deals', function (Blueprint $table) {
                if (Schema::hasColumn('flash_deals', 'deal_type')) {
                    $table->dropColumn(['deal_type']);
                }
            });
        }
    }
}
