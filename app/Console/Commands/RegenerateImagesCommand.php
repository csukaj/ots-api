<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Manipulators\FileSetter;

/**
 * Regenerate all uploaded images in all size (see config). Doesn't touch original.
 */
class RegenerateImagesCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:regenerateimages {--database=local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreates image files for uploaded media';

    /**
     * Database connection name used
     */
    protected $database;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $this->database = $this->option('database');
        Config::set('database.default', $this->database);

        foreach (File::get() as $file) {
            if ($file->isSupportedImage()) {
                FileSetter::saveScaledImages($file);
            }
        }

        $this->info("Image files updated.");
    }

}
