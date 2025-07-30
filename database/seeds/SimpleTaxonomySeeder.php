<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class SimpleTaxonomySeeder extends Seeder
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

        $taxonomies = [
            'taxonomies.age_range',
            'taxonomies.charge',
            'taxonomies.content_status',
            'taxonomies.cart_status',
            'taxonomies.media_role',
            'taxonomies.cruise_description',
            'taxonomies.date_range_type',
            'taxonomies.margin_type',
            'taxonomies.name',
            'taxonomies.pricing_logic',
            'taxonomies.product_type',
            'taxonomies.schedule_frequency',
            'taxonomies.embarkation_type',
            'taxonomies.embarkation_direction',
            'taxonomies.program_description',
            'taxonomies.program_type',
            'taxonomies.content_category',
            'taxonomies.price_modifier_type',
            'taxonomies.minimum_nights_checking_level',
            'taxonomies.order_status',
            'taxonomies.email_template',
            'taxonomies.order_type',
            'taxonomies.billing_type'
        ];
        foreach ($taxonomies as $txPath) {
            $tx = $this->saveTaxonomyWithChildren($txPath);
        }
    }
}
