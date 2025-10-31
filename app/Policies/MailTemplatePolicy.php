<?php

namespace App\Policies;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Email\Models\MailTemplate;

class MailTemplatePolicy extends CommonUserBusinessPolicy
{
    /**
     * @param User $user
     * @param MailTemplate $model
     * @return bool
     */
    public function view(User $user, $model)
    {
        if (! $business = Business::find($model->owner_id)) {
            return false;
        }

        //Check the passed user belongs to that business.
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }
}
