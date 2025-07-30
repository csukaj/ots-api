<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderSendRequest extends FormRequest
{
    public function messages()
    {
        return [
            'telephone.PhoneNumber' => '":input" not valid phone number'
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return
            [
                # @todo @ivan - ideiglenesen kikommentezve
                // 'email' => 'required|email',
                // 'first_name' => 'required',
                // 'last_name' => 'required',
                // 'language' => 'required',
                // 'nationality' => 'required',
                // 'remarks' => 'nullable',
                // 'telephone' => 'required|PhoneNumber',
                // 'items.*.guests.*.first_name' => 'required|string',
                // 'items.*.guests.*.last_name' => 'required|string',
                // 'items.*.interval.date_from' => 'required|date_format:Y-m-d',
                // 'items.*.interval.date_to' => 'required|date_format:Y-m-d',
                // 'billing_country' => 'string',
                // 'billing_zip' => 'string',
                // 'billing_settlement' => 'string',
                // 'billing_address' => 'string',
            ];
    }

    public function getSite()
    {
        return parse_url($this->header('Referer', $this->header('Origin')), PHP_URL_HOST);
    }
}