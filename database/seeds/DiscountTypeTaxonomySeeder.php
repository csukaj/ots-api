<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class DiscountTypeTaxonomySeeder extends Seeder {

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $parentTx = $this->saveTaxonomyPath('taxonomies.price_modifier_application_level');

        foreach (Config::get('taxonomies.price_modifier_application_levels') as $levelName => $levelProperties) {
            $levelTx = $this->saveTaxonomy($levelProperties['id'], $levelName, $parentTx);

            foreach ($levelProperties['price_modifier_condition_types'] as $name => $properties) {
                $tx = $this->saveTaxonomy($properties['id'], $name, $levelTx);

                $propPriority = 0;

                $classificationTx = $this->saveTaxonomy($properties['classification'], 'classification', $tx);

                foreach ($properties['classifications'] as $elementName => $elementConfig) {
                    if (!is_array($elementConfig)) {
                        $elementConfig = ['id' => $elementConfig];
                    }
                    $elementConfig['priority'] = $propPriority++;
                    $this->saveTaxonomy($elementConfig['id'], $elementName, $classificationTx, $elementConfig);
                }

                $metaTx = $this->saveTaxonomy($properties['meta'], 'meta', $tx);

                foreach ($properties['metas'] as $elementName => $elementConfig) {
                    if (!is_array($elementConfig)) {
                        $elementConfig = ['id' => $elementConfig];
                    }
                    $elementConfig['priority'] = $propPriority++;
                    $this->saveTaxonomy($elementConfig['id'], $elementName, $metaTx, $elementConfig);
                }
            }
        }
    }

}
