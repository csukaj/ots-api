<?php

namespace App\Services\Billing;

use App\Order;
use App\Services\Billing\Models\Product\Factory as ProductFactory;
use App\Services\Billing\Szamlazzhu\Phpapi\InvoiceAgent;
use App\Services\ErrorsTrait;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


/**
 * Billing service
 *
 * This service handle the billing logic and wrap the Szamlazz.hu Agent API
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
    protected $szamlazzhuConfig = [];

    /**
     * @var string PDF stream
     */
    protected $pdf;

    /**
     * @var string Invoice name/number
     */
    protected $invoiceNumber;

    /**
     * @var Object Payment logger
     */
    protected $logger = null;

    /**
     * @var array Process result array
     */
    protected $result = [];

    /**
     * @var Invoice agent, typically szamlazzhu invoiceAgent
     */
    private $agent;

    /**
     * Service constructor.
     *
     * @param Order $order
     * @param array $config
     * @throws Exception
     */
    public function __construct(
        Order $order,
        array $config
    )
    {
        $this->order = $order;
        $this->config = $config;
        $this->szamlazzhuConfig = $config['szamlazzhu'];

        $this->initLogger();
        $this->agent = new InvoiceAgent(config('billing.szamlazzhu'));
    }

    /**
     * Initialize the payment logger
     *
     * @throws Exception
     */
    protected function initLogger()
    {
        $this->logger = new Logger('payment');
        $streamHandler = new StreamHandler($this->config['log']);
        $this->logger->pushHandler($streamHandler);
    }

    public function setAgent($agent){
        $this->agent = $agent;
        return $this;
    }

    /**
     * Validate the orderId format and after the success validation load the order
     *
     * @param int $orderId
     * @return mixed
     */
    protected function loadOrder($orderId = null)
    {
        if (is_null($orderId)) {
            $message = 'order_id is null';
            $this->addError($message);
            return null;
        }
        if (!is_numeric($orderId)) {
            $message = 'order_id must be numeric';
            $this->addError($message);
            return null;
        }

        $order = Order::find($orderId);

        if ($order === null) {
            $message = 'Order with "' . $orderId . '" is not found';
            $this->addError($message);
            return null;
        }

        $this->setSiteLanguage($order->site);

        return $order;
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
     * Create new invoice through szamlazzhu api
     *
     * @param int $orderId
     * @return Service
     * @throws Exception
     */
    public function create(int $orderId): self
    {
        # Load order
        $order = $this->loadOrder($orderId);

        //if we failed to load a correct order
        if (is_null($order)) {
            return $this;
        }

        if (empty($order->payment)) {
            $message = 'Order with "' . $orderId . '" has no payment information';
            $this->addError($message);
            return $this;
        }

        # Initialize agent
        $szamlazz = $this->agent;

        # Settings
        $settings = [
            "e_invoice" => true,
            "keychain" => "",
            "download_invoice" => false
        ];

        # Header data
        $billingDate = $order->payment->updated_at->format('Y-m-d');
        $header = [
            "invoice_date" => $billingDate,
            "fulfillment" => $billingDate,
            "payment_due" => $billingDate,
            "exchange_bank" => 'MNB',
            "payment_method" => __('billing.szamlazzhu.create_invoice.payment_method.bank_card'),
            "currency" => config('billing.szamlazzhu.currency'),
            "language" => $this->getInvoiceLanguage($order->language()),
            "comment" => __('billing.szamlazzhu.create_invoice.law_comment'),
            "is_paid" => true,
            "order_no" => (string)$orderId
        ];

        # Seller config
        $seller = config('billing.szamlazzhu.seller');

        # Buyer details
        $buyer = self::getBuyerDetails($order);

        # Billing items
        foreach ($order->items as $orderItem) {
            $orderItemModel = ProductFactory::getModel($orderItem);

            $billingItem = [
                "name" => $orderItemModel->getName(),
                "quantity" => $orderItemModel->getQuantity(),
                "quantity_unit" => $orderItemModel->getQuantityUnit(),
                "unit_price" => $orderItemModel->getUnitPrice(),
                "vat" => $orderItemModel->getVat(),
                "net_price" => $orderItemModel->getNetPrice(),
                "vat_amount" => $orderItemModel->getVatAmount(),
                "gross_amount" => $orderItemModel->getGrossAmount(),
                "comment" => $orderItemModel->getComment()
            ];

            $szamlazz->addItem($billingItem);
        }

        # Invoice generating
        $invoiceNumber = $szamlazz->_generateInvoice($settings, $header, $seller, $buyer, null);

        # Response-kent a szamlaszamot adja - ezt adatbazisba mentjuk
        if ($invoiceNumber !== false) {
            $order->invoice = $invoiceNumber;
            $order->save();
        }

        return $this;
    }

    /**
     * Get PDF through szamlazzhu API
     *
     * @param $orderId
     * @return $this
     */
    public function getPdf($orderId)
    {
        $order = $this->loadOrder($orderId);

        //if we failed to load a correct order
        if (is_null($order)) {
            return $this;
        }

        if (empty($order->invoice)) {
            $message = 'Order with "' . $orderId . '" has no invoice information';
            $this->addError($message);
            return $this;
        }

        $cfg = config('billing.szamlazzhu');

        $szamlazz = $this->agent;

        $settings = [
            'username' => $cfg['beallitasok']['username'],
            'password' => $cfg['beallitasok']['password'],
            'invoice_num' => $order->invoice
        ];

        $response = $szamlazz->_requestInvoicePDF($settings);

        $this->invoiceNumber = $response['invoice_number'];
        $this->pdf = $response['pdf'];

        return $this;
    }

    public function getPdfInvoice()
    {
        return $this->pdf;
    }

    /**
     * Get the invoice language by the site's language
     *
     * Szamlazz.hu doesn't know every language this reason need the define a "fallback" language.
     * For example the russian language is not supported, in this case can you overwrite  the invoice language change to english.
     * The default fallback language is english.
     *
     * @param string $siteLanguage
     * @return string
     */
    protected function getInvoiceLanguage(string $siteLanguage)
    {
        switch ($siteLanguage) {
            case 'hu':
            case 'en':
            case 'de':
                $invoiceLanguage = $siteLanguage;
                break;
            case 'ru':
            default:
                $invoiceLanguage = 'en';
                break;
        }

        return $invoiceLanguage;
    }

    /**
     * @param Order $order
     * @return array
     * @throws Exception
     */
    protected static function getBuyerDetails(Order $order): array
    {
        if (!$order->billing_zip || !$order->billing_settlement || !$order->billing_address) {
            throw new Exception('Missing billing information!');
        }
        $buyer = [
            "name" => $order->billingName(),
            "zip" => $order->billing_zip,
            "city" => $order->billing_settlement,
            "address" => $order->billing_address,
            "email" => $order->email,
            "send_email" => true,
            "phone" => $order->telephone,
        ];

        if ($order->isCompanyOrder()) {
            $buyer['tax_no'] = $order->tax_number;
        }
        return $buyer;
    }
}