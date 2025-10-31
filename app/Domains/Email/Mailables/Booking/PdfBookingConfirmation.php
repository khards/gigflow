<?php

namespace App\Domains\Email\Mailables\Booking;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Email\Template\TemplateMailable;
use App\Domains\Order\Order;
use Carbon\Carbon;

class PdfBookingConfirmation extends TemplateMailable
{
    public Order $order;
    public User $user;
    public Business $business;
    public $business_address;
    public $billpayer;
    public string $datetime;
    public string $payment_link;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $user = $params['user'];
        $order = $params['order'];

        $this->user = $user;
        $this->order = $order;
        $this->billpayer = $order->getBillpayer()->first();
        $this->business = $order->business()->first();
        $this->business_address = $this->business->address()->first();
        $this->datetime = (new Carbon())->timezone($this->business->timezone)->format('l jS \\of F Y h:i');

        $this->payment_link = config('app.url').route('frontend.order.payment', $order->id, false);
    }

    public function owner() {
        return $this->order->business;
    }
}
