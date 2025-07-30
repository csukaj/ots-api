<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Facades\Config;

class AlterCartsAddExtensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('name');
            $table
                ->string('first_name')
                ->nullable()
            ;
            $table
                ->string('last_name')
                ->nullable()
            ;
            $table
                ->string('company_name')
                ->nullable()
            ;
            $table
                ->string('tax_number')
                ->nullable()
                ->change()
            ;
            $table
                ->integer('billing_type_taxonomy_id')
                ->unsigned()
                ->default(Config::getOrFail('taxonomies.billing_types.individual'))
            ;
            $table->foreign('billing_type_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('company_name');

            $table
                ->string('tax_number')
                ->nullable(false)
                ->change()
            ;

            $table->dropForeign(['billing_type_taxonomy_id']);
            $table->dropColumn('billing_type_taxonomy_id');
        });
    }
}
