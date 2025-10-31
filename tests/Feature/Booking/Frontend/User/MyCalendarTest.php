<?php

namespace Tests\Feature\Booking\Frontend\User;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyCalendarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the calendar can't be fetched when logged out.
     */
    public function testCalendarCanNotBeFetchedWhenLoggedOut()
    {
        $route = route('frontend.user.calendar');
        $response = $this->get($route);
        $response->assertStatus(302);
        $response->assertSeeText('/login');
        $response->assertRedirect('/login');
    }

    /**
     * Test that the calendar can be fetched when logged in.
     */
    public function testCalendarCanBeFetchedWhenLoggedIn()
    {
        $user = User::factory()->create(['password' => '1234']);
        $route = route('frontend.user.calendar');

        $response = $this->actingAs($user)
            ->get($route/*, [
                'calendar_url' => '1234',
            ]*/);
        $response->assertStatus(200);
    }
}
