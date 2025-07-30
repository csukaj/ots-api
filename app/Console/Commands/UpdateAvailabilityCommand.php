<?php

namespace App\Console\Commands;

use App\Accommodation;
use App\Facades\Config;
use App\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateAvailabilityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateavailability  {--channel_manager=hls}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates availability data from Hotel Link Solutions API for linked hotels';

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
     * @throws \Exception
     */
    public function handle()
    {
        $chmInput = $this->option('channel_manager');
        Log::info("Availability update started for `$chmInput`");
        switch ($chmInput) {
            case 'hls':
                $channelManagerTaxonomyId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.elements.Hotel Link Solutions');
                break;
            default:
                throw new \Exception('Unsupported channel manager: `' . $chmInput . '`');
        }

        $organizationIds = Organization::getChannelManagedOrganizationIds($channelManagerTaxonomyId);
        foreach ($organizationIds as $accommodationId) {
            $accommodation = Accommodation::findOrFail($accommodationId);
            $channelManagerService = app('channel_manager')->fetch($accommodation);
            //$availabilities = $channelManagerService->getAvailabilities();
            $channelManagerService->update();
        }
        return 0;
    }
}
