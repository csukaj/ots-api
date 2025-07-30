<?php

use App\Facades\Config;
use App\Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        if(!Organization::find(401)) {
            $description_id = DB::table('descriptions')->insertGetId([
                'description' => '7° South Ltd',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::table('organizations')->updateOrInsert([
                'id' => 401,
                'updated_at' => Carbon::now()
            ], [
                'name_description_id' => $description_id,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.organization_types.supplier.id'),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);


            DB::table('organizations')
                ->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.accommodation.id'))
                ->whereNull('deleted_at')
                ->update(['supplier_id' => 401]);
        }

        if(!Organization::find(402)) {
            $description_id2 = DB::table('descriptions')->insertGetId([
                'description' => 'Silhouette Cruises',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::table('organizations')->updateOrInsert([
                'id' => 402,
                'updated_at' => Carbon::now()
            ], [
                'name_description_id' => $description_id2,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.organization_types.supplier.id'),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::table('organization_groups')
                ->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_group_types.ship_group.id'))
                ->whereNull('deleted_at')
                ->update(['supplier_id' => 402]);

            DB::table('cruises')
                ->whereNull('deleted_at')
                ->update(['supplier_id' => 402]);
        }
    }


}
