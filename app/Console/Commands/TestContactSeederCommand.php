<?php

namespace App\Console\Commands;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\HotelChain;
use App\Organization;
use Illuminate\Console\Command;
use Modules\Stylerscontact\Entities\Person;
use Modules\Stylerscontact\Entities\Contact;

/**
 * Seeds parent organization test data to specified database
 */
class TestContactSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testcontactseeder {--database=local}';

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
        $file = 'docs/contact.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        $dataRows = json_decode(file_get_contents($file), true);
        foreach ($dataRows as $data) {
            $this->comment("{$data['type']} (#{$data['value']})");
            $this->setContact($data);
        }

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }

    /**
     * Save a new hotel chain to database and set associated data
     *
     * @param array $data
     * @return HotelChain
     */
    private function setContact(array $data): Contact
    {
        switch ($data['contactable']['type']) {
            case 'supplier':
                $contactable_type = Organization::class;
                break;
            case 'person':
                $contactable_type = Person::class;
                break;
            default:
                throw new UserException('Unsupported contactable type!');
        }
        $contact = new Contact();
        $contact->fill([
            'contactable_type' => $contactable_type,
            'contactable_id' => $data['contactable']['id'],
            'value' => $data['value'],
            'type_taxonomy_id' => Config::getOrFail('taxonomies.contact_types.' . $data['type']),
            'is_public' => !empty($data['is_public'])
        ]);
        $contact->saveOrFail();
        return $contact;
    }
}
