<?php

namespace Modules\Stylersauth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CreateBaseRolesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        // $this->call("OthersTableSeeder");
        DB::table('roles')->updateOrInsert([
            'id' => Config::get('stylersauth.role_admin')
        ], [
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => 'System Admin'
        ]);
        DB::table('roles')->updateOrInsert([
            'id' => Config::get('stylersauth.role_manager')
        ], [
            'name' => 'manager',
            'display_name' => 'Product Owner/Supplier',
            'description' => 'Product Owner/Supplier'
        ]);
        DB::table('roles')->updateOrInsert([
            'id' => Config::get('stylersauth.role_user')
        ], [
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'User'
        ]);
        DB::table('roles')->updateOrInsert([
            'id' => Config::get('stylersauth.role_advisor')
        ], [
            'name' => 'advisor',
            'display_name' => 'Advisor',
            'description' => 'Advisor'
        ]);
    }

}
