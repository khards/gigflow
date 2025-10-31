<?php

namespace App\Booking\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'formId' => $this->resource['formId'],
            'formData' => is_string($this->resource['formData']) ? $this->resource['formData'] : json_encode($this->resource['formData']),
            'sessionId' => (string) $this->resource['sessionId'],
            'status' => $this->resource['errors']->count() ? 'error' : 'ok',
            'errors' => $this->resource['errors'],
            'number_lines_in_cart' => (string) $this->resource['totalLines'],
            'number_items_in_cart' => (string) $this->resource['totalItems'],
            'reference' => (string) $this->resource['reference'],
            'price' => [
                'adjustments' => (string) $this->resource['adjustments'],
                'total_price' => (string) $this->resource['totalPrice'],
                'dispatch_price' => (string) $this->resource['dispatchPrice'],
                'deposit' => (string) $this->resource['deposit'],
            ],
        ];
    }
}
