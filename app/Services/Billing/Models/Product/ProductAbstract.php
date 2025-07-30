<?php

namespace App\Services\Billing\Models\Product;

use App\OrderItem;
use Carbon\Carbon;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ProductAbstract
{
    protected $orderItem;

    protected $product;

    protected $language = 'en';

    /**
     * ProductAbstract constructor.
     * @param OrderItem $orderItem
     * @throws \Exception
     */
    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
        $this->product = $orderItem->orderItemable;
        $this->language = $this->orderItem->order->language();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName(): string
    {
        $nameParts = [];

        $nameParts[] = __('billing.szamlazzhu.create_invoice.mediated_service');
        $nameParts[] = $this->getOrganizationString();

        $nameParts[] = $this->getOrderDetailsString($this->orderItem);
        $nameParts[] = $this->getMealString();

        return implode(' - ', $nameParts);
    }

    public function getQuantity(): string
    {
        return '1.0';
    }

    public function getQuantityUnit(): string
    {
        return $this->language != 'en' ? 'db' : 'pcs';
    }

    public function getUnitPrice(): string
    {
        return $this->orderItem->price;
    }

    public function getVat(): string
    {
        return config('billing.szamlazzhu.vat');
    }

    public function getNetPrice(): string
    {
        return $this->getUnitPrice();
    }

    public function getVatAmount(): string
    {
        return "0";
    }

    public function getGrossAmount(): string
    {
        return $this->orderItem->price;
    }

    public function getComment(): string
    {
        return $this->getGuestsString()
            . " \r\n"
            . $this->getFromToDateFormatted();
    }

    /**
     * Examine the language specific meal string part from language content
     *
     * @return string
     * @throws \Exception
     */
    protected function getMealString(): string
    {
        $translations = (new TaxonomyEntity($this->orderItem->mealPlan->name))->translations();

        $orderDetailsString = languageContent($this->language, $translations);
        $orderDetailsString = strtoupper($orderDetailsString);
        return str_replace('/', '', $orderDetailsString);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getOrganizationString(): string
    {
        return languageContent($this->language, $this->orderItem->getFromJSON('productableModel')->name);
    }

    protected function getGuestsString(): string
    {
        $guests = [];
        foreach ($this->orderItem->guests as $guest) {
            $guests[] = $guest->first_name . ' ' . $guest->last_name;
        }

        return implode('/', $guests);
    }

    protected function getFromToDateFormatted(): string
    {
        $fromDate = Carbon::parse($this->orderItem->date_from)->format('d.m.y');
        $toDate = Carbon::parse($this->orderItem->date_to)->format('d.m.y');

        return $fromDate . ' - ' . $toDate;
    }
}