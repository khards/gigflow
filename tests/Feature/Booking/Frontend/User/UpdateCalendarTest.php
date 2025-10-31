<?php

namespace Tests\Feature\Booking\Frontend\User;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCalendarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a holiday model can be created.
     */
    public function testUpdateCalendar()
    {
        $testUrl = 'https://calendar.google.com/calendar/ical/keithhards%40gmail.com/private-fa1038ee56ff0f6cdd8b754cdb7b8664/basic.ics';
        $user = User::factory()->create(['password' => '1234']);
        $route = route('frontend.user.calendar.update');

        $response = $this->actingAs($user)
            ->patch($route, [
                'calendar_url' => '1234',
                'calendar_schedule_url' => 'steps5678',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('calendar_url');

        $response = $this->actingAs($user)
            ->patch($route, [
                'calendar_url' => $testUrl,
                'calendar_schedule_url' => $testUrl . "&bogies",
            ]);

        //Check redirect is correct.
        $response->assertStatus(302);
        $response->assertRedirect('/account?#calendar-integration');
        $response->assertSessionHas('flash_success', __('Calendar URL successfully updated.'));

        //Ensure User's calendar URL was saved.
        $bookingSettings = User::find($user->id)->bookingSettings;
        $this->assertEquals($testUrl, $bookingSettings->get('calendar.url'));
        $this->assertEquals($testUrl. "&bogies", $bookingSettings->get('calendar_schedule.url'));
    }
}
