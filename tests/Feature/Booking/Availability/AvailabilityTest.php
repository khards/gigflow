<?php

namespace Tests\Feature\Booking\Availability;

use App\Booking\Models\Calendar;
use App\Booking\Services\AvailabilityManager;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;
use Tests\Feature\Booking\BookingTestDataGenerator;

class AvailabilityTest extends BookingTestDataGenerator
{
    /**
     * A really thorough test of the schedule rrule search.
     */
    public function testRandomScheduledDays()
    {
        $staff = User::factory()->create();
        $availabilityManager = new AvailabilityManager();

        // Import a schedule
        $pendingCommand = $this->artisan('larabook:schedule-import', [
            'userid' => $staff->id,
            'icsurl' => __DIR__.'/../../../random_day_test.ics',
            //{--tag=[holiday]}';
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();

        $checks = [
            ['start' => '2021-06-13 11:30:00', 'end' => '2020-08-15 01:00:00', 'price' => null, 'test' => 'assertNull'],
            ['start' => '2021-06-14 1:30:00', 'end' => '2021-06-14 14:00:00', 'price' => 311, 'test' => 'assertNotNull'],
            ['start' => '2022-03-26 19:00:00', 'end' => '2022-03-26 23:30:00', 'price' => 50, 'test' => 'assertNotNull'],
            ['start' => '2020-09-09 00:00:00', 'end' => '2020-09-09 23:59:59', 'price' => 623.44, 'test' => 'assertNotNull'],
            ['start' => '2026-09-09 00:00:00', 'end' => '2026-09-09 23:59:59', 'price' => 623.44, 'test' => 'assertNotNull'],
            ['start' => '2026-09-09 00:00:00', 'end' => '2026-09-10 23:59:59', 'price' => null, 'test' => 'assertNull'],
            ['start' => '2020-11-11 12:00:00', 'end' => '2020-11-11 14:30:00', 'price' => null, 'test' => 'assertNotNull'],
            ['start' => '2030-11-11 12:00:00', 'end' => '2030-11-11 14:30:00', 'price' => null, 'test' => 'assertNotNull'],
            ['start' => '2030-11-11 12:00:00', 'end' => '2030-11-11 14:31:00', 'price' => null, 'test' => 'assertNull'],
            ['start' => '2030-11-11 11:59:59', 'end' => '2030-11-11 14:33:00', 'price' => null, 'test' => 'assertNull'],

            ['start' => '2020-10-10 06:00:00', 'end' => '2020-10-17 02:00:00', 'price' => null, 'test' => 'assertNotNull'],
            ['start' => '2022-10-10 06:00:00', 'end' => '2022-10-17 02:00:00', 'price' => null, 'test' => 'assertNotNull'],
            ['start' => '2022-10-10 05:00:00', 'end' => '2022-10-10 06:00:00', 'price' => null, 'test' => 'assertNull'],
        ];
        foreach ($checks as $check) {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $check['start']);
            $finishDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $check['end']);
            $available = $availabilityManager->staffAvailable($startDateTime, $finishDateTime, $staff);

            //Assert
            $this->{$check['test']}($available);

            if ($available && $check['price']) {
                $this->assertEquals($check['price'], $available->properties->get('price'));
            } else {
                //dd(__METHOD__ . " FAILED", $check);
            }
        }
    }

    /**
     * Test the businesses staff availability schedule + booking.
     */
    public function testStaffAvailability()
    {
        $availabilityManager = new AvailabilityManager();

        $staff = User::factory()->create();

        $booking = factory(Booking::class)->create([
            'order_id' => 'test1234',
            'order_item_id' => 'itemid111'
        ]);

        // Try to book on a Saturday 7pm to 1am
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-22 19:30:00');
        $finishDateTime = Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-23 01:00:00');

        // User has no schedule, so can't be booked.
        $available = $availabilityManager->staffHasAvailability($startDateTime, $finishDateTime, $staff);
        $this->assertFalse($available);

        //Setup the staff schedule.
        $this->createStaffScheduleTestData($staff);

        // Staff has no pre-existing bookings.
        $available = $availabilityManager->staffHasAvailability($startDateTime, $finishDateTime, $staff);
        $this->assertTrue($available);

        // Staff has an existing all day event on that date, check that we can't double book!
        $bookingCalendarEntry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $booking->getMorphClass(),
            'booked_by_id' => $booking->id,
            'start' => '2020-08-22 14:00:00',
            'end' => '2020-08-23 1:00:00',
        ]);

        // Should not be available.
        $available = $availabilityManager->staffHasAvailability($startDateTime, $finishDateTime, $staff);
        $this->assertFalse($available);

        // Cancel the all day booking
        $bookingCalendarEntry->delete();

        // Check is now available.
        $available = $availabilityManager->staffHasAvailability($startDateTime, $finishDateTime, $staff);
        $this->assertTrue($available);

        // Staff has a morning kids disco, will be available for the evening disco
        Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $booking->getMorphClass(),
            'booked_by_id' => $booking->id,
            'start' => '2020-08-22 12:00:00',
            'end' => '2020-08-22 14:00:00',
        ]);

        // Check is now available for evening booking.
        $available = $availabilityManager->staffHasAvailability($startDateTime, $finishDateTime, $staff);
        $this->assertTrue($available);
    }

    /**
     *  Business with multiple staff test.
     */
    public function testBusinessBookingAvailability()
    {
        $availabilityManager = new \App\Booking\Services\AvailabilityManager();

        //Create business with 2 staff.
        $business = $this->getBusiness('50 quid sidney\'s');

        $staff1 = User::factory()->create();
        $staff2 = User::factory()->create();

        //Attach staff to business.
        $business->users()->attach($staff1);
        $business->users()->attach($staff2);

        // Give members of staff weekend availability.
        $this->createStaffScheduleTestData($staff1); //createStaffScheduleWeekendTestData($staff1);

        // Check that business has availability (staff 1 ) on the Saturday night
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-22 19:30:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-23 01:00:00'),
            $business
        );
        $this->assertTrue($available);

        // Friday evening from 5:30pm
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-21 17:30:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-22 00:00:00'),
            $business
        );
        $this->assertTrue($available);

        // Check that staff 1 can't be booked on mid week Wednesday afternoon
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-19 12:30:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-19 16:30:00'),
            $business
        );
        $this->assertFalse($available);

        // Friday evening from 5:00pm (Too early!)
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-21 17:00:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-21 23:00:00'),
            $business
        );
        $this->assertFalse($available);

        // Give members of staff Tuesday Daytime availability only.
        $pendingCommand = $this->artisan('larabook:schedule-import', [
            'userid' => $staff2->id,
            'icsurl' => __DIR__.'/../tuesday-daytime-1000-1600.ics',
            //{--tag=[holiday]}';
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();

        // Check that business can be booked on Tuesday daytime 12:00 to 2pm school disco
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-25 12:00:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-25 14:00:00'),
            $business
        );
        $this->assertTrue($available);

        // Check that business can't be booked on Tuesday daytime 4pm to 8pm as No single member of staff can cover this!
        // Don't currently do split bookings, where ytou can change over during a shift - If you wanted to do that you'd
        // Just need to check the schedule of 2 x employees at the same time.
        $available = $availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-25 16:00:00'),
            Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-25 20:00:00'),
            $business
        );
        $this->assertFalse($available);
    }
}
