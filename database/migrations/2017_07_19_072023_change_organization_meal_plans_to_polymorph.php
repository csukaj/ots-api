<?php

use App\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrganizationMealPlansToPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_elements', function (Blueprint $table) {
            $table->dropForeign(['organization_meal_plan_id']);
        });
       Schema::table('organization_meal_plans', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'meal_planable_id');
            $table->string('meal_planable_type', 255)->default(Organization::class);
        });
        Schema::table('organization_meal_plans', function (Blueprint $table) {
            $table->string('meal_planable_type', 255)->default(null)->change();
        });
        
        Schema::rename('organization_meal_plans', 'model_meal_plans');
        
        Schema::table('price_elements', function (Blueprint $table) {
            $table->renameColumn('organization_meal_plan_id', 'model_meal_plan_id');
            $table->foreign('model_meal_plan_id')->references('id')->on('model_meal_plans');
        });
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
