<?php

use Illuminate\Database\Migrations\Migration;

class DeleteAllDiscountBasedPriceElement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            DELETE FROM price_elements
            WHERE price_elements.price_id IN (
                SELECT prices.id
                FROM prices
                INNER JOIN products ON prices.product_id = products.id
                WHERE products.type_taxonomy_id != 60 AND price_elements.price_id = prices.id
            )
        ');
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
