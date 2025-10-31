<?php

namespace App\Booking\Controllers\Frontend;

use App\Booking\Address;
use App\Booking\Availability\AvailabilityToFullCalendar;
use App\Booking\Requests\Frontend\UpdateCalendarRequest;
use App\Booking\Services\DistanceService;
use App\Domains\Auth\Models\User;
use App\Domains\Checkout\EventForm\Event as EventParser;
use App\Domains\Order\Order;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

use Pnlinh\GoogleDistance\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

/**
 * Class UpdateCalendarController.
 */
class CalendarController extends Controller
{
    /**
     * @param  UpdatePasswordRequest  $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateCalendarRequest $request)
    {
        $user = Auth::user();
        $user->bookingSettings->set([
            'calendar' => [
                'url' => $request->get('calendar_url')
            ],
            'calendar_schedule' => [
                'url' => $request->get('calendar_schedule_url')
            ]
        ]);
        $user->save();

        return redirect()->route(
            'frontend.user.account',
            ['#calendar-integration']
        )->withFlashSuccess(__('Calendar URL successfully updated.'));
    }

    /**
     * Get json data for fullCalendar.
     *
     * @todo - move to calendar service, as I would like the customers to be able to have an external calendar widget
     *
     * At the time of writing, I would like to access this method to the API however I don't currently know how to or have any authentication method.
     *  We will be using the https://laravel.com/docs/7.x/sanctum package for API
     */
    public function fullCalendar(Request $request)
    {
        $converter = new AvailabilityToFullCalendar();
        $userId = Auth::user()->id;
        $availability = $converter->convert($userId);

        // Show users, business orders
        {
            $orders = Auth::user()->businesses->first()?->orders?->all();
            if ($orders) {
                foreach ($orders as $order) {
                    $calItem = new \stdClass();
                    $calItem->title = $order->location;
                    $calItem->url = '/order/'.$order->id;
                    $calItem->start = $order->start;
                    $calItem->end = $order->end;
                    $availability[] = $calItem;
                }
            }
        }

        return $availability;
    }

    public function getIcal(string $uuid) {

        $user = User::Uuid($uuid)->firstOrFail();
        $calendar = Calendar::create('Booking calendar');
        $orders = $user->businesses->first()?->orders?->all();

        if ($orders) {
            foreach ($orders as $order) {

                $eventDetails = collect([]);
                try {
                    $forms = null;
                    foreach ($order->formResponse as $formResponse) {
                        $forms[$formResponse->form_id] = $formResponse->form;
                    }

                    //$userResponses = $order->getResponses();
                    $eventDetails = (new EventParser())->parse($forms);
                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                }

                $calendarEvent = Event::create('Booking ' . $order->location)
//todo set timezone from business.
                    ->startsAt(new \DateTime($order->start))
                    ->endsAt(new \DateTime($order->end));

                $url = "https://elitebookingsystem.com/order/edit/{$order->id}";

                $location = $this->getGoogleMapsLocationDetails($user, $order);
                $miles = round($location->getMiles(),2);
                $mapUrl = $this->getMapUrl($user, $order);

                $orderItems = $this->getOrderItems($order);

                $description = <<<DESC
                    Event Type: {$eventDetails->get('event-type', 'unknown')}
                    Booking URL: {$url}
                    Start Time: {$order->start}
                    End Time: {$order->end}
                    Location: {$order->location}
                    Travelling time: {$location->getMinutes()} mins
                    Travelling distance: {$miles} miles
                    Map URL: {$mapUrl}

                    Customers name: {$order->billpayer->firstname} {$order->billpayer->lastname}
                    Customer phone: {$order->billpayer->phone}
                    Customer email: {$order->billpayer->email}

                    Order Items: {$orderItems}
                DESC;

                $calendarEvent->description($description);

                $calendar->event($calendarEvent);
            }
        }

        return $calendar->get();
    }

    /**
     * Ajax GET endpoint - for the sync button on the My calendar screen.
     *
     * This just calls the artisan sync command, once complete the JS then uses full calendar to refetch the data.
     *
     * @todo - move to API controller as I would like the customers to be able to have an external calendar widget
     */
    public function sync()
    {
        $userId = Auth::user()->id;
        $filename = Auth::user()->booking_settings['calendar']['url'];
        $exitCode = Artisan::call('larabook:schedule-import', [
            'userid' => $userId,
            'icsurl' => $filename,
//            //{--tag=[holiday]}';
        ]);
    }

    protected function getGoogleMapsLocationDetails(User $user, Order $order): Response
    {
        /**
         * @var Address $originAddress
         */
        $originAddress = $user->businesses->first()->address;
        $locationSerivice = app()->make(DistanceService::class);

        // From permanent cache.
        $location = $locationSerivice->getDistance(
            $originAddress->postalcode,
            $originAddress->formatSingleLine(),
            $order->location
        );
        return $location;
    }

    protected function getMapUrl(User $user, Order $order): string
    {
        return "https://www.google.com/maps/dir/" .
            rawurlencode($user->businesses->first()->address->postalcode) .
            "/" .
            rawurlencode($order->location);
    }

    protected function getOrderItems($order): string
    {
        $orderItems = '';
        foreach ($order->items as $orderItem) {
            $orderItems .= "{$orderItem->name} ({$orderItem->product_type}), ";
        }
        return  substr($orderItems, 0,  -2);
    }
}
