<?php

namespace App\Booking\Services;

use App\Domains\Order\Order;
use Illuminate\Support\Facades\DB;

class OrderManager implements \App\Booking\Contracts\OrderManager
{
    /**
     * @param Order $order
     * @param $status
     */
    public function updateStatus(Order $order, $status): void
    {
        $order->update([
            'status' => $status,
        ]);
        $order->save();
    }

    /**
     * Hard Delete an order and it's associated data.
     *
     * @param $orderId
     */
    public function delete(Order $order): void
    {
        $orderItems = $order->items;
        $bookings = $order->bookings;

        if ($bookings->count()) {
            $calendarEntries = $bookings->first()->calendar->all();
        }

        $formResponses = $order->formResponse;
        $billPayer = $order->billpayer;
        $billPayerAddress = $order->billpayer->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        DB::beginTransaction();

        try {
            $billPayerAddress->delete();

            $billPayer->delete();

            $orderItems->each(function ($entry) {
                $entry->delete();
            });

            $formResponses->each(function ($entry) {
                $entry->delete();
            });

            $order->delete();

            $shippingAddress->delete();

            $bookings->each(function ($entry) {
                $entry->delete();
            });

            if ($bookings->count()) {
                $calendarEntries->each(function ($entry) {
                    $entry->delete();
                });
            }

            // Not a great idea to delete the customer when deleting a booking!
            // They may have other active bookings!
            //$customer->delete();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        DB::commit();
    }
}
