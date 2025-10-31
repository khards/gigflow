<?php

namespace App\Domains\Booking\Models;

use App\Booking\Models\Calendar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Vanilo\Order\Models\OrderItem;

class Booking extends Model
{
    protected $guarded = ['id'];

    protected $table = 'bookings';

    /**
     * Get the calendar entry for this booking.
     *
     * @return MorphOne
     */
    public function calendar()
    {
        return $this->morphOne(Calendar::class, 'booked_by');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
