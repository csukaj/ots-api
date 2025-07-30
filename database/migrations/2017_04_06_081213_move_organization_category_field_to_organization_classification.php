<?php

use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MoveOrganizationCategoryFieldToOrganizationClassification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $organizations = Organization::all();
        foreach($organizations as $organization){
            $orgCl = new OrganizationClassification();
            $orgCl->organization_id = $organization->id;
            $orgCl->parent_classification_id = Config::getOrFail('taxonomies.organization_properties.categories.general.id');
            $orgCl->classification_taxonomy_id = Config::getOrFail('taxonomies.organization_properties.categories.general.items.accommodation_category.id');
            $orgCl->value_taxonomy_id = $organization->category_taxonomy_id;
            $orgCl->saveOrFail();
        }
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['category_taxonomy_id']);
            $table->dropColumn('category_taxonomy_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function(Blueprint $table) {
            $table->integer('category_taxonomy_id')->unsigned();
            $table->foreign('category_taxonomy_id')->references('id')->on('taxonomies');
        });
    }
}
