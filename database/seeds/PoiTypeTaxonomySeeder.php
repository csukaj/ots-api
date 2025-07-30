<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class PoiTypeTaxonomySeeder extends Seeder
{

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        foreach (['poi_type', 'organization_group_poi_type'] as $parentType) {
            $parentTx = $this->saveTaxonomyPath("taxonomies.{$parentType}");

            foreach (Config::getOrFail("taxonomies.{$parentType}s") as $typeData) {
                $this->saveTaxonomy($typeData['id'], $typeData['name'], $parentTx);
            }
        }
    }

}
