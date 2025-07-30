<?php

namespace Tests\Functional\Controllers;

use App\Accommodation;
use App\Device;
use App\Entities\AccommodationEntity;
use App\Entities\AvailabilityEntity;
use App\Entities\ShipGroupEntity;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\PayingStatusEvent;
use App\Facades\Config;
use App\Order;
use App\Services\Payment\Service as PaymentService;
use App\ShipGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\OrderTestTrait;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use OrderTestTrait;

    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare()
    {
        $sampleAccommodationOrderItem = [
            "orderItemableType" => Device::class,
            "orderItemableId" => Device::find(3)->id,
            "productableModel" => (new AccommodationEntity(Accommodation::findOrFail(1)))->getFrontendData(),
            "productableType" => Accommodation::class,
            "mealPlan" => "b/b",
            "interval" => [
                "date_from" => "2027-01-11",
                "date_to" => "2027-01-14"
            ],
            "calculatedPrice" => [
                "discounted_price" => 110,
                "original_price" => 110,
                "discounts" => [],
                "total_discount" => [],
                "meal_plan_id" => 2,
                "order_itemable_index" => 0,
                "meal_plan" => "b/b"
            ],
            "amount" => 1,
            "orderItemableIndex" => 0,
            "guests" => [
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Doe'
                ]
            ],
            "searchRequest" => [
                "interval" => ["date_from" => "2027-01-11", "date_to" => "2027-01-14"],
                "usages" => [
                    ["usage" => [["age" => 21, "amount" => 1]]]
                ],
                "search_options" => [],
                "selectedOccasion" => "holiday",
                "dontKnowChecked" => false,
                "returning_client" => false,
                "wedding_date" => null,
                "islands" => [],
                "meal_plans" => [],
                "organizations" => [1]
            ],
            "isOverbooked" => false,
            "requestHash" => "N4IglgdiBcoCYEMAuBTA+gMwE4HsC2MIATAAwCMAHALQkCsVZAzCADQiKppI6GmU30itEAF82AVwDOMANqgpCAOYpZoJSuhEybBHhziISGEREBdM2wDucGBHEAbe20k9oIABY57YRAE9WIHowwGLsrhgI9pIobFgAxjARUTHg0tAypmx4GhlsOFiy5kA"

        ];
        $shipGroup = ShipGroup::find(1);
        $sampleShipGroupOrderItem = [
            "orderItemableType" => ShipGroup::class,
            "orderItemableId" => $shipGroup->id,
            "productableModel" => (new ShipGroupEntity($shipGroup))->getFrontendData(),
            "productableType" => ShipGroup::class,
            "mealPlan" => "e/p",
            "interval" => [
                "date_from" => "2027-06-14",
                "date_to" => "2027-06-21"
            ],
            "calculatedPrice" => [
                "discounted_price" => 1100,
                "original_price" => 1100,
                "margin" => null,
                "discounts" => [],
                "total_discount" => [],
                "meal_plan_id" => 1,
                "order_itemable_index" => 0,
                "meal_plan" => "e/p"
            ],
            "amount" => 1,
            "orderItemableIndex" => 0,
            "guests" => [
                [
                    'age_range' => 'adult',
                    'first_name' => 'Jane',
                    'last_name' => 'Doe'
                ]
            ],
            "searchRequest" => [
                "interval" => ["date_from" => "2027-06-14", "date_to" => "2027-06-21"],
                "usages" => [
                    ["usage" => [["age" => 21, "amount" => 1]]]
                ],
                "search_options" => [],
                "selectedOccasion" => "holiday",
                "dontKnowChecked" => false,
                "returning_client" => false,
                "wedding_date" => null,
                "islands" => [],
                "meal_plans" => [],
                "organizations" => []
            ],
        ];
        $sampleAccommodationRequest = [
            "first_name" => "John",
            "last_name" => "Doe",
            "nationality" => "HU",
            "email" => "john.doe@stylersonline.com",
            "telephone" => "+36301234567",
            "language" => "hu",
            "remarks" => "Lorem ipsum.",
            "items" => [$sampleAccommodationOrderItem]
        ];
        return [$sampleAccommodationOrderItem, $sampleShipGroupOrderItem, $sampleAccommodationRequest];
    }

    /**
     * @test
     */
    public function it_can_save_an_order()
    {
        list(, , $sampleAccommodationRequest) = $this->prepare();
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $sampleAccommodationRequest
        );
        $this->assertTrue($responseData->success);
    }

    /**
     * @test
     */
    public function it_can_save_an_order_with_multiple_type_elements()
    {
        list($sampleAccommodationOrderItem, $sampleShipGroupOrderItem) = $this->prepare();
        $data = [
            "first_name" => "John",
            "last_name" => "Doe",
            "nationality" => "HU",
            "email" => "john.doe@stylersonline.com",
            "telephone" => "+36301234567",
            "language" => "hu",
            "remarks" => "Lorem ipsum.",
            "items" => [
                $sampleAccommodationOrderItem,
                $sampleShipGroupOrderItem
            ]
        ];
        $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $data
        );
    }

    /**
     * @test
     */
    public function it_can_decrease_availability_for_device()
    {
        $this->markTestSkipped('Availability is set manually - industry standard (and client request)');
        list(, , $sampleAccommodationRequest) = $this->prepare();
        $availableId = Device::find(3)->id;
        $dateFrom = "2027-01-11";
        $dateTo = "2027-01-14";
        $amount = 1;
        $availabilityEntity = new AvailabilityEntity(Device::class, $availableId);
        $originalAvs = $availabilityEntity->get($dateFrom, $dateTo);
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $sampleAccommodationRequest
        );
        $afterAvs = (new AvailabilityEntity(Device::class, $availableId))->get($dateFrom, $dateTo);
        $this->assertCount(count($originalAvs), $afterAvs);
        foreach ($originalAvs as $idx => $expected) {
            $this->assertEquals($expected['year'], $afterAvs[$idx]['year']);
            $this->assertEquals($expected['month'], $afterAvs[$idx]['month']);
            $this->assertEquals($expected['day'], $afterAvs[$idx]['day']);
            $this->assertEquals($expected['amount'] - $amount, $afterAvs[$idx]['amount']);
        }
    }

    /**
     * @test
     */
    public function it_can_decrease_availability_for_ship_group()
    {
        $this->markTestSkipped('Availability is set manually - industry standard (and client request)');
        list(, $sampleShipGroupOrderItem) = $this->prepare();
        $shipGroup = ShipGroup::first();
        $dateFrom = "2027-06-14";
        $dateTo = "2027-06-21";

        $devices = $shipGroup->devices;
        $originalAvs = [];
        foreach ($devices as $device) {
            $availabilityEntity = new AvailabilityEntity(Device::class, $device->id);
            $originalAvs[$device->id] = $availabilityEntity->get($dateFrom, $dateTo);
        }

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            [
                "first_name" => "John",
                "last_name" => "Doe",
                "nationality" => "HU",
                "email" => "john.doe@stylersonline.com",
                "telephone" => "+36301234567",
                "language" => "hu",
                "remarks" => "Lorem ipsum.",
                "items" => [$sampleShipGroupOrderItem]
            ]
        );

        $afterAvs = [];
        $deviceAmounts = [];
        foreach ($devices as $device) {
            $availabilityEntity = new AvailabilityEntity(Device::class, $device->id);
            $afterAvs[$device->id] = $availabilityEntity->get($dateFrom, $dateTo);
            $deviceAmounts[$device->id] = $device->amount;
        }

        $this->assertCount(count($originalAvs), $afterAvs);
        foreach ($originalAvs as $deviceID => $deviceAvailability) {
            $this->assertCount(count($deviceAvailability), $afterAvs[$deviceID]);
            foreach ($deviceAvailability as $idx => $expected) {
                $actual = $afterAvs[$deviceID][$idx];
                $this->assertEquals($expected['year'], $actual['year']);
                $this->assertEquals($expected['month'], $actual['month']);
                $this->assertEquals($expected['day'], $actual['day']);
                $this->assertEquals($expected['amount'] - $deviceAmounts[$deviceID], $actual['amount']);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_calculate_margins_for_order()
    {
        list(, , $sampleAccommodationRequest) = $this->prepare();
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $sampleAccommodationRequest
        );

        $orderItem = Order::orderBy('id')->get()->last()->items[0];
        $this->assertEquals(39.6, $orderItem->margin);
    }

    /**
     * @test
     */
    public function it_can_save_site_for_an_order()
    {
        $expected_referer = $this->faker->url;
        list(, , $sampleAccommodationRequest) = $this->prepare();
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => $expected_referer],
            $sampleAccommodationRequest
        );
        $this->assertEquals(parse_url($expected_referer, PHP_URL_HOST), $responseData->request->site);
    }

    /**
     * @test
     */
    public function it_can_get_order_by_token()
    {
        $order = $this->prepareSampleOrder();
        $token = sha1(base64_encode(str_random(40)));
        $order = Order::setToken($order->id, $token);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order/' . $token,
            'GET'
        );
        $this->assertEquals($order->id, $responseData->data->id);
        $this->assertEquals($token, $responseData->data->token);
        $this->assertEquals(Carbon::now(), $responseData->data->token_created_at);
    }

    /**
     * @test
     */
    public function it_can_not_get_order_by_expired_token()
    {
        $order = $this->prepareSampleOrder();
        $token = sha1(base64_encode(str_random(40)));
        $order = Order::setToken($order->id, $token);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order/' . $token . 'invalidtoken',
            'GET'
        );
        $this->assertEquals("The token is invalid or has expired.", $responseData->message);
        $this->assertFalse($responseData->data);

        $order->token_created_at = date('2012-08-11 15:00:00');
        $order->save();
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order/' . $token,
            'GET'
        );
        $this->assertEquals("The token is invalid or has expired.", $responseData->message);
        $this->assertFalse($responseData->data);
    }

    /**
     * @test
     */
    public function it_can_handle_sent_order_when_token_is_present()
    {
        // POST '/order' with token in payload
        $order = $this->prepareSampleOrder();
        $token = sha1(base64_encode(str_random(40)));
        $order = Order::setToken($order->id, $token);
        $order->status_taxonomy_id = Config::getOrFail('taxonomies.order_statuses.confirmed.id');
        $order->saveOrFail();

        list(, , $sampleAccommodationRequest) = $this->prepare();
        $sampleAccommodationRequest['id'] = $order->id;
        $sampleAccommodationRequest['token'] = $order->token;
        $sampleAccommodationRequest['billing_type_taxonomy_id'] = config('taxonomies.billing_types.individual');

        $this->expectsEvents(PayingStatusEvent::class);
        $fakeUrl = $this->faker->url;

        // this time we use 3rd party services, so we mock it to test our controller
        $mockedService = Mockery::mock(PaymentService::class);
        $mockedService->shouldReceive('create')->once()->andReturnSelf();
        $mockedService->shouldReceive('hasError')->once()->andReturn(false);
        $mockedService->shouldReceive('getResult')->once()->andReturn(['paymentPageUrl' => $fakeUrl]);
        App::instance('payment', $mockedService);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $sampleAccommodationRequest
        );
        $this->assertTrue($responseData->success);
        $this->assertEquals((object)['paymentPageUrl' => $fakeUrl], $responseData->data);

    }

    /**
     * @test
     */
    public function it_can_handle_sent_order_when_token_is_present_when_no_payable_status()
    {
        // POST '/order' with token in payload and !in_array($orderCurrentStatus, [$newUniqueProductOrderStatus, $confirmedStatus])
        $order = $this->prepareSampleOrder();
        $token = sha1(base64_encode(str_random(40)));
        $order = Order::setToken($order->id, $token);
        $order->status_taxonomy_id = config('taxonomies.order_statuses.offer_under_processing.id');
        $order->saveOrFail();

        list(, , $sampleAccommodationRequest) = $this->prepare();
        $sampleAccommodationRequest['id'] = $order->id;
        $sampleAccommodationRequest['token'] = $order->token;
        $sampleAccommodationRequest['billing_type_taxonomy_id'] = config('taxonomies.billing_types.individual');

        $this->expectsEvents(NewOrderStatusEvent::class);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/order',
            'POST',
            ['HTTP_REFERER' => 'http://localhost'],
            $sampleAccommodationRequest
        );
        $this->assertEquals([], $responseData->data);
    }
}