<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class PredefinedFilterTaxonomySeeder extends Seeder
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

        $parentTx = $this->saveTaxonomyPath('taxonomies.predefined_filter');

        foreach (Config::getOrFail('taxonomies.predefined_filters') as $filterData) {
            $filterTx = $this->saveTaxonomy($filterData['id'], $filterData['name'], $parentTx, $filterData);
            foreach ($filterData['elements'] as $option) {
                $filterTx = $this->saveTaxonomy($option['id'], $option['name'], $filterTx, $option);
            }
        }

    }

}