<?php

namespace App\Console\Commands;

use App\Email;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Seeds sample 'email' to  a database from JSON source
 */
class TestEmailSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testemailseeder {--database=local}';

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
        $file = 'docs/email.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        foreach (json_decode(file_get_contents($file), true) as $emailData) {
            if(Email::find($emailData['id'])){
                continue;
            }
            $email = new Email();
            $email->type_taxonomy_id = Taxonomy::getTaxonomy($emailData['type'],
                Config::get('taxonomies.email_template'))->id;
            $email->subject_description_id = $this->setDescriptionObj($emailData['subject'],
                $email->subject_description_id)->id;
            $email->content_description_id = $this->setDescriptionObj($emailData['content'],
                $email->content_description_id)->id;

            $email->saveOrFail();
        }

        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }

    /**
     * Creates a Description object
     *
     * @param array $translations
     * @return Description
     */
    private function setDescriptionObj($translations, $id)
    {
        return (new DescriptionSetter($translations, $id))->set();
    }
}
