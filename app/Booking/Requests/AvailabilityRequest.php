<?php

namespace App\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityRequest extends FormRequest
{
    /**
     * Anyone, anywhere can check availability.
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
            'start' => 'required|date_format:Y-m-d H:i:s|before:'.$this->get('end'),
            'end' => 'required',
            'location' => 'required',
            'business' => 'required|integer|exists:businesses,id',
        ];
    }
}
