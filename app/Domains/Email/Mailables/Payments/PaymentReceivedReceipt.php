<?php

namespace App\Domains\Email\Mailables\Payments;

use App\Booking\Address;
use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Billpayer\Models\Billpayer;
use App\Domains\Email\Template\TemplateMailable;
use App\Domains\Order\Order;
use Carbon\Carbon;

class PaymentReceivedReceipt extends TemplateMailable
{
    public string $customerEmail;
    public string $amount;
    public string $name;
    public Order $order;
    public User $user;
    public Business $business;
    public Billpayer $billpayer;
    public Address $business_address;
    public string $datetime;

    public function __construct(array $params)
    {
        $this->order = $params['order'];
        $this->amount = $params['amount'] ?? 0.00; // Specific. Default to zero for tempalte preview.
        $this->user = $this->order->user;

        $this->billpayer = $this->order->getBillpayer()->first();
        $this->business = $this->order->business()->first();
        $this->business_address = $this->business->address()->first();
        $this->datetime = (new Carbon())->timezone($this->business->timezone)->format('l jS \\of F Y H:i');
        $this->customerEmail = $this->order->billpayer->getEmail();

        $this->name = $this->order->billpayer->getName();

    }

//    public function __construct(Order $order, float $amount)
//    {
//        $this->order = $order;
//        $this->customerEmail = $order->billpayer->getEmail();
//        $this->amount = $amount;
//        $this->name = $order->billpayer->getName();
//    }

    public function owner() {
        return $this->order->business;
    }
}
