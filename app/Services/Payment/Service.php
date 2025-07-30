<?php

namespace App\Services\Payment;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Order;
use App\Payment;
use App\Services\ErrorsTrait;
use App\Services\OrderStatusHandlerService;
use App\Services\Payment\Limonetik\Service as LimonetikService;
use App\Services\Payment\Manipulators\PaymentSetter;
use Illuminate\Database\Eloquent\Collection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Payment service
 *
 * This service handle the payment logic and wrap the Limonetik's API
 *
 * @package App\Services\Payment
 */
class Service
{
    use ErrorsTrait;

    /**
     * @var Order model
     */
    protected $order = null;

    /**
     * @var string Site language
     */
    protected $siteLanguage;

    /**
     * @var array Payment config settings
     */
    protected $config = [];

    /**
     * @var array Limonetik config
     */
    protected $limonetikConfig = [];

    /**
     * @var Object Payment logger
     */
    protected $logger = null;

    /**
     * @var array Process result array
     */
    protected $result = [];

    private $limonetikService = null;

    /**
     * Service constructor.
     *
     * @param Order $order
     * @param array $config
     * @throws \Exception
     */
    public function __construct(
        Order $order,
        array $config
    )
    {
        $this->order = $order;
        $this->config = $config;
        $this->limonetikConfig = $config['limonetik'];

        $this->initLogger();
    }

    /**
     * Initialize the payment logger
     *
     * @throws \Exception
     */
    protected function initLogger()
    {
        $this->logger = new Logger('payment');
        $streamHandler = new StreamHandler($this->config['log']);
        $this->logger->pushHandler($streamHandler);
    }

    /**
     * Set and store the payment
     *
     * @param int $orderId
     * @param int $paymentOrderId
     * @param string $requestId
     * @param string $parentId
     * @param int $supplierId
     */
    protected function storePayment(int $orderId, int $paymentOrderId, string $requestId, string $parentId = '', int $supplierId = 0)
    {
        $paymentDetails = [
            'order_id' => $orderId,
            'payment_order_id' => $paymentOrderId,
            'request_id' => $requestId
        ];
        if ($parentId != '') {
            $paymentDetails['parent_id'] = $parentId;
        }
        if ($supplierId != 0) {
            $paymentDetails['supplier_id'] = $supplierId;
        }
        (new PaymentSetter($paymentDetails))->set();
    }

    /**
     * Update the payments status
     *
     * @param int $id
     * @param string $status
     */
    protected function updatePaymentStatus(int $id, string $status)
    {
        $paymentDetails = [
            'id' => $id,
            'status' => $status
        ];
        (new PaymentSetter($paymentDetails))->set();
    }

    /**
     * Validate the orderId format and after the success validation load the order
     *
     * @param int $orderId
     * @return mixed
     */
    /* not used
     * public function loadOrder($orderId = null)
    {
        if (is_null($orderId))
        {
            $message = 'order_id is null';
            $this->addError($message);
            return;
        }
        if (!is_numeric($orderId))
        {
            $message = 'order_id must be numeric';
            $this->addError($message);
            return;
        }

        $order = $this->order->find($orderId);

        if ($order === null)
        {
            $message = 'Order with "' . $orderId . '" is not found';
            $this->addError($message);
            return;
        }

        $this->setSiteLanguage($order->site);

        return $order;
    }*/

    /**
     * Validate the orderToken format and after the success validation load the order
     *
     * @param string $orderToken
     * @param array $options Options for order loading
     *
     * @return mixed
     */
    public function loadOrderByToken(string $orderToken, array $options = [])
    {
        if (!is_string($orderToken)) {
            $message = 'token must be string';
            $this->addError($message);

            return null;
        }

        $order = Order::getOrderByToken($orderToken, $options);

        if ($order === null) {
            $message = 'Order with "' . $orderToken . '" token is not found';
            $this->addError($message);

            return null;
        }

        $this->setSiteLanguage($order->site);

        return $order;
    }

    /**
     * @param string $orderToken
     * @return mixed
     */
    public function loadOrderByTokenNoRestriction(string $orderToken)
    {
        return $this->loadOrderByToken(
            $orderToken, [
                'checkPaymentClosed' => false,
                'checkTokenCreatedAt' => false
            ]
        );
    }

    /**
     * Set the language by querying site
     *
     * @param string $site
     */
    protected function setSiteLanguage(string $site)
    {
        $siteLanguages = config('ots.site_languages');
        $this->siteLanguage = $siteLanguages[$site];
    }

    /**
     * Function to set service (for testability) without using real limonetik service.
     * @param $service
     * @return Service
     */
    public function setService($service): self
    {
        $this->limonetikService = $service;
        return $this;
    }

    /**
     * Get service. For working code it returns limonetik service, for test purposes it returns mock service "injected"
     * by setService()
     * @return LimonetikService|null
     */
    protected function getService()
    {
        if (!$this->limonetikService) {
            $this->limonetikService = new LimonetikService($this->limonetikConfig, $this->siteLanguage);
        }
        return $this->limonetikService;
    }

    /**
     * Create (start) a payment
     *
     * @param string $orderToken
     * @return Service
     */
    public function create(string $orderToken): self
    {
        # Ellenorizzuk es loadoljuk az order-t
        $order = $this->loadOrderByToken($orderToken);
        if ($this->hasError()) {
            return $this;
        }
        $order->payment_closed = false;
        $order->save();

        # Logoljuk, hogy a limonetik-hez bekerdezunk
        $message = '[CREATE][START] [OrderId: ' . $order->id . ']';
        $this->logger->info($message);

        # Meghivjuk a limonetik service-t
        $limonetik = $this->getService();
        $limonetik->setLogger($this->logger);
        $limonetik->create($order);

        # Ha hiba volt a limonetik kommunikacioban, akkor logoljuk
        if ($limonetik->hasError()) {
            $this->logger->error($limonetik->getErrors('json'));
            $this->addErrors($limonetik->getErrors());

            return $this;
        }

        # Kikerjuk az eredmenyt
        $limonetikResult = $limonetik->getResult();

        # Logoljuk a limonetik-tol kapott valasz relevans reszeit
        $message = '[CREATE][FINISH] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $limonetikResult['paymentOrderId'] . ']';
        $this->logger->info($message);

        # Elmentjuk az adatbazisba az adatokat
        $this->storePayment(
            $order->id,
            $limonetikResult['paymentOrderId'],
            $limonetikResult['requestId']
        );

        # Osszeszedjuk a megfelelo adatokat
        $this->result = [
            'paymentOrderId' => $limonetikResult['paymentOrderId'],
            'paymentPageUrl' => $limonetikResult['paymentPageUrl']
        ];

        return $this;
    }

    /**
     * It get the details of the payment.
     *
     * @param string $orderToken
     * @return Service
     */
    public function details(string $orderToken): self
    {
        # Ellenorizzuk es loadoljuk az order-t
        $order = $this->loadOrderByTokenNoRestriction($orderToken);
        if ($this->hasError()) {
            return $this;
        }
        $payment = $order->payment;

        # Logoljuk, hogy a limonetik-hez bekerdezunk
        $message = '[DETAILS][START] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $payment->payment_order_id . ']';
        $this->logger->info($message);

        # Meghivjuk a limonetik service-t
        $limonetik = $this->getService();
        $limonetik->setLogger($this->logger);
        $limonetik->detailByPaymentOrderId($payment->payment_order_id);

        # Ha hiba volt a limonetik kommunikacioban, akkor logoljuk
        if ($limonetik->hasError()) {
            $this->logger->error($limonetik->getErrors('json'));
            $this->addErrors($limonetik->getErrors());

            return $this;
        }

        # Kikerjuk az eredmenyt
        $limonetikResult = $limonetik->getResult();

        # Logoljuk a limonetik-tol kapott valasz relevans reszeit
        $message = '[DETAILS][FINISH] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $payment->payment_order_id . ']';
        $this->logger->info($message);

        # Frissitjuk a statuszt
        $this->updatePaymentStatus(
            $payment->id,
            $limonetikResult['paymentOrder']['Status']
        );

        # Osszeszedjuk a megfelelo adatokat
        $this->result = [
            'orderId' => $order->id,
            'paymentOrderId' => $limonetikResult['paymentOrder']['Id'],
            'status' => $limonetikResult['paymentOrder']['Status'],
            'paymentOrder' => $limonetikResult['paymentOrder']
        ];

        return $this;
    }

    /**
     * Wrapper function of the details
     *
     * @param string $orderToken
     * @return Service
     */
    public function status(string $orderToken): self
    {
        return $this->details($orderToken);
    }

    /**
     * It settle all merchants money in an order.
     *
     * (Pay means in limonetik's terminology: "settle money for a merchant".)
     *
     * @param string $orderToken
     * @return $this
     */
    public function pay(string $orderToken): self
    {
        # Ellenorizzuk es loadoljuk az order-t
        $order = $this->loadOrderByToken($orderToken);
        if ($this->hasError()) {
            return $this;
        }
        $payment = $order->payment;
        $paymentOrderId = $payment->payment_order_id;

        # Eloszor osszeszedjuk vendor/merchant szinten, hogy osszesen mennyit kell fizetni majd
        $orderItems = $order->items;

        # Tombbe osszegyujtjuk limonetik merchant ID kulccsal a hozzajuk tartozo item-eket
        $merchantItems = $this->getMerchantItems($orderItems);

        foreach ($merchantItems as $limonetikMerchantId => $descriptor) {
            # Logoljuk, hogy a limonetik-hez bekerdezunk
            $message = '[PAYING][START] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $paymentOrderId . '] [Limonetik merchantId: ' . $limonetikMerchantId . ']';
            $this->logger->info($message);

            # Meghivjuk a limonetik service-t
            $limonetik = $this->getService();
            $limonetik->setLogger($this->logger);
            $limonetik->pay($limonetikMerchantId, $order, $descriptor['items']);

            # Ha hiba volt a limonetik kommunikacioban, akkor logoljuk
            if ($limonetik->hasError()) {
                $this->logger->error($limonetik->getErrors('json'));
                $this->addErrors($limonetik->getErrors());
            }

            # Kikerjuk az eredmenyt
            $limonetikResult = $limonetik->getResult();

            if (!empty($limonetikResult['paymentOrderId'])) {
                # Logoljuk a limonetik-tol kapott valasz relevans reszeit
                $message = '[PAYING][FINISH] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $limonetikResult['paymentOrderId'] . '] [Limonetik merchantId: ' . $limonetikMerchantId . ']';
                $this->logger->info($message);

                # Elmentjuk az adatbazisba az adatokat
                $this->storePayment(
                    $order->id,
                    $limonetikResult['paymentOrderId'],
                    $limonetikResult['requestId'],
                    $payment->id,
                    $descriptor['supplierId']
                );
            }
        }

        $orderStatus = new OrderStatusHandlerService();
        $orderStatus->setOrder($order);
        $orderStatus->setSite($order->site);

        if (!$this->hasError()) {
            $order->payment_closed = true;
            $order->save();

            $orderStatus->stepStatus('PAYMENT_SUCCESS');
        } else {
            $orderStatus->stepStatus('PAYMENT_FAILED');
        }

        return $this;
    }

    /**
     * Charge all order item in an order
     *
     * @param string $orderToken
     * @return $this
     * @throws \Exception
     */
    public function charge(string $orderToken): self
    {
        # Ellenorizzuk es loadoljuk az order-t
        $order = $this->loadOrderByTokenNoRestriction($orderToken);

        if ($this->hasError()) {
            $this->logger->error($this->getErrors('json'));

            return $this;
        }
        $payment = $order->payment;
        $paymentOrderId = $payment->payment_order_id;

        # Eloszor osszeszedjuk vendor/merchant szinten, hogy osszesen mennyit kell fizetni majd
        $orderItems = $order->items;

        # Tombbe osszegyujtjuk limonetik merchant ID kulccsal a hozzajuk tartozo item-eket
        $merchantItems = $this->getMerchantItems($orderItems);

        foreach ($merchantItems as $limonetikMerchantId => $descriptor) {
            $subPayment = Payment::where(
                [
                    ['order_id', '=', $order->id],
                    ['supplier_id', '=', $descriptor['supplierId']]
                ]
            )->orderBy('created_at', 'desc')
                ->first();


            # Logoljuk, hogy a limonetik-hez bekerdezunk
            $message = '[CHARGE][START] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $paymentOrderId . '] [Limonetik merchantId: ' . $limonetikMerchantId . ']';
            $this->logger->info($message);

            # Meghivjuk a limonetik service-t
            $limonetik = $this->getService();
            $limonetik->setLogger($this->logger);
            $limonetik->charge($subPayment, $descriptor['items']);

            # Ha hiba volt a limonetik kommunikacioban, akkor logoljuk
            if ($limonetik->hasError()) {
                $this->logger->error($limonetik->getErrors('json'));
                $this->addErrors($limonetik->getErrors());
            }

            # Logoljuk a limonetik-tole kapott valasz relevans reszeit
            $message = '[CHARGE][END] [OrderId: ' . $order->id . '] [PaymentOrderId: ' . $paymentOrderId . '] [Limonetik merchantId: ' . $limonetikMerchantId . ']';
            $this->logger->info($message);
        }

        return $this;
    }

    public function notification($params): self
    {
        # Logoljuk, hogy erkezett egy notification
        $message = '[NOTIFICATION FROM LIMONETIK] Data: ' . json_encode($params);
        $this->logger->info($message);

        # Load-oljuk az order-t
        $orderToken = $params['o'];
        $order = $this->loadOrderByTokenNoRestriction($orderToken);

        # Megkerjuk a statuszat a rendelesnek
        $details = $this->details($orderToken);

        # Amennyiben autorized a statusza, mindenkeppen mennie kell a charge-nak
        if ($details->result['status'] == 'Authorized') {
            $this->charge($order->token);

            return $this;
        }

        # Ha mar le van zarva, akkor nincs vele teendo!
        if ($order->payment_closed) {
            $message = '[NOTIFICATION EVENT STOPPED] because OrderId "' . $order->id;
            $message .= '" (PaymentOrder Id: "' . $order->payment->payment_order_id . '")';
            $message .= ' is closed, last status: "' . $details->result['status'] . '"';
            $this->logger->info($message);

            return $this;
        }

        # Ha nincs lezarva es nem "authorized", akkor az alabbi statuszokat kezeljuk
        switch ($details->result['status']) {
            case 'Refused':
            case 'Aborted':
                $orderStatus = new OrderStatusHandlerService();
                $orderStatus->setOrder($order);
                $orderStatus->setSite($order->site);
                $orderStatus->stepStatus('PAYMENT_FAILED');

                break;
        }

        return $this;
    }

    /**
     * After a process (create, cancel etc.) ran without error this error will be filled with result data
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * It creates a limonetik merchant ID keyed array with orderItems
     *
     * @param object $orderItems Collection of App\OrderItem
     * @throws \Exception
     */
    protected function getMerchantItems(Collection $orderItems): array
    {
        $merchantItems = [];

        foreach ($orderItems as $orderItem) {
            $productableModel = $orderItem->productableModel();
            $productableId = $productableModel->id;
            $productableClass = $orderItem->productableType();
            $product = $productableClass::find($productableId);

            $supplier = $product->supplier;
            if(!$supplier){
                throw new UserException('No supplier associated to this product!');
            }

            $limonetikTxId = Config::getOrFail('taxonomies.contact_types.limonetik_merchant_id');
            $limonetikMerchantIdObject = $supplier
                ->contacts
                ->where('type_taxonomy_id', $limonetikTxId);
            if (is_null($limonetikMerchantIdObject)) {
                $supplierName = $supplier->name->description;
                $message = 'Limonetik merchant ID missing from "' . $supplierName . '" supplier';
                throw new \Exception($message);
            }

            $limonetikMerchantId = $limonetikMerchantIdObject->first()->value;

            if (!array_key_exists($limonetikMerchantId, $merchantItems)) {
                $merchantItems[$limonetikMerchantId] = [
                    'supplierId' => $supplier->id,
                    'items' => []
                ];
            }
            $merchantItems[$limonetikMerchantId]['items'][] = $orderItem;
        }

        return $merchantItems;
    }
}