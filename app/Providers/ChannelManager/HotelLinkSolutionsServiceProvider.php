<?php

namespace App\Providers\ChannelManager;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Mail\HLSUpdateErrorMail;
use App\Manipulators\AvailabilitySetter;
use App\Providers\ChannelManager\HLS\Credential;
use App\Providers\ChannelManager\HLS\DateRange;
use App\Providers\ChannelManager\HLS\GetInventory;
use App\Providers\ChannelManager\HLS\GetInventoryResponse;
use App\Providers\ChannelManager\HLS\GetRatePlans;
use App\Providers\ChannelManager\HLS\Inventory;
use App\Providers\ChannelManager\HLS\InventoryRQ;
use App\Providers\ChannelManager\HLS\RatePlansRQ;
use Artisaninweb\SoapWrapper\Facade as SoapWrapperFacade;
use DateInterval;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class HotelLinkSolutionsServiceProvider extends ServiceProvider
{
    public $isValid = true;
    public $ids;

    private $soapWrapper;
    private $accommodation;
    private $availabilities = [];
    private $deviceIdsMap = [];
    private $config;
    private $credential;

    private $logger;

    /**
     * HotelLinkSolutionsServiceProvider constructor.
     * @param Application $app
     * @param $soapWrapper
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->config = Config::getOrFail('services.channel_managers.providers.hotel_link_solutions');

        $this->logger = new Logger('a10y');
        $handler = new RotatingFileHandler(Config::getOrFail('services.channel_managers.log_file'), 15, Logger::DEBUG, true, 0766);
        $this->logger->pushHandler($handler);

        /**  https://github.com/artisaninweb/laravel-soap */
        $this->soapWrapper = SoapWrapperFacade::add('Inventory', function ($service) {
            $service
                ->wsdl($this->config['wsdl_url'])
                ->cache(WSDL_CACHE_NONE)
                ->classmap([
                    'Amount' => HLS\Amount::class,
                    'Availability' => HLS\Availability::class,
                    'BookingCondition' => HLS\BookingCondition::class,
                    'Credential' => Credential::class,
                    'DateRange' => HLS\DateRange::class,
                    'Error' => HLS\ErrorCustom::class,
                    'GetInventory' => GetInventory::class,
                    'GetInventoryResponse' => HLS\GetInventoryResponse::class,
                    'GetRatePlans' => GetRatePlans::class,
                    'GetRatePlansResponse' => HLS\GetRatePlansResponse::class,
                    'Inventory' => HLS\Inventory::class,
                    'InventoryResponse' => HLS\InventoryResponse::class,
                    'InventoryRQ' => InventoryRQ::class,
                    'LastMinuteDefault' => HLS\LastMinuteDefault::class,
                    'MealsIncluded' => HLS\MealsIncluded::class,
                    'Policy' => HLS\Policy::class,
                    'RateDetail' => HLS\RateDetail::class,
                    'RatePackage' => HLS\RatePackage::class,
                    'RatePlan' => HLS\RatePlan::class,
                    'RatePlansRQ' => RatePlansRQ::class,
                    'RoomType' => HLS\RoomType::class,
                ]);
        });
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

    }

    /**
     * @param Accommodation $accommodation
     * @param array $ids
     * @return $this
     * @throws UserException
     */
    public function create(Accommodation $accommodation, array $ids)
    {
        $this->accommodation = $accommodation;
        $this->ids = $ids;

        $this->credential = new Credential([
            'ChannelManagerUsername' => $this->config['credential']['username'],
            'ChannelManagerPassword' => $this->config['credential']['password'],
            'HotelId' => $this->ids['channelManagerHotelId'],
            'HotelAuthenticationChannelKey' => $this->ids['hotelAuthenticationChannelKey'],
        ]);

        return $this;
    }

    /**
     * @return array
     * @throws UserException
     */
    public function list(): array
    {
        $ratePlansResponse = $this->fetchRatePlansResponse($this->credential);

        $rooms = [];
        foreach ($ratePlansResponse->GetRatePlansResult->Rooms as $room) {
            $rooms[$room->RoomId] = $room->Name;
        }

        return $rooms;
    }

    public function getAvailabilities()
    {
        return $this->availabilities;
    }

    /**
     * @return HotelLinkSolutionsServiceProvider
     * @throws UserException
     */
    public function fetch(): self
    {
        $this->deviceIdsMap = array_flip($this->ids['devicesChannelManagerId']);
        $this->logger->info('=====Update from API started for accommodation #' . $this->accommodation->id);
        $ratePlansResponse = $this->fetchRatePlansResponse($this->credential);

        $ratePlans = [];
        foreach ($ratePlansResponse->GetRatePlansResult->Rooms as $room) {
            foreach ($room->RatePlans as $ratePlan) {
                $ratePlans[] = $ratePlan->RatePlanId;
            }
        }

        try {
            $dateTo = (new DateTime())->add(new DateInterval('P' . $this->config['availability_to_days'] . 'D'));
        } catch (\Exception $e) {
            $dateTo = new DateTime();
        }

        try {
            $dateRange = new DateRange((new DateTime())->format('Y-m-d'), $dateTo->format('Y-m-d'));
            $rqDebugInfo = '[' . \json_encode($dateRange) . ',' . \json_encode($ratePlans) . ']';
            $this->logger->debug("call 'Inventory.GetInventory' with params: {$this->credential->HotelId} {$this->credential->HotelAuthenticationChannelKey} $rqDebugInfo");
            $response = $this->soapWrapper->call('Inventory.GetInventory', [
                'Request' => new GetInventory(new InventoryRQ($ratePlans, $dateRange, $this->credential))
            ]);
        } catch (\SoapFault $e) {
            $this->handleException('Inventory.GetInventory SOAP error: ' . $e->faultstring);
        }
        $this->logger->debug("Inventory.GetInventory response: " . \json_encode($response));
        $this->availabilities = $this->map($response);
        $this->logger->info('-----Update from API END for accommodation #' . $this->accommodation->id);

        return $this;
    }

    /**
     * @param GetInventoryResponse $response
     * @return array
     * @throws UserException
     */
    private function map(GetInventoryResponse $response): array
    {
        $availabilities = [];
        if (!empty($response->GetInventoryResult->Inventories)) {
            foreach ($response->GetInventoryResult->Inventories as $inventory) {
                if (!is_a($inventory, Inventory::class) || !$inventory->isValid()) {
                    $this->handleException('Inventory format error');
                }
                $deviceId = isset($this->deviceIdsMap[$inventory->RoomId]) ? $this->deviceIdsMap[$inventory->RoomId] : null;
                if (!$deviceId) {
                    continue;
                }
                $availabilities[$inventory->RoomId] = [
                    'availableType' => Device::class,
                    'availableId' => $deviceId,
                    'availabilities' => []
                ];
                foreach ($inventory->Availabilities as $availability) {
                    try {
                        $availabilities[$inventory->RoomId]['availabilities'][] = [
                            'availableType' => Device::class,
                            'availableId' => $deviceId,
                            'fromDate' => (new \DateTime($availability->DateRange->From))->format('Y-m-d'),
                            'toDate' => (new \DateTime($availability->DateRange->To))->add(new DateInterval('P1D'))->format('Y-m-d'),
                            'amount' => intval($availability->Quantity)
                        ];
                    } catch (\Exception $e) {
                        $this->handleException('Date format error @ device #' . $deviceId . ': ' . \json_encode($availability));
                    }
                }
            }
        }
        $availabilityList = array_filter($availabilities, function ($value) {
            return !empty($value['availabilities']);
        });
        $this->logger->debug("mapped: " . \json_encode($availabilityList));
        return $availabilityList;
    }

    /**
     * @throws UserException
     * @throws \Throwable
     */
    public function update()
    {
        foreach ($this->availabilities as $cmRoomId => $data) {
            $availabilitiesBefore = Availability::getAvailabilitiesToInfinity($data['availableType'],
                $data['availableId'], date('Y-m-d'), true);
            $this->logger->debug("before: " . json_encode($availabilitiesBefore->toJson()));

            $availabilitiesBefore->each(function ($item, $key) {
                $item->touch(); // touch item to indicate that data should be updated, but not changed
            });

            foreach ($data['availabilities'] as $availabilityData) {
                try {
                    (new AvailabilitySetter($availabilityData))->set();
                } catch (UserException $e) {
                    $this->handleException($e->getMessage());
                } catch (\Exception $e) {
                    $this->handleException($e->getMessage());
                }
            }
            $availabilitiesAfter = Availability::getAvailabilitiesToInfinity($data['availableType'],
                $data['availableId'], date('Y-m-d'), true);
            $this->logger->debug("after: " . json_encode($availabilitiesAfter->toJson()));
        }
        $this->logger->info('-----Local availability update END for accommodation #' . $this->accommodation->id);
    }

    /**
     * @param $message
     * @throws UserException
     */
    private function handleException($message)
    {
        $this->logger->error($message);
        $this->mailException($message);
        throw new UserException($message);
    }

    private function mailException($message)
    {
        $addressList = env('AVAILABILITY_ERROR_NOTIFICABLE_ADDRESSES', '');
        $addresses = array_map('trim', explode(',', $addressList));
        if (!empty($addresses)) {
            $mail = new HLSUpdateErrorMail(['hotelId' => $this->accommodation->id, 'message' => $message]);
            foreach ($addresses as $address) {
                Mail::to(trim($address))->send($mail);
            }
        }
    }

    /**
     * @param Credential $credential
     * @return mixed|null
     * @throws UserException
     */
    private function fetchRatePlansResponse(Credential $credential)
    {
        try {
            $this->logger->debug("call 'Inventory.GetRatePlans' with credentials: {$credential->HotelId} {$credential->HotelAuthenticationChannelKey}");
            $ratePlansResponse = $this->soapWrapper->call('Inventory.GetRatePlans', [
                'Request' => new GetRatePlans(new RatePlansRQ($credential))
            ]);
        } catch (\SoapFault $e) {
            $this->handleException('Inventory.GetRatePlans SOAP error: ' . $e->faultstring);
            $ratePlansResponse = null;
        }
        return $ratePlansResponse;
    }
}
