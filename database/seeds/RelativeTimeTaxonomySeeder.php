<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class RelativeTimeTaxonomySeeder extends Seeder
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

        $parentTx = $this->saveTaxonomyPath('taxonomies.relativetime_precision');
        foreach (Config::get('taxonomies.relativetime_precisions') as $name => $data) {
            $tx = $this->saveTaxonomy($data['id'], $name, $parentTx, $data);
        }

        $parentTx = $this->saveTaxonomyPath('taxonomies.relativetime_time_of_day');
        foreach (Config::get('taxonomies.relativetime_time_of_days') as $name => $data) {
            $tx = $this->saveTaxonomy($data['id'], $name, $parentTx, $data);
        }
    }
}
