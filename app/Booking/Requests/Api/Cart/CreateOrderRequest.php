<?php

namespace App\Booking\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Vanilo\Cart\Contracts\CartManager;

class CreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'businessId' => 'required|exists:businesses,id',
            'location' => 'sometimes|string',
            'start' => 'sometimes|date_format:Y-m-d H:i:s',
            'end' =>'sometimes|date_format:Y-m-d H:i:s||after_or_equal:start',
            'reference' => 'required|exists:carts,id',
        ];
    }
}
