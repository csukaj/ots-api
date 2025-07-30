<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class DeviceTaxonomySeeder extends Seeder
{

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        Model::unguard();

        $this->saveTaxonomyWithChildren('taxonomies.device');
        $this->saveTaxonomyWithChildren('taxonomies.device_meta');
        $this->saveTaxonomyWithChildren('taxonomies.device_description');

        $parentTx = $this->saveTaxonomyPath('taxonomies.device_classification');
        $categoryTx = $this->saveTaxonomyPath("taxonomies.device_properties.category", $parentTx);

        $priority = 0;
        foreach (Config::getOrFail('taxonomies.device_properties.categories') as $data) {
            $data['priority'] = $priority++;
            $data['type'] = Config::getOrFail('stylerstaxonomy.type_classification');
            $tx = $this->saveTaxonomy($data['id'], $data['name'], $categoryTx, $data);

            if (!empty($data['items'])) {
                $itemPriority = 0;
                foreach ($data['items'] as $itemData) {
                    $itemData['priority'] = $itemPriority++;
                    $itemData['type'] = Config::getOrFail('stylerstaxonomy.type_classification');
                    $etx = $this->saveTaxonomy($itemData['id'], $itemData['name'], $tx, $itemData);
                    if (!empty($itemData['elements'])) {
                        foreach ($itemData['elements'] as $elementName => $elementId) {
                            $this->saveTaxonomy($elementId, $elementName, $etx);
                        }
                    }
                }
            }
            if (!empty($data['metas'])) {
                foreach ($data['metas'] as $metaData) {
                    $metaData['type'] = Config::getOrFail('stylerstaxonomy.type_meta');
                    $this->saveTaxonomy($metaData['id'], $metaData['name'], $tx, $metaData);
                }
            }
        }
    }
}
