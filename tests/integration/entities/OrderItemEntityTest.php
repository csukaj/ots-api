<?php

namespace Tests\Integration\Entities;

use App\Accommodation;
use App\AgeRange;
use App\Device;
use App\Entities\AccommodationEntity;
use App\Entities\DeviceEntity;
use App\Entities\OrderItemEntity;
use App\Entities\SupplierEntity;
use App\MealPlan;
use App\Order;
use App\OrderItem;
use App\OrderItemGuest;
use App\Supplier;
use Tests\OrderTestTrait;
use Tests\TestCase;

class OrderItemEntityTest extends TestCase
{
use OrderTestTrait;
    static public $setupMode = self::SETUPMODE_ONCE;

    private function createOrder($withMargin = false)
    {
        $order = $this->prepareSampleOrder();

        $item = $order->items->first();

        $accommodation = $item->orderItemable->deviceable;
        $accommodation->supplier_id = Supplier::first()->id;
        $accommodation->saveOrFail();
        return $order;
    }

    /**
     * @test
     */
    function an_order_has_frontend_data()
    {
        $order = $this->createOrder();
        $orderItem = $order->items->first();
        $frontendData = (new OrderItemEntity($orderItem))->getFrontendData();

        $this->assertEquals(count($orderItem->guests), count($frontendData['guests']));

        $deviceableId = $orderItem->orderItemable->deviceable_id;

        $orderItemData = $orderItem->attributesToArray();
        $orderItemData['order_itemable'] = (new DeviceEntity($orderItem->orderItemable))->getFrontendData();
        $orderItemData['organization'] = (new AccommodationEntity(Accommodation::findOrFail($deviceableId)))->getFrontendData([
            'supplier',
            'contacts',
            'people',
            'admin_properties',
            'location'
        ]);

        $itemJsonData = json_decode($orderItem->json);
        $orderItemData['compulsory_fee'] = $itemJsonData && isset($itemJsonData->compulsoryFee) ? (float)$itemJsonData->compulsoryFee : null;
        $orderItemData['supplier'] = (new SupplierEntity($orderItem->orderItemable->deviceable->supplier))->getFrontendData([
            'contacts',
            'people'
        ]);
        unset($frontendData['guests']);
        $this->assertEquals($orderItemData, $frontendData);

    }

    /**
     * @test
     */
    function an_order_has_frontend_data_when_margin_defined()
    {
        $order = $this->createOrder(true);
        $orderItem = $order->items->first();
        $frontendData = (new OrderItemEntity($orderItem))->getFrontendData();

        $this->assertEquals(count($orderItem->guests), count($frontendData['guests']));

        $deviceableId = $orderItem->orderItemable->deviceable_id;

        $orderItemData = $orderItem->attributesToArray();
        $orderItemData['order_itemable'] = (new DeviceEntity($orderItem->orderItemable))->getFrontendData();
        $orderItemData['organization'] = (new AccommodationEntity(Accommodation::findOrFail($deviceableId)))->getFrontendData([
            'supplier',
            'contacts',
            'people',
            'admin_properties',
            'location'
        ]);

        $itemJsonData = json_decode($orderItem->json);
        $orderItemData['compulsory_fee'] = $itemJsonData && isset($itemJsonData->compulsoryFee) ? (float)$itemJsonData->compulsoryFee : null;
        $orderItemData['supplier'] = (new SupplierEntity($orderItem->orderItemable->deviceable->supplier))->getFrontendData([
            'contacts',
            'people'
        ]);
        unset($frontendData['guests']);
        $this->assertEquals($orderItemData, $frontendData);

    }

}
