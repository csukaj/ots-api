<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmbeddedOrderSendRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'model.id' => 'required|numeric',
            'model.site' => 'required|string',
            'model.email' => 'required|email',
            'model.first_name' => 'required',
            'model.last_name' => 'required',
            'model.nationality' => 'required',
            'model.remarks' => 'nullable',
            'model.telephone' => 'required|PhoneNumber',
            'model.items.*.guests.*.first_name' => 'required|string',
            'model.items.*.guests.*.last_name' => 'required|string',
            'model.items.*.from_date' => 'required|date_format:Y-m-d',
            'model.items.*.to_date' => 'required|date_format:Y-m-d',
            'model.billing_country' => 'string|nullable',
            'model.billing_zip' => 'string|nullable',
            'model.billing_settlement' => 'string|nullable',
            'model.billing_address' => 'string|nullable',
            'targetStatus' => 'required|numeric'
        ];

    }

}