<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController
{
    # Create invoice
    public function create(Request $request)
    {
        # @todo @ivan @20180711 - id helyett majd token kell!
        $orderId = $request->get('order_id');

        $billing = app('billing')->create($orderId);

        if ($billing->hasError())
        {
            return response()->json([
                'success' => false,
                'data' => $billing->getErrorMessages()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
}