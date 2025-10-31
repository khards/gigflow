<?php

namespace App\Policies;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Form\Models\Form;

class FormPolicy extends CommonUserBusinessPolicy
{
    /**
     * @param User $user
     * @param Form $form
     * @return bool
     */
    public function view(User $user, $form)
    {
        // Get the form's business
        if (! $business = Business::find($form->owner_id)) {
            return false;
        }

        //Check the passed user belongs to that business.
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }
}
