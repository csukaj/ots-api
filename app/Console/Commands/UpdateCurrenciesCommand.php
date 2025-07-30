<?php

namespace App\Console\Commands;

use App\Facades\Config;
use App\Services\FixerCurrencyApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCurrenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatecurrencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command get the actual currencies from fixer.io';

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
        app('currency_logger')->info('[' . app()->environment() . '] START update . IP: ' . request()->ip());

        try {
            if ($response = (new FixerCurrencyApiService())->getCurrencies()) {
                app('currency_logger')->info('[' . app()->environment() . '] SUCCESS update . IP: ' . request()->ip());
                $this->info("Currency command run with successful transfer");
                $this->storeCurrencies($response);
            } else {
                app('currency_logger')->info('[' . app()->environment() . '] ERROR update . IP: ' . request()->ip());
                $this->error("Currency command run with error. See log.");
            }
        } catch (GuzzleException $e) {
            app('currency_logger')->info('[' . app()->environment() . '] ERROR communiction . IP: ' . request()->ip());
            $this->error("Currency command run with communication error");
        } catch (\Exception $e) {
            app('currency_logger')->info('[' . app()->environment() . '] ERROR exception: ' . $e->getMessage() . ' . IP: ' . request()->ip());
            Log::error("Currency command can't save result: ". $e->getMessage());
            $this->error("Currency command can't save result. See log.");
        }

    }

    /**
     * @param $obj
     * @throws \Exception
     */
    private function storeCurrencies($obj)
    {
        $currencies = $this->loadCurrenciesFromFile();
        if ($obj->success && $currencies != json_encode($obj)) {
            file_put_contents(Config::getOrFail('cache.currencies_filename'), json_encode($obj));
            chmod(Config::getOrFail('cache.currencies_filename'), 0666);
        }
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    private function loadCurrenciesFromFile()
    {
        return file_get_contents(Config::getOrFail('cache.currencies_filename'));
    }
}
