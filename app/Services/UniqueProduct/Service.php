<?php

namespace App\Services\UniqueProduct;

use App\Cart;
use App\Entities\UniqueProductCartEntity;
use App\Facades\Config;
use App\Manipulators\CartSetter;
use App\Manipulators\OrderSetter;
use App\Services\OrderStatusHandlerService;
use App\UniqueProduct;

class Service extends \App\Services\Service
{
    protected $cart;

    protected $order;

    /**
     * Service constructor.
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function list(): self
    {
        $carts = Cart::orderBy('id', 'DESC')->get();
        $this->result = UniqueProductCartEntity::getCollection($carts);

        return $this;
    }

    public function show(int $cartId): self
    {
        $cart = Cart::findOrFail($cartId);
        $this->result = (new UniqueProductCartEntity($cart))->getFrontendData();

        return $this;
    }

    /**
     * @param array $cartData
     * @param string $saveType
     * @return Service
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    public function save(array $cartData, string $saveType): self
    {
        $cart = (new CartSetter($cartData, $saveType))->set();

        if ($saveType == 'sent') {
            $this->createOrderFromCart($cartData, $cart->uniqueProducts()->get());

            $statusHandler = new OrderStatusHandlerService();
            $statusHandler->setOrder($this->order);
            $statusHandler->stepStatus('NEW_UNIQUE_PRODUCT_ORDER');
        }

        return $this;
    }

    protected function createOrderFromCart(array $cartData, $uniqueProducts)
    {
        $billingTypeTaxonomy = $cartData['type'] == 'company'
            ? Config::getOrFail('taxonomies.billing_types.company')
            : Config::getOrFail('taxonomies.billing_types.individual');

        # Eloszor osszeallitjuk az ordert
        $orderData = [
            'first_name' => !empty($cartData['first_name']) ? $cartData['first_name'] : '',
            'last_name' => !empty($cartData['last_name']) ? $cartData['last_name'] : '',
            'company_name' => !empty($cartData['company_name']) ? $cartData['company_name'] : '',
            'tax_number' => !empty($cartData['tax_number']) ? $cartData['tax_number'] : '',
            'nationality' => strtoupper($cartData['country']),
            'email' => $cartData['email'],
            'telephone' => $cartData['phone'],
            'remarks' => '',
            'status_taxonomy_id' => Config::getOrFail('taxonomies.order_statuses.confirmed.id'),
            'status_log' => '',
            'token' => '',
            'token_created_at' => date('Y-m-d H:i:s'),
            'payment_closed' => 0,
            'billing_country' => $cartData['country'],
            'billing_zip' => $cartData['zip'],
            'billing_settlement' => $cartData['city'],
            'billing_address' => $cartData['address'],
            'billing_type_taxonomy_id' => $billingTypeTaxonomy,
            'site' => $cartData['site'],
        ];

        # Elmentjuk order item-ek nelkul, mert szuksegunk lesz a uniqueProduct-okhoz az orderId-ra
        $orderSetter = new OrderSetter($orderData);
        $this->order = $orderSetter->set();
        # Beallitjuk az order-nek az ID-t
        $orderData['id'] = $this->order->id;

        # Itt szedjuk ossze az egyedi termekeket.
        $orderItems = [];

        foreach ($uniqueProducts as $uniqueProduct) {
            $orderItem = [
                'order_id' => $this->order->id,
                'order_itemable_id' => $uniqueProduct['id'],
                'from_date' => $uniqueProduct['from_date'],
                'to_date' => $uniqueProduct['to_date'],
                'amount' => $uniqueProduct['amount'],
                # @todo @ivan - Az order_items tabla ugy lett megtervezve, hogy a meal_plan_id a resze, ezert a workaround
                'meal_plan_id' => 1,
                # @todo @ivan - irrelevans a uniqe product szempontjabol, igy drotozva van
                'order_itemable_index' => 0,
                'price' => $uniqueProduct['net_price'],
                'order_itemable_type' => UniqueProduct::class,
                'margin' => $uniqueProduct['margin'],
                'tax' => $uniqueProduct['tax']
            ];
            $orderItem['json'] = json_encode(
                array_merge(
                    [
                        'productableType' => 'App\\UniqueProduct',
                        'productableModel' => [
                            'id' => $uniqueProduct['id']
                        ],
                    ],
                    $orderItem
                )
            );
            $orderItems[] = $orderItem;
        }
        # Hozzaadjuk az item-eket is
        $orderData['order_items'] = $orderItems;

        # Ismet setteljuk, de most mar update cellal.
        $orderSetter = new OrderSetter($orderData);
        $this->order = $orderSetter->set();

        return $this;
    }

    public function delete(int $cartId): self
    {
        $cart = Cart::findOrFail($cartId);

        $draftStatusId = Config::getOrFail('taxonomies.cart_statuses.draft');
        $currentStatusId = $cart->status->id;

        # Csak akkor torolheto, ha draft statuszban van!
        if ($currentStatusId != $draftStatusId) {
            $message = 'This cart not in draft status, you cannot delete it!';
            $this->addError($message);

            return $this;
        }

        UniqueProduct::where('cart_id', $cartId)->delete();
        $cart->delete();

        return $this;
    }
}