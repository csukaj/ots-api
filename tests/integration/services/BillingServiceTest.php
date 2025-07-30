<?php

namespace Tests\Integration\Services;

use App\Order;
use App\Payment;
use App\Services\Billing\Models\Product\Accomodation;
use App\Services\Billing\Service;
use App\Services\Billing\Szamlazzhu\Phpapi\InvoiceAgent;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\OrderTestTrait;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use OrderTestTrait;

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_not_create_with_bad_order_data()
    {
        $fakeOrderId = $this->faker->numberBetween(100000, 900000);

        $billing = new Service(new Order(), config('billing'));
        $billing->create($fakeOrderId);
        $this->assertTrue($billing->hasError());
        $this->assertEquals('Order with "' . $fakeOrderId . '" is not found', $billing->getErrors()[0]['message']);

        $billing->flushErrors();

        $order = $this->prepareSampleOrder(true);
        $billing->create($order->id);

        $this->assertTrue($billing->hasError());
        $this->assertEquals('Order with "' . $order->id . '" has no payment information', $billing->getErrors()[0]['message']);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \Throwable
     */
    public function it_can_create()
    {
        $order = $this->prepareSampleOrder(true);
        if ($order->billing_type_taxonomy_id === Config::get('taxonomies.billing_types.company')) {
            $order->company_name = $this->faker->company;
        }

        $order->billing_zip = $this->faker->postcode;
        $order->billing_settlement = $this->faker->city;
        $order->billing_address = $this->faker->streetAddress;
        $order->saveOrFail();

        (new Payment([
            'order_id' => $order->id,
            'request_id' => $this->faker->randomNumber,
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->saveOrFail();

        $billingDate = $order->payment->updated_at->format('Y-m-d');
        $gIArgs = [
            ['e_invoice' => true, 'keychain' => '', 'download_invoice' => false], //settings
            [
                "invoice_date" => $billingDate,
                "fulfillment" => $billingDate,
                "payment_due" => $billingDate,
                "exchange_bank" => 'MNB',
                "payment_method" => __('billing.szamlazzhu.create_invoice.payment_method.bank_card'),
                "currency" => config('billing.szamlazzhu.currency'),
                "language" => $order->language(),
                "comment" => __('billing.szamlazzhu.create_invoice.law_comment'),
                "is_paid" => true,
                "order_no" => (string)$order->id
            ],
            config('billing.szamlazzhu.seller'),
            [
                'name' => $order->billingName(),
                "zip" => $order->billing_zip,
                "city" => $order->billing_settlement,
                "address" => $order->billing_address,
                "email" => $order->email,
                "send_email" => true,
                "phone" => $order->telephone,
            ],
            null
        ];

        $orderItem = $order->items[0];
        $billingModel = new Accomodation($orderItem);

        $billingItem = [
            "name" => $billingModel->getName(),
            "quantity" => '1.0',
            "quantity_unit" => 'db',
            "unit_price" => $orderItem->price,
            "vat" => config('billing.szamlazzhu.vat'),
            "net_price" => $orderItem->price,
            "vat_amount" => "0",
            "gross_amount" => $orderItem->price,
            "comment" => $billingModel->getComment()
        ];

        $mockInvoiceNumber = $this->faker->randomNumber;
        $mockSzamlazz = Mockery::mock(InvoiceAgent::class, config('billing.szamlazzhu'));
        $mockSzamlazz->shouldReceive('writeLog')->andReturnNull();
        $mockSzamlazz->shouldReceive('runCall')->andReturn($mockInvoiceNumber);
        $mockSzamlazz
            ->shouldReceive('addItem')
            ->times($order->items->count())
        ->with($billingItem);
        $mockSzamlazz
            ->shouldReceive('_generateInvoice')
            ->once()
            ->with($gIArgs[0], $gIArgs[1], $gIArgs[2], $gIArgs[3], $gIArgs[4])
            ->andReturn($mockInvoiceNumber);


        $billing = new Service($order, config('billing'));
        $billing
            ->setAgent($mockSzamlazz)
            ->create($order->id);

        $order->refresh();

        //returned invoice number is assigned to oder
        $this->assertEquals($mockInvoiceNumber, $order->invoice);

    }

    /**
     * @test
     * @throws \Throwable
     */
    public function it_can_getPdf()
    {

        $fakePdf = '%PDF-1.4' . implode('', array_map(function ($i) {
                return chr(rand(0, 255));
            }, range(0, 5)));


        $mockInvoiceNumber = $this->faker->randomNumber;
        $order = $this->prepareSampleOrder(true);
        $order->invoice = $mockInvoiceNumber;
        $order->saveOrFail();

        $args = [
            'username' => config('billing.szamlazzhu.beallitasok.username'),
            'password' => config('billing.szamlazzhu.beallitasok.password'),
            'invoice_num' => $order->invoice
        ];

        $mockSzamlazz = Mockery::mock(InvoiceAgent::class, config('billing.szamlazzhu'));
        $mockSzamlazz
            ->shouldReceive('_requestInvoicePDF')
            ->once()
            ->with($args)
            ->andReturn(["invoice_number" => $mockInvoiceNumber, "pdf" => $fakePdf]);


        $billing = new Service($order, config('billing'));
        $billing->setAgent($mockSzamlazz);
        $billing->getPdf($order->id);

        $this->assertEquals($fakePdf, $billing->getPdfInvoice());
    }


}