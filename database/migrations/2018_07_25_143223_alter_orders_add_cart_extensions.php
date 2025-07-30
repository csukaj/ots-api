<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Facades\Config;

class AlterOrdersAddCartExtensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('type_taxonomy_id');
            $table
                ->integer('order_type_taxonomy_id')
                ->after('id')
                ->default(Config::getOrFail('taxonomies.order_types.normal'))
                ->nullable()
                ->unsigned()
            ;
            $table
                ->string('first_name')
                ->nullable()
                ->change()
            ;
            $table
                ->string('last_name')
                ->nullable()
                ->change()
            ;
            $table
                ->string('company_name')
                ->after('last_name')
                ->nullable()
            ;
            $table
                ->string('tax_number')
                ->after('company_name')
                ->nullable()
            ;
            $table
                ->integer('billing_type_taxonomy_id')
                ->after('token_created_at')
                ->default(Config::getOrFail('taxonomies.billing_types.individual'))
                ->unsigned()
            ;
            $table->foreign('order_type_taxonomy_id')->references('id')->on('taxonomies');
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
            $table->dropForeign(['order_type_taxonomy_id']);
            $table->dropColumn('order_type_taxonomy_id');

            $table
                ->string('first_name')
                ->nullable(false)
                ->change()
            ;
            $table
                ->string('last_name')
                ->nullable(false)
                ->change()
            ;

            $table->dropColumn('company_name');

            $table->dropColumn('tax_number');

            $table->dropForeign(['billing_type_taxonomy_id']);
            $table->dropColumn('billing_type_taxonomy_id');
        });
    }
}
