<?php

namespace Tests\Feature\Staff;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the relationship between Users and businesses.
     */
    public function testStaffCanAttachViaBusiness()
    {
        //With
        $businessName = '50 quid sid';

        //With
        $staff = User::factory()->create();

        //Check mass assignments ok
        $this->assertNotNull($staff);

        //Then attach staff member to a business
        $staff->businesses()->attach($this->getBusiness($businessName));

        // Fetch the staff member
        $fetched = User::with('businesses')->find($staff->id);

        //Check it fetched
        $this->assertNotNull($fetched);

        //Check staff member has a business
        $this->assertEquals($fetched->businesses->first()->name, $businessName);
    }
}
