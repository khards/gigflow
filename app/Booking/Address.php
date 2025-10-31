<?php

namespace App\Booking;

use Illuminate\Database\Eloquent\SoftDeletes;
use Vanilo\Framework\Models\Address as VaniloAddress;

/**
 * Class Address.
 */
class Address extends VaniloAddress
{
    use SoftDeletes;

    public function business()
    {
        return $this->hasOne(Business::class, 'address_id', 'id');
    }

    public function formatSingleLine()
    {
        return "{$this->address}, {$this->city}, {$this->postalcode}, ".$this->country()->first()->id;
    }
}
