<?php
namespace Tests\Unit\Booking\Rrule;

use App\Booking\Availability\Rrule;
use Tests\Feature\Booking\BookingTestDataGenerator;

class RruleTest extends BookingTestDataGenerator{

    public function testTidyEventRrule() {
        $eventRrule = "DTSTART:20220101T000000Z\r\nRRULE:FREQ=YEARLY;INTERVAL=1";
        $expected = "RRULE:FREQ=YEARLY;INTERVAL=1";
        $this->assertTrue($expected === Rrule::tidyEventRrule($eventRrule));
    }
}
