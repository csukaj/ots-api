<?php

use App\Facades\Config;
use App\MealPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class MealPlanSeeder extends Seeder {
    
    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $parentTx = $this->saveTaxonomyPath('taxonomies.meal_plan');

        $i = 0;
        foreach (Config::get('taxonomies.meal_plans') as $name => $properties) {
            $tx = $this->saveTaxonomy($properties['id'], $name, $parentTx, ['priority' => $i]);

            try {
                $mealPlan = MealPlan::findByName($name);
            } catch (Exception $e) {
                $mealPlan = new MealPlan();
                $mealPlan->id = $properties['meal_plan_id'];
                $mealPlan->name_taxonomy_id = $properties['id'];
            }
            $mealPlan->service_bitmap = $properties['service_bitmap'];
            $mealPlan->save();

            $i++;
        }
    }

}
