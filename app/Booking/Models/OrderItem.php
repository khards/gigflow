<?php

use App\Domains\Booking\Models\Booking;
use Vanilo\Order\Models\OrderItem as VanilloOrderItem;

class OrderItem extends VanilloOrderItem
{
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
