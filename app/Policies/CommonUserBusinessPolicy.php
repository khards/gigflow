<?php

namespace App\Policies;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Form\Models\Form;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class CommonUserBusinessPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasAllAccess() ? true : false;
    }

    public function index(User $user, Business $business)
    {
        //Double check user belongs to business
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    abstract public function view(User $user, $model);

    public function create(User $user, Business $business)
    {
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    public function update(User $user, $model)
    {
        return $this->view($user, $model);
    }

    public function delete(User $user, $model)
    {
        return $this->view($user, $model);
    }

    public function restore(User $user, Form $model)
    {
        return false;
    }

    public function forceDelete(User $user, $model)
    {
        return false;
    }
}
