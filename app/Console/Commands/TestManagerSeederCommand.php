<?php

namespace App\Console\Commands;

use App\OrganizationManager;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

/**
 * Seed organization managers from test file to specified database
 */
class TestManagerSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testmanagerseeder {--database=local}';

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
    public function handle() {
        $file = 'docs/manager.json';
        $this->database = $this->option('database');
        Config::set('database.default', $this->database);
        
        foreach (json_decode(file_get_contents($file), true) as $managerData) {
            $user = User::where('email', $managerData['email'])->firstOrFail();
            foreach ($managerData['organizations'] as $organizationId) {
                $om = new OrganizationManager(['user_id' => $user->id, 'organization_id' => $organizationId]);
                $om->saveOrFail();
            }
        }
        
        $this->info("Seeded `{$file}` into database `{$this->database}`.");
    }
    
}
