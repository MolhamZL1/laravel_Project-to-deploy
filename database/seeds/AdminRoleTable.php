<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminRoleTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('admin_roles')) {
            if (DB::table('admin_roles')->where('id', 1)->doesntExist()) {
                DB::table('admin_roles')->insert([
                    'id' => 1,
                    'name' => 'Master Admin',
                ]);
            }

            if (DB::table('admin_roles')->where('id', 2)->doesntExist()) {
                DB::table('admin_roles')->insert([
                    'id' => 2,
                    'name' => 'Employee',
                ]);
            }
        }
    }
}
