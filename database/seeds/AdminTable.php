<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('admins')) {
            if (DB::table('admins')->where('id', 1)->doesntExist()) {
                DB::table('admins')->insert([
                    'id' => 1,
                    'name' => 'Master Admin',
                    'phone' => '01759412381',
                    'email' => 'admin@admin.com',
                    'admin_role_id' => 1,
                    'image' => 'def.png',
                    'password' => bcrypt(12345678),
                    'remember_token' => Str::random(10),
                ]);
            }
        }
    }
}
