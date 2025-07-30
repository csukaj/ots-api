<?php

namespace App\Http\Controllers;

use App\Manipulators\OrderSetter;
use App\Entities\OrderEntity;
use App\Http\Requests\OrderSendRequest;
use App\Order;
use App\Services\OrderStatusHandlerService;

use Exception;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Facades\Config;

/**
 * @resource OrderController
 */
class OrderController extends Controller
{
    /**
     * send
     * Send an order
     * @param OrderSendRequest $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Throwable
     */
    public function send(OrderSendRequest $request): JsonResponse
    {
        $request->merge(['site' => $request->getSite()]);

        if ($request->get('token'))
        {
            $orderToken = $request->get('token');
            $order = Order::getOrderByToken($orderToken);
            $request->merge(['id' => $order->id]);
            // @todo @ivan - itt ha nem talalunk ordert a token alapjan, akkor uj order jon letre. Jo ez igy?

            // @todo @ivan - nem mehet be 1:1-ben a request, mert tartalmazhat olyan adatot, amit nem szeretnenk kivulrol allitani
            (new OrderSetter($request->all(), true))->set();

            $orderCurrentStatus = $order->status_taxonomy_id;
            $confirmedStatus = Config::getOrFail('taxonomies.order_statuses.confirmed.id');
            $newUniqueProductOrderStatus = Config::getOrFail('taxonomies.order_statuses.new_unique_product_order.id');

            if (in_array($orderCurrentStatus, [$newUniqueProductOrderStatus, $confirmedStatus]))
            {
                (new OrderStatusHandlerService($request))
                    ->setOrder($order)
                    ->stepStatus('PAYING');

                $order->payment_closed = false;
                $order->save();

                $payment = app('payment')->create($orderToken);

                if ($payment->hasError())
                {
                    return response()->json([
                        'success' => false,
                        'data' => $payment->getErrorMessages()
                    ]);
                }

                $data = [
                    'paymentPageUrl' => $payment->getResult()['paymentPageUrl']
                ];
            }
            else
            {
                (new OrderStatusHandlerService($request))->stepStatus('NEW_ORDER');
                $data = [];
            }
        }
        else
        {
            (new OrderStatusHandlerService($request))->stepStatus('NEW_ORDER');
            $data = [];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'request' => $request->all()
        ]);
    }

    public function getByToken(Request $request, $token)
    {
        $orderModel = Order::getOrderByToken($token);

        if ($orderModel) {
            $order = (new OrderEntity($orderModel))->getFrontendData(['parse_json']);
            $message = '';
        } else {
            $order = false;
            $message = "The token is invalid or has expired.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $order,
            'request' => $request->all()
        ]);
    }
}
