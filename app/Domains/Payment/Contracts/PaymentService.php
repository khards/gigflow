<?php

namespace App\Domains\Payment\Contracts;

use App\Booking\Business;
use Illuminate\Support\Collection;

interface PaymentService
{
    /**
     * Businesses can have one of each payment method. This is enforced by the database unique column on business_id, type.
     *
     * @param  Business  $business
     * @param  string  $type
     * @param  array  $data
     * @return void
     */
    public function createUpdate(Business $business, string $type, array $data);

    /**
     * Get Payment Methods for given type.
     *
     * @param Business $business
     * @param string $type
     * @return Collection
     */
    public function get(Business $business, string $type): Collection;
}
