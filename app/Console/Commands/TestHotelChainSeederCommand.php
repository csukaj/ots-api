<?php

namespace App\Console\Commands;

use App\Facades\Config;
use App\HotelChain;
use App\Traits\HardcodedIdSetterTrait;
use Illuminate\Console\Command;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Seeds parent organization test data to specified database
 */
class TestHotelChainSeederCommand extends Command
{
    use HardcodedIdSetterTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testhotelchainseeder {--database=local}';

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
        $file = 'docs/hotel_chains.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        $dataRows = json_decode(file_get_contents($file), true);
        foreach ($dataRows as $data) {
            $this->comment("{$data['name']['en']} (#{$data['id']})");
            $this->setHotelChain($data);
        }

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }

    /**
     * Save a new hotel chain to database and set associated data
     *
     * @param array $data
     * @return HotelChain
     */
    private function setHotelChain(array $data): HotelChain
    {
        $nameDescription = (new DescriptionSetter($data['name']))->set();

        $hotelChain = new HotelChain();
        $hotelChain->id = $data['id'];
        $hotelChain->fill([
            'name_description_id' => $nameDescription->id,
            'type_taxonomy_id' => Config::getOrFail('taxonomies.organization_types.hotel_chain.id'),
            'is_active' => true
        ]);
        $hotelChain->saveOrFail();
        $this->updateAutoIncrement($hotelChain);
        return $hotelChain;
    }
}
