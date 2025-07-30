<?php

namespace App\Listeners\Order;

use App\Accommodation;
use App\AgeRange;
use App\Device;
use App\Entities\CartElementEntity;
use App\Entities\CartEntity;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\SetAvailabilityEvent;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Http\Requests\OrderSendRequest;
use App\Manipulators\OrderItemSetter;
use App\MealPlan;
use App\Order;
use App\OrderItem;
use App\OrderItemGuest;
use App\Organization;
use App\Services\OrderStatusHandlerService;
use App\Services\OrderStatusLogger;
use App\ShipGroup;
use ErrorException;
use Exception;

class NewOrderHandler
{
    private $order;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentSuccessStatusEvent $event
     * @return void
     * @throws Exception
     * @throws \Throwable
     */
    public function handle(NewOrderStatusEvent $event)
    {
        if ($this->order = $this->saveOrder($event->request)) {
            (new OrderStatusLogger($this->order))->addLog([
                'date' => date('Y-m-d H:i:s'),
                'status' => 'NEW_ORDER',
                'events' => ['Order is saved']
            ]);
        }
        //availability is set manually (client request - industry standard) - G 180806
        (new OrderStatusHandlerService())->setOrder($this->order)->stepStatus('WAITING_FOR_OFFER');
    }

    public function getOrder(): Order{
        return $this->order;
    }

    /**
     * @param $request
     * @return Order
     * @throws Exception
     * @throws \Throwable
     */
    protected function saveOrder(OrderSendRequest $request)
    {
        $requestArray = $request->toArray();
        $requestArray['site'] = $request->getSite();

        $order = new Order($requestArray);
        # new order taxonomy ID
        $order->status_taxonomy_id = Config::getOrFail('taxonomies.order_statuses.new_order.id');
        $order->saveOrFail();

        try {
            $entityProperties = ['elements' => CartElementEntity::hydrate($requestArray['items'])];
            if (isset($requestArray['familyComboSelections'])) {
                $entityProperties['familyComboSelections'] = $requestArray['familyComboSelections'];
            }
            $requestArray['items'] = (new CartEntity($entityProperties))->update(true)->elements;

            foreach ($requestArray['items'] as $itemData) {
                $itemAttributes = [
                    'order_id' => $order->id,
                    'order_itemable_type' => $itemData->orderItemableType,
                    'order_itemable_id' => $itemData->orderItemableId,
                    'order_itemable_index' => $itemData->orderItemableIndex,
                    'from_date' => $itemData->interval['date_from'],
                    'to_date' => $itemData->interval['date_to'],
                    'amount' => $itemData->amount,
                    'meal_plan_id' => MealPlan::findByName($itemData->mealPlan)->id,
                    'price' => $itemData->calculatedPrice['discounted_price'],
                    'margin' => $itemData->calculatedPrice['margin'],
                    'json' => \json_encode($itemData)
                ];
                $item = (new OrderItemSetter($itemAttributes))->set();
                $guestIndex = 0;
                switch ($item->order_itemable_type) {
                    case Device::class:
                        $ageRangeableType = $item->orderItemable->deviceable_type;
                        $ageRangeableId = $item->orderItemable->deviceable_id;
                        if (
                            $item->orderItemable->deviceable_type == Organization::class
                            && ($accommodation = Accommodation::find($item->orderItemable->deviceable_id))
                        ) {
                            $channelManagerService = app('channel_manager')->fetch($accommodation);
                            if ($channelManagerService && $channelManagerService->isValid) {
                                $channelManagerService->update();
                            }
                        }
                        break;
                    case ShipGroup::class:
                        $ageRangeableType = ShipGroup::class;
                        $ageRangeableId = $item->orderItemable->id;
                        break;
                    default:
                        throw new UserException('Unsupported type for Order item');
                }
                if (!empty($itemData->guests)) {
                    foreach ($itemData->guests as $guestData) {
                        $this->saveGuest($guestData, $item, $guestIndex++, $ageRangeableType, $ageRangeableId);
                    }
                }
            }
        } catch (Exception $e) {
            $order->delete();
            throw $e;
        }

        $order->load(['items']);
        return $order;
    }


    /**
     * saveGuest
     * Save a guest
     * @param array $guestData
     * @param OrderItem $item
     * @param int $guestIndex
     * @param  string $ageRangeableType
     * @param  int $ageRangeableId
     * @return OrderItemGuest
     * @throws UserException
     * @throws \Throwable
     */
    private function saveGuest(
        array $guestData,
        OrderItem $item,
        int $guestIndex,
        string $ageRangeableType,
        int $ageRangeableId
    ): OrderItemGuest {
        try {
            $guest = new OrderItemGuest([
                'order_item_id' => $item->id,
                'guest_index' => $guestIndex,
                'age_range_id' => !empty($guestData['age_range']) ? AgeRange::findByNameOrFail($guestData['age_range'],
                    $ageRangeableType, $ageRangeableId)->id : null,
                'first_name' => $guestData['first_name'],
                'last_name' => $guestData['last_name']
            ]);
            $guest->saveOrFail();
        } catch (ErrorException $e) {
            throw new UserException($e->getMessage());
        }
        return $guest;
    }
}
