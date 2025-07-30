<?php

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class OrganizationTaxonomySeeder extends Seeder
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

        foreach (['organization_type', 'organization_group_type'] as $type) {
            $orgTypeTx = $this->saveTaxonomyPath("taxonomies.{$type}");

            foreach (Config::getOrFail("taxonomies.{$type}s") as $name => $data) {
                $txName = isset($data['name']) ? $data['name'] : $name;
                $tx = $this->saveTaxonomy($data['id'], $txName, $orgTypeTx, $data);

                if (!empty($data['categories'])) {
                    foreach ($data['categories'] as $catName => $catId) {
                        $this->saveTaxonomy($catId, $catName, $tx);
                    }
                }
            }
        }

        $this->saveTaxonomyWithChildren('taxonomies.organization_description');
        $this->saveTaxonomyWithChildren('taxonomies.organization_group_description');

        $this->saveTaxonomyWithChildren('taxonomies.file_type');
        $this->saveTaxonomyWithChildren('taxonomies.gallery_role');
    }

}
