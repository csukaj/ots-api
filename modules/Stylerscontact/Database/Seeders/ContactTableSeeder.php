<?php

namespace Modules\Stylerscontact\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class ContactTableSeeder extends Seeder {

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $parentTx = $this->saveTaxonomyWithChildren('taxonomies.contact_type');
    }

}
