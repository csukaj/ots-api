<?php

namespace Tests\Functional\Controllers\Admin;

use App\Device;
use App\Entities\AvailabilityEntity;
use App\Entities\OrderEntity;
use App\Entities\ShipGroupEntity;
use App\Events\Order\ClosedStatusEvent;
use App\Events\Order\ConfirmedStatusEvent;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\NewUniqueProductOrderStatusEvent;
use App\Events\Order\OfferUnderProcessingStatusEvent;
use App\Events\Order\PayingStatusEvent;
use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use App\Order;
use App\ShipGroup;
use Illuminate\Support\Facades\Config;
use Tests\OrderTestTrait;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use OrderTestTrait;

    private $originalAvailabilities;

    private function prepareOrders($dateFrom = "2027-06-14", $dateTo = "2027-06-21")
    {
        $shipGroup = ShipGroup::find(1);
        $sampleShipGroupOrderItem = [
            "orderItemableType" => ShipGroup::class,
            "orderItemableId" => $shipGroup->id,
            "productableModel" => (new ShipGroupEntity($shipGroup))->getFrontendData(),
            "productableType" => ShipGroup::class,
            "mealPlan" => "e/p",
            "interval" => [
                "date_from" => $dateFrom,
                "date_to" => $dateTo
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
                "interval" => ["date_from" => $dateFrom, "date_to" => $dateTo],
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

        $this->assertSuccessfulHttpApiRequest(
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

        $devices = ShipGroup::first()->devices;
        $originalAvs = [];
        foreach ($devices as $device) {
            $availabilityEntity = new AvailabilityEntity(Device::class, $device->id);
            $originalAvs[$device->id] = $availabilityEntity->get($dateFrom, $dateTo);
        }
        $this->originalAvailabilities = $originalAvs;
    }

    /**
     * @test
     */
    public function it_can_list_orders()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->prepareOrders();

        $expected = OrderEntity::getCollection(Order::all());

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/order', 'GET', $token, [], true);
        $this->assertGreaterThan(0, count($responseData['data']));
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_list_orders_for_advisor_only_for_allowed_sites()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_advisor')]);
        $this->prepareOrders();

        $enabledSites = $user->sites->pluck('site');
        $expected = OrderEntity::getCollection(Order::whereIn('site', $enabledSites)->get());

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/order', 'GET', $token, [], true);

        $this->markTestIncomplete('needs test data');
        $this->assertGreaterThan(0, count($responseData['data']));
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_show_an_order()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->prepareOrders();

        $id = Order::firstOrFail()->id;
        $expected = (new OrderEntity(Order::findOrFail($id)))->getFrontendData();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/order/{$id}", 'GET', $token, [], true);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_delete_an_order()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $dateFrom = "2027-06-14";
        $dateTo = "2027-06-21";
        $this->prepareOrders($dateFrom, $dateTo);
        /* Test assertion Skipped('Availability is set manually - industry standard (and client request)');
         * $originalAvs = [];
        $devices = ShipGroup::find(1)->devices;
        foreach ($devices as $device) {
            $availabilityEntity = new AvailabilityEntity(Device::class, $device->id);
            $originalAvs[$device->id] = $availabilityEntity->get($dateFrom, $dateTo);
        }*/

        $orderId = Order::orderBy('id')->get()->last()->id;

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/order/{$orderId}", 'DELETE', $token);

        $this->assertEquals($orderId, $responseData->data->id);

        /*
         * $this->markTestSkipped('Availability is set manually - industry standard (and client request)');
         * $afterAvs = [];
        $deviceAmounts = [];
        foreach ($devices as $device) {
            $availabilityEntity = new AvailabilityEntity(Device::class, $device->id);
            $afterAvs[$device->id] = $availabilityEntity->get($dateFrom, $dateTo);
            $deviceAmounts[$device->id] = $device->amount;
        }

        $this->assertCount(count($originalAvs), $afterAvs);
        foreach ($originalAvs as $deviceID => $device) {
            $this->assertCount(count($device), $afterAvs[$deviceID]);
            foreach ($device as $idx => $expected) {
                $actual = $afterAvs[$deviceID][$idx];
                $this->assertEquals($expected['year'], $actual['year']);
                $this->assertEquals($expected['month'], $actual['month']);
                $this->assertEquals($expected['day'], $actual['day']);
                $this->assertEquals($expected['amount'] + $deviceAmounts[$deviceID], $actual['amount']);
            }
        }*/
    }

    /**
     * @test
     */
    public function advisor_cant_see_and_delete_only_own_site_orders()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_advisor')]);
        $this->prepareOrders();

        $id = Order::firstOrFail()->id;
        $expected = (new OrderEntity(Order::findOrFail($id)))->getFrontendData();

        $this->markTestIncomplete('implementation need');

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/order/{$id}", 'GET', $token, [], true);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_set_order_status()
    {
        // POST '/admin/order/set-status'
        // for all possible statuses that user can select

        $order = $this->prepareSampleOrder();
        //set automatically, user can't select it as  target status, no need to test //$this->testAStatus($order, NewOrderStatusEvent::class);
        $this->checkAStatus($order, WaitingForOfferStatusEvent::class);
        $this->checkAStatus($order, OfferUnderProcessingStatusEvent::class);
        $this->checkAStatus($order, ConfirmedStatusEvent::class);
        $this->checkAStatus($order, ClosedStatusEvent::class);
        //set automatically, user can't select it as  target status, no need to test //$this->testAStatus($order, PayingStatusEvent::class);
        //set automatically, user can't select it as  target status, no need to test //$this->testAStatus($order, PaymentSuccessStatusEvent::class);
        //set automatically, user can't select it as  target status, no need to test //$this->testAStatus($order, PaymentFailedStatusEvent::class);
        $this->checkAStatus($order, NewUniqueProductOrderStatusEvent::class);
    }

    private function checkAStatus(Order $order, string $eventClass)
    {
        list($token,) = $this->login([Config::get('stylersauth.role_advisor')]);
        $this->expectsEvents($eventClass);
        $order->refresh();
        $orderData = (new OrderEntity($order))->getFrontendData();
        $postBody = ['model' => $orderData, 'targetStatus' => Config::get('taxonomies.order_statuses.' . $this->toSnakeCase($eventClass,true) . '.id')];
        $this->assertSuccessfulHttpApiRequest("/admin/order/set-status", 'POST', $token, $postBody, true);
    }
}
