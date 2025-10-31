<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;

class ProductScheduleAvailable extends RuleParent
{
    /**
     * @var AvailabilityData
     */
    private $data;

    public function handle(AvailabilityData $data, \Closure $next)
    {
        $this->data = $data;

        if ($this->data->request->product->availability_type !== 'scheduled') {
            return $next($data);
        }

        // By schedule, but missing params.
        if ($this->data->request->product->availability_type == 'scheduled') {
            if ($this->data->request->start === null || $this->data->request->end === null) {
                $this->data->response->log[__CLASS__] = "Missing start, or end datetime so can't check scheduled availability";

                return $next($data);
            }
        }

        if ($this->isScheduledAvailable()) {
            $this->data->response->log[__CLASS__] = "Product Schedule all good!";
            return $next($data);
        }

        $this->data->response->log[__CLASS__] = 'The product is not available as it is currently not scheduled';

        $this->data->response->info['reason'] = 'available';
        $this->data->response->info['reason_message'] = 'Not available on the selected date';
    }

    /**
     * Determine if product is available by the schedule.
     * Products could be only available at certain times of the year etc.
     * A product _could_ be a celebrity DJ available on 2 months of the year etc.
     *
     * @return bool
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    private function isScheduledAvailable()
    {
        if ($this->data->request->product->availability_type == 'scheduled') {
            //if ($this->data->request->product->scheduledAvailableDuring(toCarbon($this->data->request->start), toCarbon($this->data->request->end))) {
            if ($this->data->request->product->isAllowedByAvailabilityScheduled(toCarbon($this->data->request->start), toCarbon($this->data->request->end)))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }
}
