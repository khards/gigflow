<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use Closure;

class StockAvailable extends RuleParent
{
    public function handle(AvailabilityData $data, Closure $next)
    {
        $start = null;
        $end = null;

        if ($data->request->product->isService()) {
            if ($data->request->quantity === null) {
                $data->response->log[__CLASS__] = 'No quantity was requested for this service.';

                return $next($data);
            }

            if (! $data->request->start || ! $data->request->end) {
                $data->response->log[__CLASS__] = 'Missing start or end date, skipping hire Stock Check.';

                return $next($data);
            }

            $start = toCarbon($data->request->start);
            $end = toCarbon($data->request->end);
        }

        $availableStock = $data->request->product->getAvailableQuantity($start, $end);

        if ($data->request->quantity <= $availableStock) {
            $data->response->quantityAvailable = $availableStock;
            $data->response->log[__CLASS__] = 'Stock IS available at the requested quantity for this service.';

            return $next($data);
        }

        $data->response->log[__CLASS__] = "The requested quantity {$data->request->quantity} for this product / service is NOT available.";

        return false;
    }
}
