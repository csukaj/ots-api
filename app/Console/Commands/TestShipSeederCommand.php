<?php
namespace App\Console\Commands;

use App\Availability;
use App\Console\Commands\TestModelSeeder\TestModelSeeder;
use App\Device;
use App\Facades\Config;
use App\Manipulators\AvailabilitySetter;
use App\Manipulators\OrganizationSetter;
use App\Organization;
use App\OrganizationDescription;
use App\Ship;
use App\ShipCompany;
use DirectoryIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Stylersmedia\Manipulators\GallerySetter;

/**
 * Seeds parent organization test data to specified database
 */
class TestShipSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testshipseeder {--database=local}';

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

        $directory = 'docs/ships/';
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
            $data = json_decode(file_get_contents($fileinfo->getPathname()), true);
            $this->comment("{$data['name']['en']} (#{$data['id']})");
            $this->setShip($data);
        }
        $this->correctShipGroupAvailabilities();

        DB::select("SELECT nextval('organizations_id_seq')");

        $this->info("Seeded `{$directory}` into database `{$this->database}`.");
    }

    /**
     * Save a new hotel chain to database and set associated data
     *
     * @param array $data
     * @return Ship
     */
    private function setShip(array $data): Ship
    {
        $ship = (new OrganizationSetter([
            'id' => $data['id'],
            'name' => $data['name'],
            'type' => 'ship',
            'parent' => $data['parent'],
            'is_active' => true
            ]))->set(true);

        (new TestModelPropertySeeder())->seed($ship, $data);

        TestModelSeeder::setModelDescriptions(OrganizationDescription::class, $ship->id, 'organization_id', 'organization_descriptions', $data['descriptions']);

        // create default gallery for organization
        (new GallerySetter([
        'galleryable_id' => $ship->id,
        'galleryable_type' => Organization::class,
        'role_taxonomy_id' => Config::getOrFail('taxonomies.gallery_roles.frontend_gallery')
        ]))->set();

        return $ship;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    protected function correctShipGroupAvailabilities(){

        ///TODO: dirty hack. make this code reusable when we have more than 1 test ship group....
        $file = 'docs/ship_groups/01 Ship Group A.json';
        $content = json_decode(file_get_contents($file),true);

        foreach ($content['devices']['cabin'] as $device){
            $deviceId = Device
                ::select('devices.id')
                ->join('taxonomies', 'devices.name_taxonomy_id', '=', 'taxonomies.id')
                ->where('taxonomies.name', $device['name']['en'])
                ->where('deviceable_id',1)
                ->first()->id;
            $availabilities = $device['availabilities'];
            Availability::forAvailable(Device::class,$deviceId)->delete();
            foreach (array_reverse($availabilities) as $availabilityData){
                $availability = new Availability([
                    'available_type' => Device::class,
                    'available_id' => $deviceId,
                    'from_time' => Availability::getSeparationTime($availabilityData['from_time']),
                    'to_time' => Availability::getSeparationTime($availabilityData['to_time']),
                    'amount' => $availabilityData['amount']
                ]);
                $availability->saveOrFail();
            }
        }
    }
}
