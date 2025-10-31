<?php

namespace App\Domains\Email\Mailables\Quote;

// TODO:
//      1. Modify JS app to load the cart from a quote ID
//      2. Add Quote email that has a link to load the cart.

use App\Domains\Email\Mailables\Booking\BookingConfirmation;

// This will just give them a link to the payment screen as the order is created.
class Quote extends BookingConfirmation
{
    public function build()
    {
        return parent::build();
    }
}
