<?php

namespace App\Providers\ChannelManager;

use App\Accommodation;
use App\Device;
use App\Facades\Config;
use Artisaninweb\SoapWrapper\Facade as SoapWrapperFacade;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public $isValid = true;

    /**
     * ServiceProvider constructor.
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @param Accommodation $accommodation
     * @return $this|HotelLinkSolutionsServiceProvider
     * @throws \App\Exceptions\UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Exception
     */
    public function fetch(Accommodation $accommodation)
    {
        $provider = $this->createProvider($accommodation);
        if (!$provider || empty($provider->ids['devicesChannelManagerId'])) {
            $this->isValid = false;
        }
        return $this->isValid ? $provider->fetch() : $this;
    }

    /**
     * @param Accommodation $accommodation
     * @return mixed|null
     * @throws \App\Exceptions\UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Exception
     */
    public function list(Accommodation $accommodation): array
    {
        $provider = $this->createProvider($accommodation, false);
        return $provider && $this->isValid ? $provider->list() : [];
    }

    /**
     * @param Accommodation $accommodation
     * @param bool $withDeviceIds
     * @return HotelLinkSolutionsServiceProvider|ServiceProvider|null
     * @throws \App\Exceptions\UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Exception
     */
    private function createProvider(Accommodation $accommodation, $withDeviceIds = true)
    {
        $channelManagerTxId = $accommodation->getChannelManagerId();
        if (!$channelManagerTxId) {
            return null;
        }
        $ids = $this->getIds($channelManagerTxId, $accommodation, $withDeviceIds);
        return $this->channelManagerFactory($accommodation, $channelManagerTxId, $ids);
    }

    /**
     * @param int $channelManagerTxId
     * @param Accommodation $accommodation
     * @param bool $withDeviceIds
     * @return array
     * @throws \Exception
     */
    private function getIds(int $channelManagerTxId, Accommodation $accommodation, bool $withDeviceIds = true)
    {
        $ids = [
            'channelManagerHotelId' => $channelManagerTxId ? $accommodation->getChannelManagerHotelId() : null,
            'hotelAuthenticationChannelKey' => $channelManagerTxId ? $accommodation->getHotelAuthenticationChannelKey() : null,
        ];
        if ($withDeviceIds) {
            $ids['devicesChannelManagerId'] = $channelManagerTxId ? Device::getDevicesChannelManagerId($accommodation->id) : null;
        }

        return $ids;
    }

    /**
     * @param Accommodation $accommodation
     * @param int $channelManagerTxId
     * @param array $ids
     * @return $this|HotelLinkSolutionsServiceProvider|null
     * @throws \App\Exceptions\UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Exception
     */
    private function channelManagerFactory(Accommodation $accommodation, int $channelManagerTxId, array $ids)
    {

        if (!$channelManagerTxId || empty($ids['channelManagerHotelId']) || empty($ids['hotelAuthenticationChannelKey'])) {
            $this->isValid = false;
            return $this;
        }

        $channelManagers = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.elements');

        switch ($channelManagerTxId) {
            case $channelManagers['Hotel Link Solutions']:
                /**  https://github.com/artisaninweb/laravel-soap */
                return (new HotelLinkSolutionsServiceProvider($this->app))->create($accommodation, $ids);
                break;
        }
        return null;
    }

}
