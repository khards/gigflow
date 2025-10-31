<?php

namespace App\Domains\Payment\Services;

use App\Booking\Business;
use App\Domains\Payment\Contracts\PaymentService as PaymentServiceContract;
use Illuminate\Support\Collection;

class PaymentService implements PaymentServiceContract
{
    /**
     * Get Payment Methods for given type    -> Perhaps it's best to return the model and have a a get data method?
     *
     * @param Business $business
     * @param string $type
     * @return Collection
     */
    public function get(Business $business, string $type): Collection
    {
        $config = $business->paymentMethod()->where('type', $type)->get()->first();

        return collect($config->data ?? []);
    }

    /**
     * Create or update Payment Method of given type.
     *
     * @param  Business  $business
     * @param  string  $type
     * @param  array  $data
     * @return void
     */
    public function createUpdate(Business $business, string $type, array $data)
    {
        $method = $business->paymentMethod()->where('type', $type)->get()->first();

        if (! $method) {
            $business->paymentMethod()->create([
                'type' => $type,
                'business_id' => $business->id,
                'data' => $data,
            ]);
        } else {
            $method->update([
                'type' => $type,
                'business_id' => $business->id,
                'data' => $data,
            ]);
        }
    }
}
