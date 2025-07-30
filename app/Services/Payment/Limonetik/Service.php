<?php

namespace App\Services\Payment\Limonetik;

use App\Order;
use App\Payment;
use App\Services\ErrorsTrait as Errors;
use App\Services\Payment\Limonetik\Entities\ChargeEntity;
use App\Services\Payment\Limonetik\Entities\PaymentOrderEntity;
use App\Services\Payment\Limonetik\Models\Charge;
use App\Services\Payment\Limonetik\Models\PaymentOrder;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;

//use App\Services\Payment\Limonetik\Models\Credentials;

//use App\Services\Payment\Limonetik\Entities\CredentialsEntity;

/**
 * Limonetik payment gateway
 *
 * The package based on the following documents:
 *
 * - https://docs.limonetik.com/docs/introduction-merchant
 * - https://docs.limonetik.com/v1.0/reference
 * - https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40 (you need login to read)
 *
 * @package App\Services\Payment\Limonetik
 */
class Service
{
    use Errors;

    const VERSION = 'V40';

    const MODE_SANDBOX = 'sandbox';
    const MODE_PRODUCTION = 'production';

    const URL_SANDBOX = 'https://api.limonetikqualif.com/Rest';
    const URL_PRODUCTION = 'https://api.limonetik.com/Rest';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Http Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var array Process result array
     */
    protected $result = [];

    /**
     * @var Object Logger instance
     */
    protected $logger;

    /**
     * Service constructor.
     *
     * @param array $config
     * @param string $language
     */
    public function __construct(array $config, string $language)
    {
        $this->config = $config;
        $this->language = $language;
        $this->setHttpClient();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create a payment order
     *
     * https://docs.limonetik.com/v1.0/reference#create
     * https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40
     *
     * @param Order $order
     * @return Service
     */
    public function create(Order $order): self
    {
        $url = $this->getPaymentOrderCreateUrl();

        $payload = [
            'PaymentOrder' => $this->getPaymentOrder($order)
        ];

        # Elkeszitjuk a request-et
        $request = $this->createRequest('POST', $url, $payload);

        # Megnezzuk, hogy megfelelo valasz jon e vissza
        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $ex) {
            $this->readErrorFromResponseException($ex);

            return $this;
        }

        $responseContent = $this->getContentFromBody($response->getBody());
        $this->logger->info('Response: ' . json_encode($responseContent));

        $this->result = [
            'paymentPageUrl' => $responseContent['PaymentPageUrl'],
            'paymentOrderId' => $responseContent['PaymentOrderId'],
            'requestId' => $responseContent['RequestId']
        ];

        return $this;
    }

    /**
     * Get payment details by order id
     *
     * https://docs.limonetik.com/v1.0/reference#detail
     * https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40
     *
     * @param string $paymentOrderId Order ID The payment order identifier in the Limonetik platform.
     * @param array $elements Element names for the query string "AddElements" attribute: MerchantUrls, MerchantOrder, OperationsSummary, PaymentMethods.
     */
    public function detailByPaymentOrderId(string $paymentOrderId, array $elements = []): self
    {
        # GET hivas lesz, osszeallitjuk az url-t
        $query = [
            'Id' => $paymentOrderId
        ];
        if (!empty($elements)) {
            $query['AddElement'] = implode(',', $elements);
        }

        $url = $this->getPaymentOrderDetailUrl() . '?' . http_build_query($query);

        # Elkeszitjuk a request-et
        $request = $this->createRequest('GET', $url);

        # Megnezzuk, hogy megfelelo valasz jon e vissza
        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $ex) {
            $this->readErrorFromResponseException($ex);

            return $this;
        }

        # Parsoljuk a valaszt
        $this->responseContent = $this->getContentFromBody($response->getBody());

        # Osszeszedjuk a megfelelo adatokat
        $this->result = [
            'paymentOrder' => $this->responseContent['PaymentOrder']
        ];

        return $this;
    }

    # @todo @ivan dokumentalas
    public function pay(string $merchantId, Order $order, array $orderItems): self
    {
        $url = $this->getPaymentOrderPayUrl();

        $payload = [
            'PaymentOrder' => $this->getPaymentOrderByOrderItem($merchantId, $order, $orderItems),
            'Credentials' => $this->getPaymentOrderCredentials($order)
        ];

        # Elkeszitjuk a request-et
        $request = $this->createRequest('POST', $url, $payload);

        # Megnezzuk, hogy megfelelo valasz jon e vissza
        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $ex) {
            $this->readErrorFromResponseException($ex);

            return $this;
        }

        $responseContent = $this->getContentFromBody($response->getBody());
        if (empty($responseContent['PaymentOrderId'])) {
            $this->addError('Error in response: ', json_encode($responseContent));
            return $this;
        }

        $this->result = [
            'paymentOrderId' => $responseContent['PaymentOrderId'],
            'requestId' => $responseContent['RequestId']
        ];

        return $this;
    }

    # @todo @ivan dokumentalas
    public function charge(Payment $payment, array $orderItems): self
    {
        $url = $this->getPaymentOrderChargeUrl();

        $chargeModel = new Charge();
        $chargeModel->paymentOrderId = $payment->payment_order_id;
        $chargeModel->currency = 'EUR';

        $payload = (new ChargeEntity($chargeModel))->getFrontendData($orderItems);

        # Elkeszitjuk a request-et
        $request = $this->createRequest('POST', $url, $payload);

        # Megnezzuk, hogy megfelelo valasz jon e vissza
        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $ex) {
            $this->readErrorFromResponseException($ex);

            return $this;
        }

        return $this;
    }

    /**
     * After a process (create, cancel etc.) you can get the result of the process
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * It create a paymentOrder object by the order
     *
     * @param Order $order
     * @return array
     */
    protected function getPaymentOrder(Order $order)
    {
        $paymentOrderModel = (new PaymentOrder($order))
            ->setMerchantId($this->config['MERCHANT_ID'])
            ->setPaymentPageId($this->config['PAYMENT_PAGE_ID'])
            ->setMerchantUrls($this->getMerchantUrls($order->token));

        return (new PaymentOrderEntity($paymentOrderModel))->getFrontendData();

    }

    /**
     * It create a paymentOrder object by the orderItem
     *
     * @param string $merchantId
     * @param Order $order
     * @param array $orderItems
     * @return array
     */
    protected function getPaymentOrderByOrderItem(string $merchantId, Order $order, array $orderItems)
    {
        $paymentOrderModel = new PaymentOrder($order);
        $paymentOrderModel->setAmountByOrderItems($orderItems);
        $paymentOrderModel->setMerchantId($merchantId);
        $paymentOrderModel->setPaymentPageId($this->config['PAYMENT_PAGE_ID']);
        $paymentOrderModel->setMerchantUrls($this->getMerchantUrls($order->token, [], true));

        return (new PaymentOrderEntity($paymentOrderModel))->getFrontendData($orderItems);
    }

    /**
     * Get the after settlement credentials of an order
     *
     * @param Order $order
     * @return array
     */
    protected function getPaymentOrderCredentials(Order $order)
    {
        $credentials = [
            [
                'name' => 'technicalTransactionId',
                'credential' => $order->payment->payment_order_id
            ]
        ];

        return $credentials;
    }

    /**
     * Get PaymentOrder create url
     *
     * @return string url
     */
    protected function getPaymentOrderCreateUrl(): string
    {
        return $this->getBaseUrl() . 'PaymentOrder/Create';
    }

    /**
     * Get PaymentOrder details url
     *
     * @return string url
     */
    protected function getPaymentOrderDetailUrl(): string
    {
        return $this->getBaseUrl() . 'PaymentOrder/Detail';
    }

    /**
     * Get PaymentOrder cancel url
     *
     * @return string url
     */
    protected function getPaymentOrderCancelUrl(): string
    {
        return $this->getBaseUrl() . 'PaymentOrder/Cancel';
    }

    /**
     * Get PaymentOrder pay url
     *
     * @return string url
     */
    protected function getPaymentOrderPayUrl(): string
    {
        return $this->getBaseUrl() . 'PaymentOrder/Pay';
    }

    /**
     * Get PaymentOrder charge url
     *
     * @return string url
     */
    protected function getPaymentOrderChargeUrl(): string
    {
        return $this->getBaseUrl() . 'PaymentOrder/Charge';
    }

    /**
     * Set Http client
     */
    public function setHttpClient($client = null)
    {
        $this->httpClient = $client ?: new HttpClient();
    }

    /**
     * Create base url for http call
     *
     * Example for return value:
     * https://api.limonetikqualif.com/Rest/V40/
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $url = '';

        switch ($this->config['ENV']) {
            case self::MODE_SANDBOX :
                $url .= self::URL_SANDBOX;
                break;
            case self::MODE_PRODUCTION :
                $url .= self::URL_PRODUCTION;
                break;
        }

        $url .= '/' . self::VERSION . '/';

        return $url;
    }

    /**
     * Create a request for limonetik's call
     *
     * @param string $method
     * @param string $url
     * @param array $payload
     * @return Request
     */
    protected function createRequest(string $method, string $url, array $payload = []): Request
    {
        $payload = json_encode($payload);

        $this->logger->info('---Request payload: ' . $payload . " ### $method $url");

        $request = new Request(
            $method,
            $url,
            [
                'Authorization' => 'Basic ' . $this->config['API_KEY'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            $payload
        );

        return $request;
    }

    /**
     * Remove unnecessary BOM from string
     *
     * @param string $data Incoming data
     * @return string
     */
    protected function removeBOM(string $data)
    {
        if (0 === strpos(bin2hex($data), 'efbbbf')) {
            return substr($data, 3);
        }
        return $data;
    }

    /**
     * Get all kind of merchant urls (ReturnUrl, AbortedUrl, ErrorUrl, ServerNotificationUrl)
     *
     * @param string $orderToken Order Token
     * @param array $only Return only with the defined rows
     *
     * @param bool $disabled
     * @return array
     */

    protected function getMerchantUrls(string $orderToken, array $only = [], bool $disabled = false) : array
    {
        $siteUrl = $this->config['SITE_URL'];
        $apiUrl = $this->config['API_URL'];
        $configMerchantUrls = $this->config['MERCHANT_URLS'][$this->language];

        $merchantUrls = [
            # URL to which Limonetik should redirect users if the authorisation request is confirmed
            'ReturnUrl' => $siteUrl . $configMerchantUrls['returnUrl'] . '?o=' . $orderToken,
            # URL to which Limonetik should redirect users if they cancel their payment
            'AbortedUrl' => $siteUrl . $configMerchantUrls['abortedUrl'] . '?o=' . $orderToken,
            # URL to which Limonetik should redirect users if an error occurs in the payment flow
            'ErrorUrl' => $siteUrl . $configMerchantUrls['errorUrl'] . '?o=' . $orderToken,
            'ServerNotificationUrl' => $apiUrl . '/payment/notification?o=' . $orderToken,
        ];

        if (!empty($only)) {
            $merchantUrls = array_intersect_key($merchantUrls, $only);
        }

        if ($disabled)
        {
            $merchantUrls = [];
        }

        return $merchantUrls;
    }

    /**
     * Read and decode the error from the exception add write to log
     *
     * @param $exception
     */
    protected function readErrorFromResponseException($exception)
    {
        $error = $this->getContentFromBody(
            $exception->getResponse()->getBody()
        );

        $this->addError('Bad request response', json_encode($error));
    }

    /**
     * Get the body from the Guzzle's response stream.
     *
     * @param $body
     * @param bool $isJson
     * @return mixed|string
     */
    protected function getContentFromBody($body, $isJson = true)
    {
        $rawContent = (string)$body;
        $content = $this->removeBOM($rawContent);

        if ($isJson) {
            $content = json_decode($content, true);
        }

        return $content;
    }
}