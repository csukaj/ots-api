<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class ProgramPropertiesSeeder extends Seeder
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

        $parentMtTx = $this->saveTaxonomyPath('taxonomies.program_meta');
        $parentClTx = $this->saveTaxonomyPath('taxonomies.program_classification');
        $categoryTx = $this->saveTaxonomyPath('taxonomies.program_properties.category', $parentClTx);

        foreach (Config::get('taxonomies.program_properties.categories') as $classificationData) {
            $properties = [
                'type' => Config::getOrFail('stylerstaxonomy.type_classification'),
                'translations' => $classificationData['translations']
            ];
            $tx = $this->saveTaxonomy($classificationData['id'], $classificationData['name'], $categoryTx, $properties);

            if (empty($classificationData['items']) && empty($classificationData['metas'])) {
                continue;
            }
            if (!empty($classificationData['items'])) {
                foreach ($classificationData['items'] as $classificationItemData) {
                    $this->setItem($tx, $classificationItemData);
                }
            }

            if (!empty($classificationData['metas'])) {
                $count = 0;
                foreach ($classificationData['metas'] as $data) {
                    $mProperties = [
                        'type' => Config::getOrFail('stylerstaxonomy.type_meta'),
                        'priority' => $count++
                    ];
                    $mTx = $this->saveTaxonomy($data['id'], $data['name'], $tx, $mProperties);
                }
            }
        }
    }

    private function setItem($tx, $classificationItemData)
    {
        $iProperties = [
            'type' => Config::getOrFail('stylerstaxonomy.type_classification'),
            'is_searchable' => !empty($classificationItemData['is_searchable'])
        ];
        if (isset($classificationItemData['priority'])) {
            $iProperties['priority'] = $classificationItemData['priority'];
        }
        if (isset($classificationItemData['is_required'])) {
            $iProperties['is_required'] = $classificationItemData['is_required'];
        }
        $txi = $this->saveTaxonomy($classificationItemData['id'], $classificationItemData['name'], $tx, $iProperties);

        if (!empty($classificationItemData['elements'])) {
            $priority = 0;
            foreach ($classificationItemData['elements'] as $elementName => $elementId) {
                $eProperties = ['priority' => $priority++];
                $this->saveTaxonomy($elementId, $elementName, $txi, $eProperties);
            }
        }
    }
}
