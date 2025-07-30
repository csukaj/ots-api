<?php
namespace App\Console\Commands;

use App\AgeRange;
use App\Console\Commands\TestModelSeeder\TestModelSeeder;
use App\Facades\Config;
use App\Manipulators\FeeSetter;
use App\Manipulators\ProductSetter;
use App\Manipulators\ProgramSetter;
use App\Product;
use App\Program;
use DirectoryIterator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Seeds parent organization test data to specified database
 */
class TestProgramSeederCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testprogramseeder {--database=local}';

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
        $directory = 'docs/programs/';
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
            $prgData = json_decode(file_get_contents($fileinfo->getPathname()), true);
            $this->comment("{$prgData['name']['en']} (#{$prgData['id']})");
            $this->seedProgram($prgData);
        }

        $this->info("Seeded directory `{$directory}` into database `{$this->database}`.");
    }

    private function seedProgram(array $prgData)
    {
        $program = (new ProgramSetter($prgData))->set(true);
        $this->seedProgramProducts($program->id, $prgData);
        (new TestModelPropertySeeder())->seed($program, $prgData);
        TestModelSeeder::setGallery(Program::class, $program, !empty($prgData['media']) ? $prgData['media'] : []);
        return $program;
    }

    private function seedProgramProducts(int $programId, array $prgData)
    {
        if (!empty($prgData['products'])) {
            foreach ($prgData['products'] as $productData) {
                $this->setProduct($programId, $productData);
            }
        }
    }

    /**
     * Creates a Product object with provided data. It also creates Prices
     *
     * @param int $programId
     * @param array $productData
     */
    private function setProduct(int $programId, array $productData)
    {
        if (isset($productData['name'])) {
            $nameDescription = (new DescriptionSetter($productData['name']))->set();
        } else {
            $nameDescription = null;
        }
        $product = (new ProductSetter([
            'id' => $productData['id'],
            'productable_id' => $programId,
            'productable_type' => Program::class,
            'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.' . $productData['type']),
            'name_description_id' => $nameDescription ? $nameDescription->id : null
            ]))->set(true);

        if (!empty($productData['age_ranges'])) {
            TestModelSeeder::setAgeRanges(Product::class, $product->id, $productData['age_ranges']);
        }

        if (!empty($productData['fees'])) {
            foreach ($productData['fees'] as $feeData) {
                $this->setFee($product->id, $feeData);
            }
        }
    }

    /**
     * Creates a Price object with provided data. It also creates PriceElements
     *

     * @param int $productId
     * @param array $feeData
     * @return int
     */
    private function setFee(int $productId, array $feeData)
    {
        $feeSetterData = [
            'product_id' => $productId,
            'age_range' => !empty($feeData['age_range']) ? $feeData['age_range'] : null,
            'rack_price' => isset($feeData['rack_price']) ? $feeData['rack_price'] : null,
        ];
        $fee = (new FeeSetter($feeSetterData))->set();

        return $fee->id;
    }

}
