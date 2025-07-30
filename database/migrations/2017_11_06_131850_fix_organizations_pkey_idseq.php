<?php

use Illuminate\Database\Migrations\Migration;

class FixOrganizationsPkeyIdseq extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("SELECT SETVAL('public.organizations_id_seq', COALESCE(MAX(id), 10000) ) FROM public.organizations");
        DB::statement("SELECT SETVAL('public.languages_id_seq', COALESCE(MAX(id), 1) ) FROM public.languages");
        DB::statement("SELECT SETVAL('public.meal_plans_id_seq', COALESCE(MAX(id), 1) ) FROM public.meal_plans");
        DB::statement("SELECT SETVAL('public.roles_id_seq', COALESCE(MAX(id), 1) ) FROM public.roles");
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
