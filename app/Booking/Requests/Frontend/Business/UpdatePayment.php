<?php

namespace App\Booking\Requests\Frontend\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'paypal.account' => 'string',
          'paypal.clientId' => 'string',
          'paypal.descriptor' => 'string',
          'paypal.currency' => 'string',
          'paypal.app_id' => 'string',
          'paypal.secret' => 'string',
          'paypal.webhook_id' => 'string',

          'bank.name' => 'string',
          'bank.payee' => 'string',
          'bank.account' => 'string',
          'bank.sortcode' => 'string',
        ];
    }
}
