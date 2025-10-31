<?php

namespace App\Booking\Commands;

use App\Booking\Availability\Schedule;
use App\Domains\Auth\Models\User;
use Tests\TestCase;

class ScheduleImportTest extends TestCase
{
    /**
     * Test import calendar CLI command.
     */
    public function testScheduleImport()
    {
        /*
         * Calendar has been setup under keithhards@gmail.com
         * Test calendar name: DJ Schedule
         * Public URL: https://calendar.google.com/calendar/ical/5o7sfstb7lk12ebmlgl7rg6oso%40group.calendar.google.com/public/basic.ics
         *
         * Mon - Fri    00:00 to 02:00      No end date
         * Mon - Fri    17:30 to 00:00      No end date
         * Saturday     All day             No end date
         * Sunday       All day             No end date
         * NEY          All day             [price=500]     Does not repeat!
         */

        // Given the data is setup
        $manyStaff = factory(User::class, 5)->create();
        $staff = $manyStaff->first();
        $filename = __DIR__.'/testScheduleImport.ics';

        // When we run the import cli command
        $pendingCommand = $this->artisan('larabook:schedule-import', [
            'userid' => $staff->id,
            'icsurl' => $filename,
            //{--tag=[holiday]}';
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();

        //Get the availability
        $availability = Schedule::where(['model_type' => $staff->getMorphClass(), 'model_id' => $staff->id])->get();

        //Check the number of entries
        $this->assertEquals(5, $availability->count());

        //Test new year eve is in the availability calendar as it's a Thursday and we need to be available!
        $nye = $availability->where('start_datetime', '2020-12-31 00:00:00')->first();
        $this->assertEquals('New years eve', $nye->summary);
        $this->assertEquals(null, $nye->is_recurring);
        $this->assertEquals('2021-01-01 00:00:00', $nye->end_datetime);
    }
}
