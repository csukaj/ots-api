<?php

namespace App\Console\Commands;

use App\Console\Commands\TestModelSeeder\TestModelSeeder;
use App\Facades\Config;
use App\Manipulators\OrganizationSetter;
use App\OrganizationDescription;
use App\ShipCompany;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seeds parent organization test data to specified database
 */
class TestShipCompanySeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testshipcompanyseeder {--database=local}';

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
        $file = 'docs/ship_companies.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        $dataRows = \json_decode(\file_get_contents($file), true);
        foreach ($dataRows as $data) {
            $this->comment("{$data['name']['en']} (#{$data['id']})");
            $this->setShipCompany($data);
        }

        DB::select("SELECT nextval('organizations_id_seq')");

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }

    /**
     * Save a new hotel chain to database and set associated data
     *
     * @param array $data
     * @return ShipCompany
     */
    private function setShipCompany(array $data): ShipCompany
    {
        $shipCompany = (new OrganizationSetter([
            'id' => $data['id'],
            'name' => $data['name'],
            'type' => 'ship_company',
            'is_active' => true
        ]))->set(true);

        (new TestModelPropertySeeder())->seed($shipCompany, $data);

        TestModelSeeder::setModelDescriptions(OrganizationDescription::class, $shipCompany->id, 'organization_id', 'organization_descriptions', $data['descriptions']);

        return $shipCompany;
    }
}
