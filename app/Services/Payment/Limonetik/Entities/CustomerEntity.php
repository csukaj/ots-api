<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\Customer;

class CustomerEntity
{
    protected $customer;

    protected $locale;

    public function __construct(Customer $customer, string $locale)
    {
        $this->customer = $customer;
        $this->locale = $locale;
    }

    public function getFrontendData(): array
    {
        return [
            'Id' => $this->customer->id,
            'Email' => $this->customer->email,
            'FirstName' => $this->customer->firstName,
            'LastName' => $this->customer->lastName,
            'Culture' => $this->locale
        ];
    }
}