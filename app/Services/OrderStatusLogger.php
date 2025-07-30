<?php
namespace App\Services;


use App\Order;

class OrderStatusLogger
{
    protected $order;

    public function __construct($orderData, $status = '')
    {
        $this->order = $orderData;
    }

    /**
     * @param $obj
     */
    public function addLog($obj) {
        $order = Order::findOrFail($this->order->id);
        $availableLog = $order->status_log;
        $log = [];
        if (!empty($availableLog)) {
            $log = json_decode($availableLog);
        }
        $log[] = $obj;
        $order->status_log = json_encode($log);
        $order->save();
    }

}
