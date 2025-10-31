<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use App\Booking\Product;

class ProductActive extends RuleParent
{
    /**
     * @var AvailabilityData
     */
    private $data;

    public function handle(AvailabilityData $data, \Closure $next)
    {
        $this->data = $data;

        if ($data->request->active === false && $data->request->product->state == Product::STATUS_DRAFT) {
            $data->response->log[__CLASS__] = 'Product is In-active';

            return $next($data);
        }

        if ($data->request->active === true && $data->request->product->state == Product::STATUS_ACTIVE) {
            $data->response->log[__CLASS__] = 'Product is active';

            return $next($data);
        }

        $data->response->log[__CLASS__] = "The product {$data->request->product->id} is not active";
    }
}
