<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToPolymorphicRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('age_ranges', function (Blueprint $table) {
            $name = 'age_rangeable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('availabilities', function (Blueprint $table) {
            $name = 'available';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('contacts', function (Blueprint $table) {
            $name = 'contactable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('content_media', function (Blueprint $table) {
            $name = 'mediable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('date_ranges', function (Blueprint $table) {
            $name = 'date_rangeable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('devices', function (Blueprint $table) {
            $name = 'deviceable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('galleries', function (Blueprint $table) {
            $name = 'galleryable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('model_meal_plans', function (Blueprint $table) {
            $name = 'meal_planable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $name = 'order_itemable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
        Schema::table('products', function (Blueprint $table) {
            $name = 'productable';
            $table->index(["{$name}_id", "{$name}_type"], null);
        });
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
