<?php

namespace Tests;

use App\AgeRange;
use App\Facades\Config;
use App\Http\Requests\EmbeddedOrderSendRequest;
use App\Http\Requests\OrderSendRequest;
use App\Order;
use App\OrderItem;
use App\OrderItemGuest;
use App\Organization;
use App\Services\OrderStatusHandlerService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

trait OrderTestTrait
{
    private function prepareSampleOrder($withToken = false): Order
    {
        $token = sha1(base64_encode(str_random(40)));
        $attributes = $withToken ? ['token' => $token, 'token_created_at' => Carbon::now()] : [];

        return factory(Order::class, 1)->create($attributes)->each(function ($order) {
            $item = factory(OrderItem::class)->create([
                'order_id' => $order->id
            ]);
            $ageRange = factory(AgeRange::class)->create([
                'age_rangeable_type' => Organization::class,
                'age_rangeable_id' => $item->orderItemable->deviceable->id,
            ]);
            factory(OrderItemGuest::class)->create([
                'order_item_id' => $item->id,
                'age_range_id' => $ageRange->id,
            ]);

        })->first();
    }

    /**
     * @param string $eventClass
     * @param $requestOrOrder
     * @param Order|null $orderToSet
     * @throws \Exception
     */
    protected function assertEventFired(string $eventClass, $requestOrOrder, Order $orderToSet = null)
    {
        $this->expectsEvents($eventClass);

        $service = (new OrderStatusHandlerService($requestOrOrder));
        if ($orderToSet) {
            $service->setOrder($orderToSet);
        }
        $service->stepStatus(self::toSnakeCase($eventClass));

        /**
         * Or if we need to test the event itself
         * Event::assertDispatched($eventClass, function ($event) {
         * return $event->customer->id === $this->customer->id;
         * });
         */
    }

    protected function assertStatusUpdated(Order $order, string $eventClass)
    {
        $stepStatusName = self::toSnakeCase($eventClass, true);
        $this->assertEquals(Config::getOrFail('taxonomies.order_statuses.' . $stepStatusName . '.id'), $order->status_taxonomy_id);
    }

    protected function assertLogWritten(Order $order, string $eventClass, $anywhere = false)
    {
        $expected = (object)[
            'date' => date('Y-m-d H:i:s'),
            'status' => self::toSnakeCase($eventClass)
        ];
        $logEntries = json_decode($order->status_log);
        if (!$anywhere) {
            $lastLine = last($logEntries);
            $this->assertTrue($this->logLineEquals($expected, $lastLine));
        } else {
            $found = false;
            foreach ($logEntries as $line) {
                if ($this->logLineEquals($expected, $line)) $found = true;
            }
            $this->assertTrue($found);
        }
    }

    private function logLineEquals($expected, $actual)
    {
        $equals = substr($expected->date, 0, 18) === substr($actual->date, 0, 18);
        if (!empty($expected->status) && isset($actual->status)) {
            $equals = $equals && ($expected->status === $actual->status);
        }
        if (!empty($expected->events) && isset($actual->events)) {
            $equals = $equals && ($expected->events === $actual->events);
        }
        return $equals;
    }


    protected function createRequest(
        $method,
        $content,
        $uri = '/test',
        $server = ['CONTENT_TYPE' => 'application/json'],
        $parameters = [],
        $cookies = [],
        $files = [],
        $embeddedRq = true
    )
    {
        if ($embeddedRq) {
            return EmbeddedOrderSendRequest::createFromBase(
                Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
            );
        }
        return OrderSendRequest::createFromBase(
            Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        );
    }


    static protected function toSnakeCase(string $eventClass, $toLower = false): string
    {
        $name = str_replace('_STATUS_EVENT', '', strtoupper(snake_case(class_basename($eventClass))));
        return !$toLower ? $name : strtolower($name);
    }
}