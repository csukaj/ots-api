<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class OrderStatusTaxonomySeeder extends Seeder
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

        $parentTx = $this->saveTaxonomyPath('taxonomies.order_status');

        $priority = 0;
        foreach (Config::getOrFail('taxonomies.order_statuses') as $data) {
            $data['priority'] = $priority++;
            $tx = $this->saveTaxonomy($data['id'], $data['name'], $parentTx, $data);
        }
    }
}
