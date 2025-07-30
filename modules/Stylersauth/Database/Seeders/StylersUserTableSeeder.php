<?php

namespace Modules\Stylersauth\Database\Seeders;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StylersUserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        if (!User::withTrashed()->find(1)) {
            DB::table('users')->updateOrInsert([
                'id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ], [
                'name' => 'root',
                'email' => 'root@example.com',
                'password' => Hash::make('sdakfg8756HKSDGF'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('user_roles')->updateOrInsert([
                'user_id' => 1,
                'role_id' => Config::get('stylersauth.role_admin')
            ], [
                'user_id' => 1,
                'role_id' => Config::get('stylersauth.role_admin')
            ]);
        }

        if (!User::withTrashed()->find(2)) {
            DB::table('users')->updateOrInsert([
                'id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ], [
                'name' => 'test',
                'email' => 'test@example.com',
                'password' => Hash::make('sdakfg8756HKSDGF'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('user_roles')->updateOrInsert([
                'user_id' => 2,
                'role_id' => Config::get('stylersauth.role_user')
            ], [
                'user_id' => 2,
                'role_id' => Config::get('stylersauth.role_user')
            ]);
        }

        if (!User::withTrashed()->find(3)) {
            DB::table('users')->updateOrInsert([
                'id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ], [
                'name' => 'manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('sdakfg8756HKSDGF'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('user_roles')->updateOrInsert([
                'user_id' => 3,
                'role_id' => Config::get('stylersauth.role_manager')
            ], [
                'user_id' => 3,
                'role_id' => Config::get('stylersauth.role_manager')
            ]);
        }

        if (!User::withTrashed()->find(4)) {
            DB::table('users')->updateOrInsert([
                'id' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ], [
                'name' => 'advisor',
                'email' => 'advisor@example.com',
                'password' => Hash::make('sdakfg8756HKSDGF'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('user_roles')->updateOrInsert([
                'user_id' => 4,
                'role_id' => Config::get('stylersauth.role_advisor')
            ], [
                'user_id' => 4,
                'role_id' => Config::get('stylersauth.role_advisor')
            ]);
            DB::table('user_sites')->updateOrInsert([
                'user_id' => 4,
                'site' => 'localhost'
            ], [
                'user_id' => 4,
                'site' => 'localhost'
            ]);
        }
    }

}
