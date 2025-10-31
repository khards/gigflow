<?php

namespace App\Booking\Requests\Frontend\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class Update.
 */
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
     * Convert the json field in the variations input to an array so that we can validate.
     * This can be dropped when we pass a properly formatted json request, rather than form request from the front end.
     *
     * @return array
     */
    public function validationData()
    {
        if ($this->request->get('variations')) { //$this->request->parameters["variations"]
            $this->merge(['variations' => json_decode($this->request->get('variations'), true)]);
        }

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'form' => 'nullable|exists:forms,id',
            'product_name' => ['required'],
            'product_setup_time' => 'numeric|between:0,999999',
            'product_state' => ['required'],
            'product_description' => ['required'],
            'product_url' => 'nullable|sometimes|url',
            'product_image' => 'sometimes|image', ////['sometimes', 'file'],
            'type' => 'in:product,service',
            'required' => 'required|in:0,1',
            'addon' => 'required|in:0,1',
            'delivery_methods' => 'required|array|min:1',
            'delivery_methods.*' => 'distinct|in:delivered,collected,shipped',

            'price_type' => 'required|in:fixed,scheduled',
            'price_fixed_price' => 'sometimes|numeric|between:0,999999',

            'price_scheduled' => 'required_if:price_type,scheduled|array|min:1',
            'price_scheduled.*.schedule' => 'exists:schedules,id,deleted_at,NULL,model_type,business',
            'price_scheduled.*.price' => 'numeric|between:0,999999.99',
            'price_scheduled.*.per' => 'sometimes|in:booking,day,hour,person',

            'special_price_scheduled' => 'required_if:price_type,scheduled|array|min:1',
            'special_price_scheduled.*.schedule' => 'exists:schedules,id,deleted_at,NULL,model_type,business',
            'special_price_scheduled.*.price' => 'numeric|between:0,999999.99',
            'special_price_scheduled.*.per' => Rule::in(['special']),

            'advance_charge.*.from' => 'numeric|between:0,999999',
            'advance_charge.*.to' => 'numeric|between:0,999999',
            'advance_charge.*.value' => 'numeric|between:-999999.99,999999.99',

            'extra_hours_charge_max_hours' => 'numeric|between:0,24',

            'block_number_days_future' => 'numeric|between:0,99999',
            'block_same_day_bookings' => 'in:yes,no',
            'blocked_postcodes', 'nullable|string|max:1024',

            'staff.required' => 'sometimes|in:yes,no',
            'staff.quantity' => 'sometimes|numeric|between:1,999',

            'availability_type' => 'sometimes|in:available,scheduled',
            'availability_schedule' => 'required_if:availability_type,scheduled|exists:schedules,id,deleted_at,NULL',
            'available_quantity' => 'numeric|between:0,9999999',

            'travelling.limit' => 'required_if:delivery_methods,delivered|in:yes,no',
            'travelling.value' => 'required_if:travelling.limit,cc|numeric|between:0,9999',
            'travelling.type' => 'required_if:travelling.limit,cc|in:miles,minutes',

            'delivery.delivered.charge' => 'sometimes|numeric|between:0,9999999',
            'delivery.delivered.per' => 'sometimes|in:order,item,mile,kilometer',
            'delivery.delivered.over' =>   'sometimes|numeric|between:0,9999999',

            'delivery.shipped.price' => 'sometimes|numeric|between:0,9999999',
            'delivery.shipped.per' => 'sometimes|in:item,order',

            'variations' => 'sometimes|array',
            'variations.*.id' => 'required|numeric|between:0,9999999',
            'variations.*.name' => 'required',
            'variations.*.stock_from_parent' => 'required|in:0,1',
            'variations.*.is_default' => 'required|in:0,1',
        ];
    }
}
