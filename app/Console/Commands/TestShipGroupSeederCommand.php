<?php
namespace App\Console\Commands;

use App\Console\Commands\TestModelSeeder\TestOrganizationSeeder;
use App\Console\Commands\TestModelSeeder\TestProductSeeder;
use App\Facades\Config;
use App\Manipulators\OrganizationGroupPoiSetter;
use App\ShipGroup;
use DirectoryIterator;
use Illuminate\Console\Command;

/**
 * Seeds ship groups to a database from a directory of JSON source files
 */
class TestShipGroupSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testshipgroupseeder {--database=local}';

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
        $directory = 'docs/ship_groups/';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        $directoryIterator = new DirectoryIterator($directory);
        $allFilesInfo = [];

        foreach ($directoryIterator as $fileInfo) {
            if ((!$fileInfo->isDot())) {
                $allFilesInfo[] = clone $fileInfo;
            }
        }

        usort($allFilesInfo, function ($a, $b) {
            return strcmp($a->getFilename(), $b->getFilename());
        });

        foreach ($allFilesInfo as $fileinfo) {
            $orgData = \json_decode(\file_get_contents($fileinfo->getPathname()), true);
            $this->comment("{$orgData['name']['en']} (#{$orgData['id']})");

            list($model, $dateRangeRelativeIds) = (new TestOrganizationSeeder(ShipGroup::class))->seed($orgData);
            
            $productSeederParams = [
                'modelType' => ShipGroup::class,
                'modelId' => $model->id,
                'productableType' => ShipGroup::class,
                'productableId' => $model->id,
            ];
            (new TestProductSeeder())->seed($productSeederParams, $dateRangeRelativeIds, $orgData);
            
            foreach ($orgData['pois'] as $organizationGroupPoiData) {
                $organizationGroupPoiData['organization_group_id'] = $orgData['id'];
                (new OrganizationGroupPoiSetter($organizationGroupPoiData))->set();
            }
        }

        $this->info("Seeded directory `{$directory}` into database `{$this->database}`.");
    }
}
