<?php

namespace Modules\Stylersauth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class StylersauthDatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $this->call("Modules\Stylersauth\Database\Seeders\CreateBaseRolesTableSeeder");
        $this->call("Modules\Stylersauth\Database\Seeders\StylersUserTableSeeder");
    }

}
