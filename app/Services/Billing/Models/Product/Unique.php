<?php

namespace App\Services\Billing\Models\Product;

class Unique extends ProductAbstract implements ProductInterface
{
    public function getName() : string
    {
        return $this->product->name;
    }

    public function getQuantity() : string
    {
        return $this->product->amount;
    }

    public function getQuantityUnit() : string
    {
        return $this->product->unit;
    }

    public function getUnitPrice() : string
    {

        return $this->product->net_price;
    }

    public function getVat() : string
    {
        return $this->product->tax;
    }

    /**
     * Sum NET price
     *
     * @return string
     */
    public function getNetPrice() : string
    {
        return $this->orderItem->sumNetPrice();
    }

    public function getVatAmount() : string
    {
        $vat = $this->orderItem->sumTax();

        return $vat;
    }

    public function getGrossAmount() : string
    {
        return $this->orderItem->sumGrossPrice();
    }

    public function getComment() : string
    {
        $comments = '';
        if ($this->product->from_date)
        {
            $comments.= $this->product->from_date;
        }

        if ($this->product->to_date)
        {
            if ($this->product->from_date)
            {
                $comments.= ' - ';
            }
            $comments.= $this->product->to_date;
        }

        if ($this->product->description)
        {
            if ($comments != '')
            {
                $comments.= "\r\n";
            }
            $comments.= $this->product->description;
        }

        return $comments;
    }
}