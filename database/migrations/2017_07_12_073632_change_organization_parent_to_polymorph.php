<?php

use App\HotelChain;
use App\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrganizationParentToPolymorph extends Migration
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
            $table->renameColumn('parent_id', 'parentable_id');
            $table->string('parentable_type', 255)->nullable()->default(null);
        });
        Organization::withTrashed()->whereNotNull('parentable_id')->update(['parentable_type'=> HotelChain::class]);
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
