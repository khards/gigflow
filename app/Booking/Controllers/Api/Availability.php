<?php

namespace App\Booking\Controllers\Api;

use App\Booking\Business;
use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Requests\AvailabilityRequest;
use App\Booking\Resources\AvailabilityResource;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Availability extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //  $this->middleware('auth');
    }

    /**
     * I think this is fairly redundant from my original intention as it just deals with si a staff available.
     *
     * @return \App\Http\Resources\Availability
     */
    public function check(AvailabilityRequest $request, AvailabilityManager $availabilityManager)
    {
        // @todo - location checking....
        // @todo - make a request validation class
        $location = $request->get('location');
        $businessId = $request->get('business');
        $business = Business::find($businessId);

        $availability = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', $request->get('start')),
            Carbon::createFromFormat('Y-m-d H:i:s', $request->get('end')),
            $business
        );

        $message = $availability ? 'Available' : "Sorry {$business->name} is unable to fulfil your booking at this time";

        return new AvailabilityResource(
            (object) [
                'available' => $availability,
                'message' => $message,
            ]
        );
    }
}
