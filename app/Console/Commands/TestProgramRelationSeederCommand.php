<?php
namespace App\Console\Commands;

use App\Manipulators\RelativeTimeSetter;
use App\ProgramRelation;
use Illuminate\Console\Command;
use App\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Seed organization managers from test file to specified database
 */
class TestProgramRelationSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testprogramrelationseeder {--database=local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds a database from JSON source';

    /**
     * Database connection name used
     */
    protected $database;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = 'docs/program_relations.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        foreach (json_decode(file_get_contents($file), true) as $programRelationData) {
            $programRelation = new ProgramRelation();
            $programRelation->id = $programRelationData['id'];
            $programRelation->parent_id = $programRelationData['parent_id'];
            $programRelation->child_id = $programRelationData['child_id'];
            $programRelation->sequence = $programRelationData['sequence'];
            $programRelation->relative_time_id = (new RelativeTimeSetter($programRelationData['relative_time']))->set()->id;
            $programRelation->embarkation_type_taxonomy_id = isset($programRelationData['embarkation_type']) ? Taxonomy::getTaxonomy($programRelationData['embarkation_type'], Config::getOrFail('taxonomies.embarkation_type'))->id : null;
            $programRelation->embarkation_direction_taxonomy_id = isset($programRelationData['embarkation_direction']) ? Taxonomy::getTaxonomy($programRelationData['embarkation_direction'], Config::getOrFail('taxonomies.embarkation_direction'))->id : null;
            $programRelation->saveOrFail();
        }

        DB::select("SELECT nextval('program_relations_id_seq')");

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }
}
