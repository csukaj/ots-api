<?php

namespace Tests\Integration\Services;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\OrganizationMeta;
use App\Providers\ChannelManager\HLS\Credential;
use App\Providers\ChannelManager\HLS\DateRange;
use App\Providers\ChannelManager\HLS\GetInventory;
use App\Providers\ChannelManager\HLS\GetInventoryResponse;
use App\Providers\ChannelManager\HLS\GetRatePlans;
use App\Providers\ChannelManager\HLS\InventoryRQ;
use App\Providers\ChannelManager\HLS\RatePlansRQ;
use Artisaninweb\SoapWrapper\Facade as SoapWrapperFacade;
use DateInterval;
use DateTime;
use Tests\TestCase;

class HotelLinkSolutionsServiceTest extends TestCase
{

    /**
     * @param array $mockList
     * @param int $accommodationId
     * @return array
     * @throws \Exception
     */
    protected function prepareMocks(array $mockList, int $accommodationId)
    {
        $accommodation = Accommodation::findOrFail($accommodationId);
        $config = config('services.channel_managers.providers.hotel_link_solutions');
        $expectedValues = [];


        SoapWrapperFacade::shouldReceive('add')
            ->once()
            ->andReturn(SoapWrapperFacade::getFacadeRoot());

        //{"ChannelManagerUsername":"ota","ChannelManagerPassword":"fakePassword","ChannelManagerAuthenticationKey":null,"HotelId":"fakeId","HotelUsername":null,"HotelPassword":null,"HotelAuthenticationKey":null,"HotelAuthenticationChannelKey":"74dd9b27c6d1fb5fb1289fae19878cac"}


        $credential = new Credential([
            'ChannelManagerUsername' => $config['credential']['username'],
            'ChannelManagerPassword' => $config['credential']['password'],
            'HotelId' => $accommodation->getChannelManagerHotelId(),
            'HotelAuthenticationChannelKey' => $accommodation->getHotelAuthenticationChannelKey(),
        ]);

        $ratePlanResponse = null;
        if (in_array('getRatePlans', $mockList)) { // must be the first condition to get rate plan list
            $testGetRatePlanRequest = [
                'Request' => new GetRatePlans(new RatePlansRQ($credential))
            ];
            $ratePlanResponse = json_decode('{"GetRatePlansResult":{"GetRatePlansResult":null,"Rooms":[{"RoomId":"fb332fbe-8a3f-1524732941-4927-8b96-f94ec114a95a","Name":"Lonely","RatePlans":[{"RatePlanId":"81a040fb-e068-1524733882-4a19-a5c5-c5a75585b927","Name":"Lonely Promotion Price","GuestsIncluded":7,"Order":651},{"RatePlanId":"e2acf69e-94df-1524734216-4b79-82f0-69b4db8ec187","Name":"Lonely Standard Price","Order":652}],"Order":7},{"RoomId":"c3f2707f-9c79-1531300576-424f-bd91-4cf3ab03e8d1","Name":"Ph\u00f2ng double room","RatePlans":[{"RatePlanId":"a4c5eb84-d020-1531300614-46a5-8fa1-345f8a201934","Name":"gi\u00e1 c\u00f3 \u0103n s\u00e1ng","Order":647}],"Order":999},{"RoomId":"25c57ba9-9120-1524733567-4cb6-8780-483b66db7bf6","Name":"Queen","RatePlans":[{"RatePlanId":"8b233616-26f9-1527130013-4a2f-9e4f-0207932d6faf","Name":"Queen Clearsale Price","Order":664},{"RatePlanId":"e4f7d6d3-550a-1524736431-4851-b04f-2070c6f3979a","Name":"Queen Promotion Price","Order":658},{"RatePlanId":"d07fe5e0-3038-1524734779-49d2-9b4e-cf18590dacd8","Name":"Queen Standard Price","Order":657}],"Order":8},{"RoomId":"3a138aae-ac2b-1507002078-4a25-a346-e795cbe9eea4","Name":"Deluxe","RatePlans":[{"RatePlanId":"d7160f21-48e3-1509082722-44c4-9c15-3c2012f6267d","Name":"Room With Free Upgrade","Order":600},{"RatePlanId":"51770a32-5b24-1507002134-4df1-a4f6-c630df949fc0","Name":"BEST AVAILABLE RATE","Order":599}],"Order":4},{"RoomId":"4301edc5-45f9-1522288287-446b-b7dc-9751bc8f5352","Name":"vntrip_21222","RatePlans":[{"RatePlanId":"f0fb87ed-2d22-1522291516-4e99-942d-0a2bdeecbb64","Name":"standard_vntrip_2","Order":643},{"RatePlanId":"c3e6e908-e5f4-1522289879-4591-b61e-f9a89c4f0746","Name":"best_vntrip_2222","Order":641}],"Order":6},{"RoomId":"3437b4e1-36d9-1524733448-4f85-bd61-641394ebd6dd","Name":"King","RatePlans":[{"RatePlanId":"00e6cab1-9b3a-1524734561-475c-a5db-a769edcab1f6","Name":"King Standard Price 1234","Order":655},{"RatePlanId":"5421d1c2-44e6-1524734670-4d46-90aa-ba6b5bf2d0e1","Name":"King Promotion Price","Order":656}],"Order":5},{"RoomId":"6e1c1409-0c83-1507192559-42b1-b85a-b0a63ab6b14d","Name":"Grand","RatePlans":[{"RatePlanId":"01a526a5-b48f-1507193902-4daa-b2e2-bb16a0b9b4fb","Name":"With breakfast and Transfo","Order":597},{"RatePlanId":"d0235273-81f6-1507192582-46f1-b711-8337cfb36280","Name":"Best Available Rate","Order":595}],"Order":3},{"RoomId":"8e920ce7-4dc2-1526032403-47e5-8712-c50acd5d5407","Name":"Ph\u00f2ng 1","RatePlans":[{"RatePlanId":"90ca46b4-44cf-1526032409-4154-9395-32741bf190aa","Name":"Bagong Plano, ikalawang yugto","Order":1},{"RatePlanId":"6ba9c413-2202-1527512743-4467-be47-b76b51574ac8","Name":"rate plan123","Order":665}],"Order":1},{"RoomId":"6a7da937-b691-1507169291-4916-8db0-0af3cc8bd199","Name":"Premier","RatePlans":[{"RatePlanId":"376eb8f9-ef59-1507192851-44ce-a0d8-06ac177f802a","Name":"With Breakfast and Transfo","Order":596},{"RatePlanId":"1d37fa7f-8cd4-1507169315-46d9-b980-f5add55c6ffd","Name":"BEST AVAILABLE RATE1122","Order":594}],"Order":2}],"Success":true,"Error":null,"MessageId":"29b26047-138b-1535099927-4195-9d66-271b26e871fd","MessageTime":""}}');
            $expectation = SoapWrapperFacade::shouldReceive('call')
                ->once()
                ->with('Inventory.GetRatePlans', $testGetRatePlanRequest)
                ->andReturn($ratePlanResponse);
            if('fakePassword' == $config['credential']['password']){
                //$expectation->andThrow(\SoapFault::class,'',0);
            }
            $expectedValues['getRatePlans'] = $ratePlanResponse;
        }
        if (in_array('getInventory', $mockList)) {
            $ratePlans = [];
            foreach ($ratePlanResponse->GetRatePlansResult->Rooms as $room) {
                foreach ($room->RatePlans as $ratePlan) {
                    $ratePlans[] = $ratePlan->RatePlanId;
                }
            }

            $dateTo = (new DateTime())->add(new DateInterval('P' . $config['availability_to_days'] . 'D'));
            $dateRange = new DateRange((new DateTime())->format('Y-m-d'), $dateTo->format('Y-m-d'));
            $testGetInventoryRequest = [
                'Request' => new GetInventory(new InventoryRQ($ratePlans, $dateRange, $credential))
            ];
            $getInventoryResponse = unserialize('O:53:"App\Providers\ChannelManager\HLS\GetInventoryResponse":1:{s:18:"GetInventoryResult";O:53:"App\Providers\ChannelManager\HLS\GetInventoryResponse":6:{s:18:"GetInventoryResult";N;s:11:"Inventories";a:8:{i:0;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"fb332fbe-8a3f-1524732941-4927-8b96-f94ec114a95a";s:14:"Availabilities";a:0:{}s:12:"RatePackages";a:2:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"81a040fb-e068-1524733882-4a19-a5c5-c5a75585b927";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:350000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"e2acf69e-94df-1524734216-4b79-82f0-69b4db8ec187";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:300000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}}}i:1;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"25c57ba9-9120-1524733567-4cb6-8780-483b66db7bf6";s:14:"Availabilities";a:0:{}s:12:"RatePackages";a:4:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"8b233616-26f9-1527130013-4a2f-9e4f-0207932d6faf";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:20000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-08-31T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"8b233616-26f9-1527130013-4a2f-9e4f-0207932d6faf";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:400;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-09-01T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}i:2;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"e4f7d6d3-550a-1524736431-4851-b04f-2070c6f3979a";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:900000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}i:3;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"d07fe5e0-3038-1524734779-49d2-9b4e-cf18590dacd8";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:1020000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}}}i:2;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"3a138aae-ac2b-1507002078-4a25-a346-e795cbe9eea4";s:14:"Availabilities";a:2:{i:0;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-12-31T00:00:00";}s:8:"Quantity";i:2;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}i:1;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2019-01-01T00:00:00";s:2:"To";s:19:"2019-12-31T00:00:00";}s:8:"Quantity";i:3;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}}s:12:"RatePackages";a:3:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"51770a32-5b24-1507002134-4df1-a4f6-c630df949fc0";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:450000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:0;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:0;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";i:0;s:16:"CloseToDeparture";i:0;s:8:"StopSell";i:0;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-12-31T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"51770a32-5b24-1507002134-4df1-a4f6-c630df949fc0";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:8064;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2019-01-01T00:00:00";s:2:"To";s:19:"2019-09-03T00:00:00";}s:7:"Channel";N;}i:2;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"51770a32-5b24-1507002134-4df1-a4f6-c630df949fc0";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:50000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2019-09-04T00:00:00";s:2:"To";s:19:"2019-12-31T00:00:00";}s:7:"Channel";N;}}}i:3;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"4301edc5-45f9-1522288287-446b-b7dc-9751bc8f5352";s:14:"Availabilities";a:0:{}s:12:"RatePackages";a:1:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"f0fb87ed-2d22-1522291516-4e99-942d-0a2bdeecbb64";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:40;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}}}i:4;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"3437b4e1-36d9-1524733448-4f85-bd61-641394ebd6dd";s:14:"Availabilities";a:0:{}s:12:"RatePackages";a:2:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"00e6cab1-9b3a-1524734561-475c-a5db-a769edcab1f6";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:1500000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"5421d1c2-44e6-1524734670-4d46-90aa-ba6b5bf2d0e1";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:1200000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}}}i:5;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"6e1c1409-0c83-1507192559-42b1-b85a-b0a63ab6b14d";s:14:"Availabilities";a:1:{i:0;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2019-12-31T00:00:00";}s:8:"Quantity";i:2;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}}s:12:"RatePackages";a:2:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"01a526a5-b48f-1507193902-4daa-b2e2-bb16a0b9b4fb";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:100;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2019-02-21T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"d0235273-81f6-1507192582-46f1-b711-8337cfb36280";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:9408;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2019-09-05T00:00:00";}s:7:"Channel";N;}}}i:6;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"8e920ce7-4dc2-1526032403-47e5-8712-c50acd5d5407";s:14:"Availabilities";a:0:{}s:12:"RatePackages";a:2:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"90ca46b4-44cf-1526032409-4154-9395-32741bf190aa";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:20000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-08-31T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"6ba9c413-2202-1527512743-4467-be47-b76b51574ac8";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:10000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2020-02-23T00:00:00";}s:7:"Channel";N;}}}i:7;O:42:"App\Providers\ChannelManager\HLS\Inventory":3:{s:6:"RoomId";s:47:"6a7da937-b691-1507169291-4916-8db0-0af3cc8bd199";s:14:"Availabilities";a:3:{i:0;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-11-30T00:00:00";}s:8:"Quantity";i:3;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}i:1;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-12-01T00:00:00";s:2:"To";s:19:"2018-12-31T00:00:00";}s:8:"Quantity";i:0;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}i:2;O:45:"App\Providers\ChannelManager\HLS\Availability":4:{s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2019-01-01T00:00:00";s:2:"To";s:19:"2019-12-31T00:00:00";}s:8:"Quantity";i:8;s:13:"ReleasePeriod";N;s:6:"Action";s:0:"";}}s:12:"RatePackages";a:5:{i:0;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"376eb8f9-ef59-1507192851-44ce-a0d8-06ac177f802a";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:20000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-08-31T00:00:00";}s:7:"Channel";N;}i:1;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"376eb8f9-ef59-1507192851-44ce-a0d8-06ac177f802a";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:10;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-09-01T00:00:00";s:2:"To";s:19:"2019-03-31T00:00:00";}s:7:"Channel";N;}i:2;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"376eb8f9-ef59-1507192851-44ce-a0d8-06ac177f802a";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:9431;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2019-04-01T00:00:00";s:2:"To";s:19:"2019-09-05T00:00:00";}s:7:"Channel";N;}i:3;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"1d37fa7f-8cd4-1507169315-46d9-b980-f5add55c6ffd";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:20000;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-08-24T00:00:00";s:2:"To";s:19:"2018-08-31T00:00:00";}s:7:"Channel";N;}i:4;O:44:"App\Providers\ChannelManager\HLS\RatePackage":11:{s:10:"RatePlanId";s:47:"1d37fa7f-8cd4-1507169315-46d9-b980-f5add55c6ffd";s:4:"Rate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";d:8064;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraAdultRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:14:"ExtraChildRate";O:43:"App\Providers\ChannelManager\HLS\RateDetail":2:{s:6:"Amount";O:39:"App\Providers\ChannelManager\HLS\Amount":3:{s:4:"Type";s:12:"FIXED_AMOUNT";s:5:"Value";N;s:8:"Currency";s:3:"VND";}s:6:"Action";s:0:"";}s:9:"MinNights";N;s:9:"MaxNights";N;s:14:"CloseToArrival";N;s:16:"CloseToDeparture";N;s:8:"StopSell";N;s:9:"DateRange";O:42:"App\Providers\ChannelManager\HLS\DateRange":2:{s:4:"From";s:19:"2018-09-01T00:00:00";s:2:"To";s:19:"2019-09-30T00:00:00";}s:7:"Channel";N;}}}}s:7:"Success";b:1;s:5:"Error";N;s:9:"MessageId";s:47:"d7377b71-80f3-1535109513-4ac7-b5d8-eed9a54948a3";s:11:"MessageTime";s:0:"";}}');
            $resp = new GetInventoryResponse($getInventoryResponse->GetInventoryResult);
            SoapWrapperFacade::shouldReceive('call')
                ->once()
                ->with('Inventory.GetInventory', $testGetInventoryRequest)
                ->andReturn($resp);
            $expectedValues['getInventory'] = $resp;
        }

        return $expectedValues;
    }

    /**
     * @test
     */
    function service_can_fetch_availability_data()
    {
        $accommodationId = 21;
        $this->prepareMocks(['getRatePlans', 'getInventory'], $accommodationId);
        $accommodation = Accommodation::findOrFail($accommodationId);
        $channelManagerService = app('channel_manager')->fetch($accommodation);
        $availabilities = $channelManagerService->getAvailabilities();
        $this->assertNotEmpty($availabilities);
        foreach ($availabilities as $availability) {
            $this->assertNotEmpty($availability['availableType']::find($availability['availableId']));
            $this->assertNotEmpty($availability['availabilities']);
            foreach ($availability['availabilities'] as $availabilityData) {
                $this->assertRegExp('/20\d\d-[01]\d-[0-3]\d/', $availabilityData['fromDate']);
                $this->assertRegExp('/20\d\d-[01]\d-[0-3]\d/', $availabilityData['toDate']);
                $this->assertGreaterThan(-1, $availabilityData['amount']);
            }
        }
    }

    /**
     * @test
     */
    function service_doesnt_fail_when_accommodation_has_no_channel_manager_id()
    {
        $accommodationId = 1;
        $accommodation = Accommodation::findOrFail($accommodationId);
        $channelManagerService = app('channel_manager')->fetch($accommodation);
        $this->assertFalse($channelManagerService->isValid);

    }

    /**
     * @test
     */
    function service_can_handle_errors()
    {
        $accommodationId = 21;
        config(['services.channel_managers.providers.hotel_link_solutions.credential.password' => 'fakePassword']);

        //TODO need to use mocked soap service with expected error
        //$this->prepareMocks(['getRatePlans', 'getInventory'], $accommodationId);

        $accommodation = Accommodation::findOrFail($accommodationId);
        OrganizationMeta
            ::where('taxonomy_id',
                Config::getOrFail('taxonomies.organization_properties.categories.settings.metas.channel_manager_id.id'))
            ->where('organization_id', $accommodationId)
            ->update(['value' => 'fakeId']);
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('SOAP error: Authentication Failed');
        app('channel_manager')->fetch($accommodation);

    }

    /**
     * @test
     * @throws UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     */
    function service_can_update_availability_data()
    {
        $accommodationId = 21;
        //$this->prepareMocks(['getRatePlans', 'getInventory'], $accommodationId);

        $accommodation = Accommodation::findOrFail($accommodationId);
        $channelManagerService = app('channel_manager')->fetch($accommodation);
        $channelManagerService->update();
        $deviceIds = $accommodation->devices()->get()->pluck('id');
        $maxAvailabilityUpdate = Availability
            ::where('available_type', Device::class)
            ->whereIn('available_id', $deviceIds)
            ->max('updated_at');
        $this->assertGreaterThan(date('Y-m-d H:i:s', time() - 10), $maxAvailabilityUpdate);
    }

    /**
     * @test
     * @throws UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Throwable
     */
    function service_can_write_logs()
    {
        $logfile = $this->getTimedFilename(Config::getOrFail('services.channel_managers.log_file'));
        $fileSizeBefore = filesize($logfile);
        ///
        $accommodationId = 21;
        $accommodation = Accommodation::findOrFail($accommodationId);
        $channelManagerService = app('channel_manager')->fetch($accommodation);
        $channelManagerService->update();
        ///
        $fileSizeAfter = filesize($logfile);
        $this->assertGreaterThan($fileSizeBefore, $fileSizeAfter);
        $difference = $fileSizeAfter - $fileSizeBefore;

        $fp = fopen($logfile, 'r');
        fseek($fp, -1 * $difference, SEEK_END); // It needs to be negative
        $log = explode("\n", fread($fp, $difference));
        fclose($fp);

        $this->assertRegExp('/\.INFO.+Update from API started for accommodation #\d+/', $log[0]);
        $this->assertRegExp('/\.DEBUG.+call \'Inventory.GetRatePlans\' with credentials:/', $log[1]);
        $this->assertRegExp('/\.DEBUG.+call \'Inventory.GetInventory\' with params/', $log[2]);
        $this->assertRegExp('/\.DEBUG.+Inventory.GetInventory response:/', $log[3]);
        $this->assertRegExp('/\.DEBUG.+ mapped: {"/', $log[4]);
        $this->assertRegExp('/\.INFO.+Update from API END for accommodation #\d+/', $log[5]);
        $availabilityUpdateDebug = array_slice($log, 6, count($log) - 2 - 6);
        $this->assertNotEmpty($availabilityUpdateDebug);
        $this->assertTrue(count($availabilityUpdateDebug) % 2 == 0);
        $this->assertRegExp('/\.INFO.+Local availability update END for accommodation #\d+/', $log[count($log) - 2]);
        //TODO ('Need to check custom log writing.');  example: https://medium.com/@samrapaport/unit-testing-log-messages-in-laravel-5-6-a2e737247d3a
    }


    /**
     * @test
     */
    function all_test_above_this_with_mocked_soap_communication()
    {
        $this->markTestIncomplete('Need to mock soap communication to really test code');
    }

    /**
     * @test
     */
    function service_can_list_room_ids()
    {
        $accommodationId = 21;
        $expectedValues = $this->prepareMocks(['getRatePlans'], $accommodationId);
        $expected = [];
        foreach ($expectedValues['getRatePlans']->GetRatePlansResult->Rooms as $room) {
            $expected[$room->RoomId] = $room->Name;
        }

        $accommodation = Accommodation::findOrFail($accommodationId);
        $idList = app('channel_manager')->list($accommodation);

        $this->assertNotEmpty($idList);
        $this->assertEquals($expected, $idList);
    }

    /**
     * @test
     */
    function list_doesnt_fail_when_accommodation_has_no_channel_manager_id()
    {
        $accommodationId = 1;
        $accommodation = Accommodation::findOrFail($accommodationId);
        $idList = app('channel_manager')->list($accommodation);
        $this->assertEquals([], $idList);

    }

    private function getTimedFilename($filename)
    {
        $fileInfo = pathinfo($filename);
        $timedFilename = str_replace(
            array('{filename}', '{date}'),
            array($fileInfo['filename'], date('Y-m-d')),
            $fileInfo['dirname'] . '/' . '{filename}-{date}'
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.' . $fileInfo['extension'];
        }

        return $timedFilename;
    }
}
