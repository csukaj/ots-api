<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait PaymentDeveloperCapabilities
{
    public function details(Request $request)
    {
        $orderToken = $request->get('token');
        $payment = app('payment')->details($orderToken);

        if ($payment->hasError())
        {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        $result = $payment->getResult();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function cancel(Request $request)
    {
        $orderId = $request->get('order_id');
        $payment = app('payment')->cancel($orderId);

        if ($payment->hasError())
        {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function charge(Request $request)
    {
        $orderToken = $request->get('token');
        $payment = app('payment')->charge($orderToken);

        if ($payment->hasError())
        {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

}