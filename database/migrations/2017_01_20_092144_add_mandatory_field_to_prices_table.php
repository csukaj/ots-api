<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMandatoryFieldToPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->boolean('mandatory')->after('amount')->default(0);
        });
        Schema::table('prices', function (Blueprint $table) {
            $sql = '
                UPDATE prices
                SET mandatory = TRUE
                WHERE id IN (
                  WITH mandatories AS (
                    SELECT
                      prices.*,
                      products.productable_id,
                      ROW_NUMBER() OVER (PARTITION BY products.productable_id ORDER BY prices.id ASC) AS row_number
                    FROM prices
                    INNER JOIN products ON prices.product_id = products.id
                    WHERE NOT extra
                  )
                  SELECT id
                  FROM mandatories
                  WHERE row_number = 1
                )
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
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('mandatory');
        });
    }
}
