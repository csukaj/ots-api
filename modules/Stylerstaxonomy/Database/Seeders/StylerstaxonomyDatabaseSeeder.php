<?php

namespace Modules\Stylerstaxonomy\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * @codeCoverageIgnore
 */
class StylerstaxonomyDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('Modules\Stylerstaxonomy\Database\Seeders\LanguageTableSeeder');
    }

}
