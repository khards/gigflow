<?php

namespace App\Booking\Services;

use App\Booking\Business;
use App\Booking\Contracts\Product;

class BusinessManager implements \App\Booking\Contracts\BusinessManager
{
    /**
     * Create a business.
     *
     * @param array $details
     * @return mixed
     */
    public function create(array $details): Business
    {
        return Business::create($details);
    }

    /**
     * Read a business.
     *
     * @param $id
     * @return Business
     */
    public function read($id)
    {
        // TODO: Implement read() method.
    }

    /**
     * Update a business.
     *
     * @param Business|int $business
     * @param array $details
     * @return Business
     */
    public function update($business, array $details): Business
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        $business->update($details);

        return $business;
    }

    /**
     * Delete a product.
     *
     * @param Business|int $business
     * @return mixed
     */
    public function delete($business)
    {
        // TODO: Implement delete() method.
    }
}
