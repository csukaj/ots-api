<?php

use App\PriceElement;
use Illuminate\Database\Migrations\Migration;

class DeleteOrphranedPriceElements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $priceElementIds = DB::table('price_elements')
            ->select('price_elements.id')
            ->join('model_meal_plans', 'price_elements.model_meal_plan_id', '=', 'model_meal_plans.id')
            ->whereNull('price_elements.deleted_at')
            ->whereNotNull('model_meal_plans.deleted_at')
            ->get()
            ->pluck('id')
            ->toArray();

        PriceElement::whereIn('id', $priceElementIds)->delete();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
