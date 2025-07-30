<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteAndLastLoginToUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->softDeletes();
            $table->timestamp('last_login')->nullable();
        });
        Illuminate\Support\Facades\DB::statement('ALTER SEQUENCE users_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropSoftDeletes();
            $table->dropColumn('last_login');
        });
    }

}
