<?php

use App\OrganizationGroup;
use App\ShipGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeOrganizationGroupToShipGroupInPolymorphicRelations extends Migration
{
    private $tables = [
        'age_ranges' => 'age_rangeable_type',
        'availabilities' => 'available_type',
        'date_ranges' => 'date_rangeable_type',
        'devices' => 'deviceable_type',
        'galleries' => 'galleryable_type',
        'model_meal_plans' => 'meal_planable_type',
        'order_items' => 'order_itemable_type',
        'organizations' => 'parentable_type',
        'products' => 'productable_type'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table => $field) {
            DB::statement("UPDATE $table SET $field = '" . ShipGroup::class . "' WHERE $field = '" . OrganizationGroup::class . "'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table => $field) {
            DB::statement("UPDATE $table SET $field = '" . OrganizationGroup::class . "' WHERE $field = '" . ShipGroup::class . "'");
        }
    }
}
