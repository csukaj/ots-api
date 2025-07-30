<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class UserSettingsTaxonomySeeder extends Seeder
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

        $parentTx = $this->saveTaxonomyPath('taxonomies.user_setting');

        $priority = 0;
        foreach (Config::getOrFail('taxonomies.user_settings') as $data) {
            $data['priority'] = $priority++;
            $tx = $this->saveTaxonomy($data['id'], $data['name'], $parentTx, $data);
            if (!empty($data['items'])) {
                $itemPriority = 0;
                foreach ($data['items'] as $itemName => $itemId) {
                    $itemData['priority'] = $itemPriority++;
                    $this->saveTaxonomy($itemId, $itemName, $tx, $itemData);
                }
            }
        }
    }
}
