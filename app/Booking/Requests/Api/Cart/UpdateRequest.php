<?php

namespace App\Booking\Requests\Api\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|between:0,999999',
            'businessId' => 'required|exists:businesses,id',
            'location' => 'sometimes|string',
            'start' => 'sometimes|date_format:Y-m-d H:i:s',
            'end' =>'sometimes|date_format:Y-m-d H:i:s||after_or_equal:start',
            'formData' => 'sometimes|json|nullable',
            'currentFormId' => 'sometimes|int|nullable',
            'navAction' => 'sometimes|string|nullable',
            'sessionId' => 'sometimes|string|nullable',
        ];
    }
}
