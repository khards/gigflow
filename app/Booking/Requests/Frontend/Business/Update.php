<?php

namespace App\Booking\Requests\Frontend\Business;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
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
          'name' => 'string',
          'timezone' => 'required|timezone',

          'address.name' => 'string',
          'address.address' => 'string',
          'address.city' => 'string',
          'address.postalcode' => 'required',
          'address.country_id' => 'required|string|exists:countries,id',
        ];
    }
}
