<?php

namespace Modules\Stylerscontact\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class StylerscontactDatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $this->call('Modules\Stylerscontact\Database\Seeders\ContactTableSeeder');
    }

}
