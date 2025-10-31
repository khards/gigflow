<?php

namespace App\Policies;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Domains\Auth\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasAllAccess() ? true : false;
    }

    /**
     * View all businesses schedules.
     *
     * @param User $user
     * @param Business $business
     * @return bool
     */
    public function viewAllByBusiness(User $user, Business $business)
    {
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the schedule model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Schedule $schedule
     * @return mixed
     */
    public function view(User $user, Schedule $schedule)
    {
        // Check that the schedule belongs to a business model
        if ($schedule->model_type !== (new Business())->getMorphClass()) {
            return false;
        }

        // Get the business
        // $schedule->model()->first()

        // Get the user.
        // $schedule->model()->first()->users()->first()

        // Check it's a business type.

        // Fetch the business that Schedule belongs to.
        if (! $business = Business::find($schedule->model_id)) {
            return false;
        }

        // Check that user belongs to the business.
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @return mixed
     */
    public function create(User $user, Business $business)
    {
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Schedule  $schedule
     * @return mixed
     */
    public function update(User $user, Schedule $schedule)
    {
        return $this->view($user, $schedule);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Schedule  $schedule
     * @return mixed
     */
    public function delete(User $user, Schedule $schedule)
    {
        return $this->view($user, $schedule);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Schedule  $schedule
     * @return mixed
     */
    public function restore(User $user, Schedule $schedule)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Schedule  $schedule
     * @return mixed
     */
    public function forceDelete(User $user, Schedule $schedule)
    {
        //
    }
}
