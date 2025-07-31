<?php

namespace Tests\Command;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateAvailabilityCommandTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $cwd = '';
    protected $frontend_cache_directories;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->cwd = realpath(__DIR__ . '/../..');
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->app->configureMonologUsing(function ($monolog) {
            $monolog->pushHandler(new \Monolog\Handler\TestHandler());
        });
    }


    /** @test */
    public function it_displays_error_on_failure()
    {

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported channel manager: `NOT_EXISTS`');

        // Call a method that should create a log message
        Artisan::call('command:updateavailability', ['--channel_manager' => 'NOT_EXISTS']);

    }

    /** @test */
    public function it_adds_log_line_on_normal_execution()
    {
        $managerName = 'hls';

        $start = microtime(true);
        // Call a method that should create a log message
        Artisan::call('command:updateavailability', ['--channel_manager' => $managerName]);

        $elapsed = microtime(true) - $start;
        $this->assertGreaterThan(2, $elapsed); // check if anything happened in command

        // If you need result of console output        $resultAsText = Artisan::output();

        //$this->assertTrue($resultAsText);
        // Retrieve the records from the Monolog TestHandler
        $records = app('log')->getMonolog()->getHandlers()[0]->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals('Availability update started for `' . $managerName . '`', $records[0]['message']);
    }

}
