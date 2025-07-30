<?php

namespace Tests\Command;

use App\Facades\Config;
use App\Island;
use App\MealPlan;
use Modules\Stylerstaxonomy\Entities\Language;
use Tests\TestCase;
use function dd;

class UpdateCacheCommandTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $cwd = '';
    protected $frontend_cache_directories;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->cwd = realpath(__DIR__ . '/../..');
    }

    public function getConfigJson($fileName, $directory)
    {
        $file = $this->cwd . '/' . $directory . '/' . $fileName . '.ts';
        $this->assertFileExists($file);
        $content = str_replace(["const {$fileName} = ", ";\nexport default {$fileName};"], [],
            file_get_contents($file));
        return json_decode($content, true);
    }

    /**
     * @test
     */
    public function it_creates_islands_json_and_its_data_is_fine()
    {
        $this->createCache();
        $islands = Island::all();

        foreach (Config::get('cache.frontend_cache_directories') as $directory) {
            $file = 'islands_testing';
            $fileData = $this->getConfigJson($file, $directory);

            $this->assertCount(count($islands), $fileData);

            foreach ($islands as $island) {
                $elementFound = false;
                foreach ($fileData as $fileIsland) {
                    if ($fileIsland['id'] == $island->id && $fileIsland['name'] == $island->name->name) {
                        $elementFound = true;
                    }
                }
                $this->assertTrue($elementFound);
            }
        }

        $this->removeCache();
    }

    /**
     * @test
     */
    public function it_creates_languages_json_and_its_data_is_fine()
    {
        $this->createCache();

        foreach (Config::get('cache.frontend_cache_directories') as $directory) {
            $file = 'languages_testing';
            $fileData = $this->getConfigJson($file, $directory);
            $languages = Language::all();
            $languageCount = count($languages);

            $this->assertCount($languageCount, $fileData);
            foreach ($languages as $language) {
                $elementFound = false;
                if (
                    $language->id == $fileData[$language->iso_code]['id'] &&
                    $language->name->name == $fileData[$language->iso_code]['name'] &&
                    $language->iso_code == $fileData[$language->iso_code]['iso_code'] &&
                    $language->date_format == $fileData[$language->iso_code]['date_format'] &&
                    $language->time_format == $fileData[$language->iso_code]['time_format'] &&
                    $language->first_day_of_week == $fileData[$language->iso_code]['first_day_of_week']
                ) {
                    $elementFound = true;
                }

                $this->assertTrue($elementFound);
            }
        }

        $this->removeCache();
    }

    /**
     * @test
     */
    public function it_creates_meal_plans_json_and_its_data_is_fine()
    {

        $this->createCache();
        $mealPlans = MealPlan::all();
        $mealPlanCount = count($mealPlans);

        foreach (Config::get('cache.frontend_cache_directories') as $directory) {
            $file = 'meal_plans_testing';
            $fileData = $this->getConfigJson($file, $directory);
            $this->assertCount($mealPlanCount, $fileData);

            foreach ($mealPlans as $mealPlan) {
                $elementFound = false;
                for ($i = 0; $i < $mealPlanCount; $i++) {
                    if ($fileData[$i]['id'] == $mealPlan->id && $fileData[$i]['name'] == $mealPlan->name->name) {
                        $elementFound = true;
                    }
                }
                $this->assertTrue($elementFound);
            }
        }

        $this->removeCache();
    }

    /**
     * @test
     */
    public function it_creates_site_languages_json_and_its_data_is_fine()
    {

        $this->createCache();
        $expected = Config::getOrFail('ots.site_languages');

        foreach (Config::get('cache.frontend_cache_directories') as $directory) {
            $fileData = $this->getConfigJson('site_languages_testing', $directory);
            $this->assertEquals($expected, $fileData);
        }

        $this->removeCache();
    }

    protected function createCache()
    {
        $output = null;
        $returnVar = null;
        exec('cd ' . $this->cwd . ' && php artisan command:updatecache --database=testing', $output, $returnVar);
        if ($returnVar) {
            echo 'Error while running updatecache command.' . PHP_EOL;
            dd($output);
        }
    }

    protected function removeCache()
    {
        $output = null;
        $returnVar = null;
        exec('cd ' . $this->cwd . ' && php artisan command:clearcache --database=testing', $output, $returnVar);
        if ($returnVar) {
            echo 'Error while running updatecache command.' . PHP_EOL;
            dd($output);
        }
    }

}
