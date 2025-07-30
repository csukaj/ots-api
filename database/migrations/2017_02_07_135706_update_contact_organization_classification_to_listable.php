<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema;

class UpdateContactOrganizationClassificationToListable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_classifications', function(Blueprint $table) {
            $sql = '
                UPDATE organization_classifications
                SET is_listable = TRUE
                WHERE classification_taxonomy_id = 204
            ';
            DB::connection()->getPdo()->exec($sql);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_classifications', function(Blueprint $table) {
            $sql = '
                UPDATE organization_classifications
                SET is_listable = FALSE
                WHERE classification_taxonomy_id = 204
            ';
            DB::connection()->getPdo()->exec($sql);
        });
    }
}
