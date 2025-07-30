<?php

use App\Facades\Config;
use App\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

class DropParentOrganizationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        $parents = DB::table('parent_organizations')->get();
        foreach ($parents as $parent) {
            $orgNameDescription = (new DescriptionSetter(['en' => $parent->name]))->set();
            $oldData = [
                'name_description_id' => $orgNameDescription->id,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.organization_types.hotel_chain.id'),
                'is_active' => true
            ];
            $parentOrg = new Organization();
            $parentOrg->fill($oldData);
            $parentOrg->saveOrFail();
            if(!is_null($parent->deleted_at)){
                $parentOrg->deleted_at = $parent->deleted_at;
                $parentOrg->saveOrFail();
            }
            Organization::withTrashed()
                ->where('parent_id', $parent->id)
                ->update(['parent_id' => $parentOrg->id]);
        }
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('organizations');
        });
        Schema::drop('parent_organizations');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('parent_organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('name');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('parent_organizations');
        });

        $parents = Organization::withTrashed()
            ->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.hotel_chain.id'))
            ->get();
        foreach ($parents as $parent) {
            $id = DB::table('parent_organizations')->insertGetId(
                ['name' => $parent->name->description]
            );
            Organization::withTrashed()
                ->where('parent_id', $parent->id)
                ->update(['parent_id' => $id]);
            $parent->forceDelete();
        }
    }
}
