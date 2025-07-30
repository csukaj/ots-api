<?php

namespace App\Console\Commands;

use App\Facades\Config;
use App\Organization;
use Illuminate\Console\Command;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Entities\Person;

/**
 * Seeds parent organization test data to specified database
 */
class TestPeopleSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testpeopleseeder {--database=local}';

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
        $file = 'docs/person.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        $dataRows = json_decode(file_get_contents($file), true);
        foreach ($dataRows as $data) {
            $this->comment("{$data['name']} -> {$data['personable']['type']} #{$data['personable']['id']}");
            $person = $this->setPerson($data);
            foreach ($data['contacts'] as $contactData) {
                $this->setContact($person, $contactData);
            }
        }

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }

    private function setPerson(array $data): Person
    {
        switch ($data['personable']['type']) {
            case 'supplier':
                $personable_type = Organization::class;
                break;
            default:
                throw new UserException('Unsupported contactable type!');
        }
        $person = (new Person())->fill([
            'personable_type' => $personable_type,
            'personable_id' => $data['personable']['id'],
            'name' => $data['name'],
        ]);
        $person->saveOrFail();
        return $person;
    }

    private function setContact(Person $person, array $data): Contact
    {
        $contact = new Contact();
        $contact->fill([
            'contactable_type' => Person::class,
            'contactable_id' => $person->id,
            'value' => $data['value'],
            'type_taxonomy_id' => Config::getOrFail('taxonomies.contact_types.' . $data['type']),
            'is_public' => !empty($data['is_public'])
        ]);
        $contact->saveOrFail();
        return $contact;
    }
}
