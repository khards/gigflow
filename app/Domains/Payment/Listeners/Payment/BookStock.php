<?php

namespace App\Domains\Payment\Listeners\Payment;

use App\Booking\Business;
use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Models\Calendar;
use App\Booking\Product;
use App\Domains\Order\Order;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Events\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BookStock
{
    private Order $order;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private AvailabilityManager $availabilityManager)
    {
        //
    }

    /**
     * Handle the event.
     *
     * This event is responsable for booking services in calendar and take stock.
     * Other listeners send emails, update status etc.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(PaymentTransaction $event)
    {
        $eventData = $event->getData();

        $order = Order::find($eventData['orderId']);

        if (! $order) {
            throw new \Exception('Error, order '.$eventData['orderId'].' was not found. Unable to process payment event');
        }
        $this->order = $order;

//BUG?......!!! Could be in completed state and recieve a payment.
        // 1. Calculate bookable servicess & book them.
        if (!$this->order->status->equals(OrderStatus::NEW())) {
            return;
        }

        if ($eventData['amount'] < 0) {
            return;
        }

        $this->processOrderItems();
    }

    /**
     * Process the order items.
     *
     * Call correct handler for each product/service type.
     *
     * App\Domains\Checkout\Services\CheckoutService::bookServices($request) to book services.
     *
     * 1. Calculate bookable servicess & book them.
     * 2. Take stock from non service products
     *
     * @return void
     */
    private function processOrderItems() {

        $services = collect();

        foreach($this->order->items as $orderItem) {
            $product = Product::find($orderItem->product_id);
            if(!$product) {
                throw new \Exception('Error, product '.$orderItem->product_id.' was not found. Unable to process payment event');
            }
            if ($product->isService()) {
                $services->push([
                    'product' => $product,
                    'orderItem' => $orderItem,
                ]);
            } else {
                $product->decrementStock($orderItem->quantity);
            }
        }

        if($services->count()) {
            $this->bookServices($services);
        }
    }

    /**
     * Create booking when there is a bookable order item in the cart
     * Calendar entry is assigned to staff and the item/booking.
     *
     * @param Collection $services
     */
    protected function bookServices(Collection $services)
    {

        // Products can have and not have staff.
        // We must check here if products need staff and how many are required.
        //
        // Some products can be dry-hired with no staff.
        // We don't want to asssign staff to those!!!!!!

        {
            $totalNumStaffForOrder = 0;
            foreach ($services as $service) {
                if ($service['product']->staff_quantity > 0) {
                    $totalNumStaffForOrder += $service['product']->staff_quantity;
                    break;
                }
            }
        }

        // Get all staff for all services.
        $allStaff = $this->getAvailableStaffMembers($service['product']->business, $totalNumStaffForOrder);
        $numberStaffAvailable = count($allStaff);
        if ($totalNumStaffForOrder > $numberStaffAvailable) {
            $message = 'Not enough staff available for order ' . $this->order->id . "\n";
            $message = "Got " . $numberStaffAvailable . " staff, when {$totalNumStaffForOrder} needed";
Log::warning($message);
            //@TODO send notification email via event.
            //Don't prevent booking as customer has paid !!!

            //throw new \Exception();

            $totalNumStaffForOrder = $numberStaffAvailable;

        }

        //Book the bookable products in the calendar, so that nobody else can book them.
        foreach($services as $line) {
            $product = $line['product'];
            Calendar::create([
                'model_id' => $product->id,
                'model_type' => $product->getMorphClass(),
                'start' => $this->order->start,
                'end' => $this->order->end,
                'booked_by_type' => $this->order->getMorphClass(),
                'booked_by_id' => $this->order->id,
                'start' => $this->order->start,
                'end' => $this->order->end,
            ]);
        }

        //Book the staff to cover the order item for products requiring staff.
        if ($product->staff_quantity) {
            foreach($allStaff as $s) {
                Calendar::create([
                    'model_id' => $s->id,
                    'model_type' => $s->getMorphClass(),
                    'booked_by_type' => $this->order->getMorphClass(),
                    'booked_by_id' => $this->order->id,
                    'start' => $this->order->start,
                    'end' => $this->order->end,
                ]);
            }
        }

    }

    /**
     * Search for available member of staff. If one is available, then return them.
     * If no staff are available, then throw an exception.
     *
     * @param $business
     * @param $startDateTime
     * @param $endDateTime
     *
     * @return array[User]
     */
    private function getAvailableStaffMembers(Business $business, int $quantityRequired): array
    {
        $available = [];

        foreach ($business->users()->get() as $staff) {
            $start = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $this->order->start);
            $end = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $this->order->end);

            if ($this->availabilityManager->staffHasAvailability($start, $end, $staff)) {

                $available[] = $staff;

                if (count($available) >= $quantityRequired)
                    break;
            }
        }

        return $available;
    }
}
