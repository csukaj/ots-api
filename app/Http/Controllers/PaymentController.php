<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\PaymentDeveloperCapabilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController
{
    use PaymentDeveloperCapabilities;

    public function create(Request $request): JsonResponse
    {
        $token = $request->get('token');
        $payment = app('payment')->create($token);

        if ($payment->hasError()) {
            return response()->json([
                'success' => false,
                'data' => $payment->getErrorMessages()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $payment->getResult()
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $token = $request->get('token');
        $payment = app('payment')->status($token);

        if ($payment->hasError()) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        $result = $payment->getResult();

        return response()->json([
            'success' => true,
            'data' => [
                'orderId' => $result['orderId'],
                'paymentOrderId' => $result['paymentOrderId'],
                'status' => $result['status']
            ]
        ]);
    }

    public function notification(Request $request): JsonResponse
    {
        $payment = app('payment')->notification($request->all());

        /**
         * @todo @ivan - itt majd kell donteni, hogy mi legyen a policy: ha belsoleg van
         * valami hiba, akkor adjunk vissza egy failed valaszt, ami a limonetik szamara azt
         * jelenti, hogy probalkozzon ujra, vagy ha belso hiba van, akkor ne probalja ujra hivni
         */
        if ($payment->hasError()) {
            return response()->json(['success' => true], 400);
        }

        return response()->json(['success' => true], 200);
    }

    public function pay(Request $request): JsonResponse
    {
        $token = $request->get('token');

        # Itt meg kell nezni, hogy egyaltalan letezik e az az order token, amivel be akarjak hivni
        $order = app('payment')->loadOrderByToken($token);

        # Ha nem valos megrendeles token, vagy a megrendelés már végigment
        # vissza kell dobni frontend oldalon a fooldalra
        # ha a payment_closed = true, a fizetesi folyamat mar vegigment, nem szabad ujra elinditani.
        if (is_null($order) || $order->payment_closed) {
            return response()->json([
                'success' => false,
                'data' => [
                    'redirectToMainPage' => true
                ]
            ]);
        }

        # Ha atmentunk a szurokon, akkor johet a tenyleges fizetes
        $payment = app('payment')->pay($token);

        if ($payment->hasError()) {
            return response()->json([
                'success' => false,
                'data' => $payment->getErrorMessages()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
}