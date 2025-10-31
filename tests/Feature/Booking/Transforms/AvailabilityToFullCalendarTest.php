<?php

namespace Tests\Feature\Booking\Transforms;

use App\Booking\Controllers\Frontend\CalendarController;
use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Tests\TestCase;

class AvailabilityToFullCalendarTest extends BookingTestDataGenerator
{
    use RefreshDatabase;

    /**
     * Test the relationship between Users and businesses.
     */
    public function testTransformScheduleToFullCalendar()
    {
        $staff = User::factory()->create();
        $this->createStaffScheduleTestData($staff);
        $response = $this->actingAs($staff)->get('calendar/mycalendar');
        $expectedJson = '[{"title":"Friday night to mon morning","rrule":{"tzid":"UTC","dtstart":"2020-07-31T17:30:00","freq":"WEEKLY","wkst":"MO"},"duration":{"seconds":203400}},{"title":"Monday evening to Tuesday morning","rrule":{"tzid":"UTC","dtstart":"2020-08-03T17:30:00","freq":"WEEKLY","byweekday":"MO"},"duration":{"seconds":30600}},{"title":"Tuesday evening to Wed morn","rrule":{"tzid":"UTC","dtstart":"2020-08-04T17:30:00","freq":"WEEKLY","byweekday":"TU"},"duration":{"seconds":30600}},{"title":"Wed evve ning to Thurs Morn","rrule":{"tzid":"UTC","dtstart":"2020-08-05T17:30:00","freq":"WEEKLY","byweekday":"WE"},"duration":{"seconds":30600}},{"title": "Thurs evening to Fri morn","start": "2020-08-06 17:30:00","end": "2020-08-07 02:00:00"},{"title":"Thurs evening to Fri morn","rrule":{"tzid":"UTC","dtstart":"2020-08-06T17:30:00","freq":"WEEKLY","byweekday":"TH"},"duration":{"seconds":30600}}]';
        $response->assertJson(json_decode($expectedJson, true));
        $response->assertStatus(200);
    }
}
