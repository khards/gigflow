<?php

namespace App\Booking;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $guarded = ['id'];

    public function calendar()
    {
        return $this->morphTo('booked_by');
    }
}
