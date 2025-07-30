<?php

use App\District;
use App\Facades\Config;
use App\Island;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class IslandTaxonomySeeder extends Seeder {
    
    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $parentTx = $this->saveTaxonomyPath('taxonomies.island');

        foreach (Config::get('taxonomies.islands') as $name => $data) {
            $tx = $this->saveTaxonomy($data['id'], $name, $parentTx, $data);
            
            try {
                $island = Island::findByName($name);
            } catch (Exception $e) {
                $island = new Island();
                $island->name_taxonomy_id = $tx->id;
                $island->save();
            }
            
            foreach ($data['districts'] as $districtName => $districtTxId) {
                $districtTx = $this->saveTaxonomy($districtTxId, $districtName, $tx);
               
                try {
                    $district = District::findByName($districtName, $island);
                } catch (Exception $e) {
                    $district = new District();
                    $district->name_taxonomy_id = $districtTx->id;
                    $district->island_id = $island->id;
                    $district->save();
                }
            }
        }
    }

}
