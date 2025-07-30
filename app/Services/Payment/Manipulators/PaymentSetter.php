<?php

namespace App\Services\Payment\Manipulators;

use App\Exceptions\UserException;
use App\Payment;

class PaymentSetter
{
    /**
     * Attributes that can be set from input
     */
    protected $attributes = [
        'id' => null,
        'parent_id' => null,
        'payment_order_id' => null,
        'supplier_id' => null,
        'request_id' => '',
        'order_id' => null,
        'status' => '',
    ];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
        //TODO: input validation with validator facade
        foreach (['id', 'parent_id', 'order_id', 'supplier_id'] as $numericField) {
            if (isset($attributes[$numericField]) && (!is_numeric($attributes[$numericField])|| $attributes[$numericField] < 0)) {
                throw new UserException('bad input data for field: '.$numericField);
            }
        }
        foreach (['request_id', 'status'] as $stringField) {
            if (isset($attributes[$stringField]) && !is_string($attributes[$stringField])) {
                throw new UserException('bad input data for field: '.$stringField);
            }
        }
    }

    public function set(): Payment
    {
        if (is_null($this->attributes['id'])) {
            $payment = new Payment();
            $payment->payment_order_id = $this->attributes['payment_order_id'];
            $payment->order_id = $this->attributes['order_id'];
            $payment->request_id = $this->attributes['request_id'];
            $newStatus = [
                date('Y-m-d H:i:s') => [
                    'status' => 'Created'
                ]
            ];
            $payment->status_log = json_encode($newStatus);

            if (!is_null($this->attributes['parent_id'])) {
                $payment->parent_id = $this->attributes['parent_id'];
            }

            if (!is_null($this->attributes['supplier_id'])) {
                $payment->supplier_id = $this->attributes['supplier_id'];
            }
        } else {
            //setter can only update status, other properties must not modified
            $payment = Payment::findOrFail($this->attributes['id']);
            $previousStates = json_decode($payment->status_log, true);
            $newStatus = [
                date('Y-m-d H:i:s') => [
                    'status' => $this->attributes['status']
                ]
            ];
            $payment->status_log = json_encode(array_merge($previousStates, $newStatus));
        }

        $payment->save();

        return $payment;
    }
}