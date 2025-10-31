<?php

namespace Tests\Unit\Holiday;

use App\Booking\Holiday;
use App\Booking\Models\Calendar;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a holiday model can be created.
     */
    public function testHoliday()
    {
        $holiday = Holiday::create([
            'title' => 'A.Test "" {} [][\\n',
            'description' => "some <b>Description's\n\nTest",
        ]);

        $this->assertNotNull($holiday);
    }

    /**
     * Test that staff can book holiday.
     */
    public function testBookStaffHoliday()
    {
        /**
         * @var User $staff
         */
        $staff = User::create(['name' => 'test', 'email' => 'test@test.com', 'password' =>1234]);

        // With a holiday
        $holiday = Holiday::create([
            'title' => 'A.Test "" {} [][\\n',
            'description' => "some <b>Description's\n\nTest",
        ]);

        $bookingClass = (app()->make(Booking::class))->getMorphClass();
        // When the staff has a booking...
        $bookingCalendarEntry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $bookingClass,
            'booked_by_id' => 777,
            'start' => '2020-04-18 14:00:00',
            'end' => '2020-04-19 1:00:00',
        ]);

        // When I Book staff in holiday calendar
        $bookingCalendarEntry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $holiday->getMorphClass(),
            'booked_by_id' => $holiday->id,
            'start' => '2020-04-18 14:00:00',
            'end' => '2020-04-19 1:00:00',
        ]);

        // Then, verify that that staff holiday is in the calendar
        $calendarEntry = Calendar::all()->first();
        $this->assertNotNull($calendarEntry);

        // Now get back the single holiday, ignoring the booking.
        //
        // The following does 2 queries - It took me some time to figure this out in July 2020 as I started Cardstream, pips was up with a temperature that night too!
        // You'll look back on this comment one day :-)
        //
        //  select * from "calendar" where "calendar"."model_id" = ? and "calendar"."model_id" is not null and "calendar"."model_type" = ? order by "calendar"."id" asc limit 1000 offset 0
        //  select * from "holidays" where "holidays"."id" in (1, 2)
        $staff->calendar()->hols()->with('booked_by')->each(function ($item) use ($holiday) {
            $this->assertInstanceOf(Holiday::class, $item->booked_by);
            $this->assertEquals($holiday->title, $item->booked_by->title);
        });
    }
}
