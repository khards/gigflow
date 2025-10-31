<?php

namespace Tests\Feature\Business;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the relationship between business and users.
     */
    public function testBusinessCanAttachStaff()
    {
        $businessName = '50 quid sid';
        $staff = User::factory()->create();

        //Check user created ok [yes I do need to do this as I was having a bug with default properties and global scopes]
        $this->assertNotNull($staff);

        $business = $this->getBusiness($businessName);
        $business->users()->attach($staff);

        $testBusinesss = Business::with('users')->find($business->id);
        $this->assertEquals($testBusinesss->users->first()->email, $staff->email);
    }
}
