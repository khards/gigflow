<?php

namespace App\Booking\Requests\Frontend\Schedule;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'schedule_summary' => ['required'],
            'schedule_state' => ['required'],
            'startDate' => ['required'],
            'startTime' => ['required'],
            'endDate' => ['required'],
            'endTime' => ['required'],
            'rrule' => 'nullable|sometimes',
            'timezone' => 'required',
        ];
    }
}
