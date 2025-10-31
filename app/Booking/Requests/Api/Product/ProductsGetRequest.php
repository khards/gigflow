<?php

// API

namespace App\Booking\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductsGetRequest extends FormRequest
{
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'state' => 'required|in:active,draft',
            'available' => 'sometimes|bool',
            'location' => 'sometimes|string',
            'start' => 'required|date_format:Y-m-d H:i:s',
            'end' =>'required|date_format:Y-m-d H:i:s||after_or_equal:start',
            'opt_fields' => 'sometimes|in:price',
            'timezone' => 'sometimes|string',
        ];
    }
}
