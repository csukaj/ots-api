<?php

namespace App\Console;

use App\Console\Commands\ClearCacheCommand;
use App\Console\Commands\FlushRedisCommand;
use App\Console\Commands\RegenerateImagesCommand;
use App\Console\Commands\TestAccommodationSeederCommand;
use App\Console\Commands\TestCommand;
use App\Console\Commands\TestContactSeederCommand;
use App\Console\Commands\TestContentSeederCommand;
use App\Console\Commands\TestCruiseSeederCommand;
use App\Console\Commands\TestEmailSeederCommand;
use App\Console\Commands\TestHotelChainSeederCommand;
use App\Console\Commands\TestManagerSeederCommand;
use App\Console\Commands\TestPeopleSeederCommand;
use App\Console\Commands\TestProgramRelationSeederCommand;
use App\Console\Commands\TestProgramSeederCommand;
use App\Console\Commands\TestShipCompanySeederCommand;
use App\Console\Commands\TestShipGroupSeederCommand;
use App\Console\Commands\TestShipSeederCommand;
use App\Console\Commands\TruncateDbCommand;
use App\Console\Commands\UpdateAvailabilityCommand;
use App\Console\Commands\UpdateCacheCommand;
use App\Console\Commands\UpdateCurrenciesCommand;
use App\Console\Commands\UpdateTranslationsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        TruncateDbCommand::class,
        TestHotelChainSeederCommand::class,
        TestAccommodationSeederCommand::class,
        TestShipCompanySeederCommand::class,
        TestShipGroupSeederCommand::class,
        TestShipSeederCommand::class,
        TestManagerSeederCommand::class,
        TestContentSeederCommand::class,
        TestProgramSeederCommand::class,
        TestProgramRelationSeederCommand::class,
        TestCruiseSeederCommand::class,
        TestContactSeederCommand::class,
        TestEmailSeederCommand::class,
        TestPeopleSeederCommand::class,
        UpdateCacheCommand::class,
        UpdateTranslationsCommand::class,
        ClearCacheCommand::class,
        FlushRedisCommand::class,
        RegenerateImagesCommand::class,
        UpdateCurrenciesCommand::class,
        TestCommand::class,
        UpdateAvailabilityCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//         $schedule->command(UpdateCurrenciesCommand::class  )->quarterly();  <-- bad 'quarter' is quarter of YEAR not hour
         //$currencyCommand = $schedule->command('command:updatecurrencies'); <-- can be bad without schedule frequency setting
         if(App::environment()=='production'){
             $schedule->command('command:updatecurrencies')->everyTenMinutes();
             # @todo @ivan @20190116 - majd a teszteles utan at kell allitani hourly-ra, mert a fixer.io basic csomagjaban az van benne
             // $schedule->command('command:updatecurrencies')->hourly();
         }else{
             $schedule->command('command:updatecurrencies')->weekly();
         }
         $schedule->command('command:updateavailability --channel_manager=hls')->hourly(); //->withoutOverlapping();
    }
}
