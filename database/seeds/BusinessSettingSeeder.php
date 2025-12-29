<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('business_settings')) {
            if (DB::table('business_settings')->where('id', 1)->doesntExist()) {
                DB::table('business_settings')->insert([
                    'id' => 1,
                    'type' => 'system_default_currency',
                    'value' => 1,
                ]);
            }
        }
    }
}
