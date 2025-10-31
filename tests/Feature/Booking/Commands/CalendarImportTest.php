<?php
/**
 * Calendar import used for Holidays, NOT schedules.
 */

namespace App\Booking\Commands;

use App\Domains\Auth\Models\User;
use Tests\TestCase;

class CalendarImportTest extends TestCase
{
    /**
     * Test import calendar CLI command.
     */
    public function testCalendarImport()
    {
        /*
         * Calendar has been setup under keithhards@gmail.com
         * Test calendar name: Test - website
         * Public URL: https://calendar.google.com/calendar/ical/t0gil8dffs2jnof98ijiq5dkek%40group.calendar.google.com/public/basic.ics
         *
         * Holidays on
         *      Saturday, August 15 2020
         *      Saturday  August 22, 2020, 00:00 – Sunday August 23, 2020, 18:00
         *
         */

        // Given the data is setup
        $manyStaff = factory(User::class, 5)->create();
        $staff = $manyStaff->first();
        $filename = __DIR__.'/testCalendarImport.ics';

        // When we run the import cli command
        $pendingCommand = $this->artisan('larabook:calendar-import', [
            'userid' => $staff->id,
            'icsurl' => $filename,
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();

        // Then check the correct count of Holidays in the database
        $count = $staff->calendar()->hols()->with('booked_by')->count();
        $this->assertEquals(2, $count);

        $expected = [
            1 => [
                'id' => 1,
                'model_type' => 'user',
                'model_id' => '3',
                'start' => '2030-08-15 00:00:00',
                'end' => '2030-08-16 00:00:00',
                'booked_by_type' => 'holiday',
                'booked_by_id' => '1',
                'created_by' => null,
                'updated_by' => null,
                'deleted_at' => null,
                'timezone' => 'Europe/London',
                'booked_by' => [
                        'id' => 1,
                        'title' => 'holiday test',
                        'description' => '[holiday]',
                    ],
            ],
            2 => [
                'id' => 2,
                'model_type' => 'user',
                'model_id' => '3',
                'start' => '2030-08-21 23:00:00',
                'end' => '2030-08-23 17:00:00',
                'booked_by_type' => 'holiday',
                'booked_by_id' => '2',
                'created_by' => null,
                'updated_by' => null,
                'deleted_at' => null,
                'timezone' => 'Europe/London',
                'booked_by' => [
                        'id' => 2,
                        'title' => 'Holiday period',
                        'description' => '[holiday]',
                    ],
            ],
        ];

        // Then check the database has been populated correctly!
        $staff->calendar()->hols()->with('booked_by')->each(function ($item) use ($expected) {
            $result = $item->toArray();
            unset(
                $result['created_at'],
                $result['updated_at'],
                $result['booked_by']['created_at'],
                $result['booked_by']['updated_at']
            );
            $this->assertEquals($expected[$item->id], $result);
        });
    }
}
