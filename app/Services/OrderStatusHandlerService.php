<?php

namespace App\Services;

use App\Events\Order\ClosedStatusEvent;
use App\Events\Order\ConfirmedStatusEvent;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\NewUniqueProductOrderStatusEvent;
use App\Events\Order\OfferUnderProcessingStatusEvent;
use App\Events\Order\PayingStatusEvent;
use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use App\Facades\Config;
use App\Order;

class OrderStatusHandlerService
{
    protected $request;
    protected $order;
    private $orderStatuses;

    /**
     * @var Site where we are
     */
    protected $site;

    public function __construct($request = null)
    {
        $this->request = $request;
        $this->setOrderStatuses();
    }

    public function stepStatus($targetStatus): self
    {

        if (!in_array($targetStatus, $this->orderStatuses)) {
            throw new \Exception('Bad stepStatus: ' . $targetStatus);
        }

        switch ($targetStatus) {
            case 'NEW_ORDER': //validation, save order, set availability, auto set status: waiting_for_offer, status log
                event(new NewOrderStatusEvent($this->request));
                break;

            case 'WAITING_FOR_OFFER': //email: user(thank you, it's doing), ad(you have a new task), status log
                event(new WaitingForOfferStatusEvent($this->order));
                break;

            case 'OFFER_UNDER_PROCESSING': //status log
                event(new OfferUnderProcessingStatusEvent($this->request));
                break;

            case 'CONFIRMED': //email: user(with payment option), status log
                event(new ConfirmedStatusEvent($this->request));
                break;

            case 'CLOSED': //email: user(sorry, we don't able to help), status log
                event(new ClosedStatusEvent($this->request));
                break;

            case 'NEW_UNIQUE_PRODUCT_ORDER': //validation, save order, set availability, auto set status: waiting_for_offer, status log
                event(new NewUniqueProductOrderStatusEvent($this->order));
                break;

            case 'PAYING': //limonetik, save limonetik response, auto set status: payment_success or payment_failed, status log
                event(new PayingStatusEvent($this->request));
                break;

            case 'PAYMENT_SUCCESS': //response to user about the success payment, set availability, empty cart, email: user(payment is success and details), status log
                event(new PaymentSuccessStatusEvent($this->order, $this->site));
                break;

            case 'PAYMENT_FAILED': //response to user about the failed payment, email: user(payment is failed), status log
                event(new PaymentFailedStatusEvent($this->order, $this->site));
                break;
        }

        if ($targetStatus !== 'NEW_ORDER') {
            $orderId = is_a($this->order, Order::class) ? $this->order->id : $this->request->model['id'];
            Order::setStatus($orderId, $this->getOrderStatusIdByName($targetStatus));
            (new OrderStatusLogger((object)['id' => $orderId], $targetStatus))
                ->addLog(['date' => date('Y-m-d H:i:s'), 'status' => $targetStatus]);
        }

        return $this;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Site setter
     *
     * @param $site
     * @return OrderStatusHandlerService
     */
    public function setSite($site): self
    {
        $this->site = $site;
        return $this;
    }

    private function setOrderStatuses()
    {
        $this->orderStatuses = [];
        foreach (Config::getOrFail('taxonomies.order_statuses') as $statusKey => $orderStatus) {
            $this->orderStatuses[$orderStatus['id']] = strtoupper($statusKey);
        }
    }

    private function getOrderStatusIdByName(string $statusName): int
    {
        $statusNames = array_flip($this->orderStatuses);
        return $statusNames[$statusName];
    }
}
