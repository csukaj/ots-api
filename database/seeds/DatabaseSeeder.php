<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call(SimpleTaxonomySeeder::class); //bulk seeder for simple taxonomies
        $this->call(DeviceTaxonomySeeder::class);
        $this->call(IslandTaxonomySeeder::class);
        $this->call(MealPlanSeeder::class);
        $this->call(PredefinedFilterTaxonomySeeder::class);
        $this->call(OrganizationTaxonomySeeder::class);
        $this->call(OrganizationPropertiesSeeder::class);
        $this->call(ProcedureSeeder::class);
        $this->call(ViewSeeder::class);
        $this->call(DiscountTypeTaxonomySeeder::class);
        $this->call(DiscountOfferTaxonomySeeder::class);
        $this->call(RelativeTimeTaxonomySeeder::class);
        $this->call(ProgramPropertiesSeeder::class);
        $this->call(CruisePropertiesSeeder::class);
        $this->call(PoiTypeTaxonomySeeder::class);
        $this->call(UserSettingsTaxonomySeeder::class);
        $this->call(SuppliersTableSeeder::class);
        $this->call(OrderStatusTaxonomySeeder::class);
        $this->call(MergedFreeNightsDefaultValueSeeder::class); //must be last (at least after merged free nights tx seed)
    }

}
