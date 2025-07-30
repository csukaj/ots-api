<?php

namespace App\Console\Commands;

use App\Console\Commands\TestModelSeeder\TestCruiseSeeder;
use App\Facades\Config;
use DirectoryIterator;
use Illuminate\Console\Command;

/**
 * Seeds cruises to a database from a directory of JSON source files
 */
class TestCruiseSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testcruiseseeder {--database=local}';

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
        $directory = 'docs/cruises/';
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
            $crData = json_decode(file_get_contents($fileinfo->getPathname()), true);
            $this->comment("{$crData['name']['en']} (#{$crData['id']})");
            (new TestCruiseSeeder())->seed($crData);
        }

        $this->info("Seeded directory `{$directory}` into database `{$this->database}`.");
    }

}
